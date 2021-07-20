<?php

namespace LicenseManagerForWooCommerce\Lists;

use DateTime;
use Exception;
use LicenseManagerForWooCommerce\AdminMenus;
use LicenseManagerForWooCommerce\AdminNotice;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\NodefyOperationLog as NodefyOperationLogModel;
use LicenseManagerForWooCommerce\Repositories\Resources\NodefyOperationLog as NodefyOperationLogRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Settings;
use LicenseManagerForWooCommerce\Setup;
use WC_Product;
use WP_List_Table;
use WP_User;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class NodefyOperationLogList extends WP_List_Table
{
    /**
     * Path to spinner image.
     */
    const SPINNER_URL = '/wp-admin/images/loading.gif';

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var string
     */
    protected $timeFormat;

    /**
     * @var string
     */
    protected $gmtOffset;

    /**
     * @var array
     */
    protected $roles;

    /**
     * LicensesList constructor.
     */
    public function __construct()
    {
        global $wpdb;

        parent::__construct(
            array(
                'singular' => __('Operation log', 'license-manager-for-woocommerce'),
                'plural'   => __('Operation logs', 'license-manager-for-woocommerce'),
                'ajax'     => false
            )
        );

        $user = wp_get_current_user();
        $this->roles = $user ? (array)$user->roles : [];
        $this->table      = $wpdb->prefix . Setup::NODEFY_OPERATION_LOG_TABLE_NAME;
        $this->dateFormat = get_option('date_format');
        $this->timeFormat = get_option('time_format');
        $this->gmtOffset  = get_option('gmt_offset');
    }

    protected function is_sales() {
        return count($this->roles) > 0 && $this->roles[0] === 'sales' ? true : false;
    }

    protected function is_super_admin() {
        return in_array( 'administrator', (array) wp_get_current_user()->roles ) ? true : false;
    }

    /**
     * Creates the different status filter links at the top of the table.
     *
     * @return array
     */
    protected function get_views()
    {
        $statusLinks = array();
        $current     = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'all';

        // All link
        $class = $current == 'all' ? ' class="current"' :'';
        $allUrl = remove_query_arg('status');
        $statusLinks['all'] = sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            $allUrl,
            $class,
            __('All', 'license-manager-for-woocommerce'),
            NodefyOperationLogRepository::instance()->count()
        );

        return $statusLinks;
    }

    /**
     * Adds the order and product filters to the licenses list.
     *
     * @param string $which
     */
    protected function extra_tablenav($which)
    {
        if ($which === 'top') {
            echo '<div class="alignleft actions">';
            $this->licenseDropdown();
            $this->orderDropdown();
            $this->productDropdown();
            $this->userDropdown();
            submit_button(__('Filter', 'license-manager-for-woocommerce'), '', 'filter-action', false);
            echo '</div>';
        }
    }

     /**
     * Displays the license dropdown filter.
     */
    public function licenseDropdown()
    {
        $license = false;

        if (isset($_REQUEST['license-id'])) {
            $license = LicenseResourceRepository::instance()->findBy(array('id' => $_REQUEST['license-id']));
        }

        ?>
        <label for="filter-by-license-id" class="screen-reader-text">
            <span><?php _e('Filter by license', 'license-manager-for-woocommerce'); ?></span>
        </label>
        <select name="license-id" id="filter-by-license-id">
            <?php if ($license): ?>
                <option selected="selected" value="<?php echo esc_attr($license->getID()); ?>">
                    <?php echo esc_html($license->getShortDecryptedLicenseKey());?>
                </option>
            <?php endif; ?>
        </select>
        <?php
    }

    /**
     * Displays the order dropdown filter.
     */
    public function orderDropdown()
    {
        $order = false;

        if (isset($_REQUEST['order-id'])) {
            $order = wc_get_order((int)$_REQUEST['order-id']);
        }

        ?>
        <label for="filter-by-order-id" class="screen-reader-text">
            <span><?php _e('Filter by order', 'license-manager-for-woocommerce'); ?></span>
        </label>
        <select name="order-id" id="filter-by-order-id">
            <?php if ($order): ?>
                <option selected="selected" value="<?php echo esc_attr($order->get_id()); ?>">
                    <?php echo $order->get_formatted_billing_full_name(); ?>
                </option>
            <?php endif; ?>
        </select>
        <?php
    }

    /**
     * Displays the product dropdown filter.
     */
    public function productDropdown()
    {
        $product = false;

        if (isset($_REQUEST['product-id'])) {
            $product = wc_get_product((int)$_REQUEST['product-id']);
        }

        ?>
        <label for="filter-by-product-id" class="screen-reader-text">
            <span><?php _e('Filter by product', 'license-manager-for-woocommerce'); ?></span>
        </label>
        <select name="product-id" id="filter-by-product-id">
            <?php if ($product): ?>
                <option selected="selected" value="<?php echo esc_attr($product->get_id()); ?>">
                    <?php echo $product->get_name(); ?>
                </option>
            <?php endif; ?>
        </select>
        <?php
    }

    /**
     * Displays the user dropdown filter.
     */
    public function userDropdown()
    {
        $user = false;

        if (isset($_REQUEST['user-id'])) {
            $user = get_user_by('ID', (int)$_REQUEST['user-id']);
        }
        ?>
        <label for="filter-by-user-id" class="screen-reader-text">
            <span><?php _e('Filter by user', 'license-manager-for-woocommerce'); ?></span>
        </label>
        <select name="user-id" id="filter-by-user-id">
            <?php if ($user) {
                printf(
                    '<option value="%d" selected="selected">%s (#%d - %s)</option>',
                    $user->ID,
                    $user->display_name,
                    $user->ID,
                    $user->user_email
                );
            } ?>
        </select>
        <?php
    }

    /**
     * Checkbox column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Id column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @return string
     */
    public function column_id($item)
    {
        // ID
        // $actions['id'] = sprintf(__('ID: %d', 'license-manager-for-woocommerce'), intval($item['id']));

        // Edit
        $actions['view'] = sprintf(
            '<a href="%s">%s</a>',
            admin_url(
                wp_nonce_url(
                    sprintf(
                        'admin.php?page=%s&action=view&id=%d',
                        AdminMenus::NODEFY_OPERATION_LOG_PAGE,
                        intval($item['id'])
                    ),
                    'lmfwc_view_nodefy_operation_log'
                )
            ),
            __('View', 'license-manager-for-woocommerce')
        );

        // Delete disable now
        if ($this->is_super_admin() && false){
            $actions['delete'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url(
                    sprintf(
                        'admin.php?page=%s&action=delete&id=%d&_wpnonce=%s',
                        AdminMenus::NODEFY_OPERATION_LOG_PAGE,
                        intval($item['id']),
                        wp_create_nonce('delete')
                    )
                ),
                __('Permanently Delete', 'license-manager-for-woocommerce')
            );
        }
        
        return $item['id'] . $this->row_actions($actions);
    }

    /**
     * License id key column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @return string
     */
    public function column_license_id($item)
    {
        $license = LicenseResourceRepository::instance()->findBy(array(
            'id' => $item['license_id']
        ));
        $key = $license ? esc_html($license->getShortDecryptedLicenseKey()) : 'deleted';
        $html = sprintf(
            '<a href="%s" target="_blank">#%s</a>',
            '/wp-admin/admin.php?page=lmfwc_licenses&action=edit&id='.$item['license_id'],
            $item['license_id'].' - '.$key
        );
        return $html;
    }

    /**
     * Order ID column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @return string
     */
    public function column_order_id($item)
    {
        $html = '';

        if ($order = wc_get_order($item['order_id'])) {
            $html = sprintf(
                '<a href="%s" target="_blank">#%s</a>',
                get_edit_post_link($item['order_id']),
                $order->get_order_number()
            );
        }

        return $html;
    }

    /**
     * Product ID column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @return string
     */
    public function column_product_id($item)
    {
        $html = '';

        /** @var WC_Product $product */
        if ($product = wc_get_product($item['product_id'])) {

            if ($parentId = $product->get_parent_id()) {
                $html = sprintf(
                    '<span>#%s - %s</span>',
                    $product->get_id(),
                    $product->get_name()
                );

                if ($parent = wc_get_product($parentId)) {
                    $html .= sprintf(
                        '<br><small>%s <a href="%s" target="_blank">#%s - %s</a></small>',
                        __('Variation of', 'license-manager-for-woocommerce'),
                        get_edit_post_link($parent->get_id()),
                        $parent->get_id(),
                        $parent->get_name()
                    );
                }
            } else {
                if ($this->is_sales()){
                    $html = sprintf(
                        '#%s - %s',
                        $product->get_id(),
                        $product->get_name()
                    );
                } else {
                    $html = sprintf(
                        '<a href="%s" target="_blank">#%s - %s</a>',
                        get_edit_post_link($item['product_id']),
                        $product->get_id(),
                        $product->get_name()
                    );
                }
                
            }
        }

        return $html;
    }

    /**
     * User ID column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @return string
     */
    public function column_user_id($item)
    {
        $html = '';

        if ($item['user_id'] !== null) {
            /** @var WP_User $user */
            $user = get_userdata($item['user_id']);

            if ($user instanceof WP_User) {
                $str = sprintf(
                    '%s (#%d - %s)',
                    $user->display_name,
                    $user->ID,
                    $user->user_email
                );
                if (current_user_can('license_manager_manage_options') && !$this->is_sales()) {
                    $str = sprintf(
                        '<a href="%s">%s (#%d - %s)</a>',
                        get_edit_user_link($user->ID),
                        $user->display_name,
                        $user->ID,
                        $user->user_email
                    );
                }
                $html .= $str;
            }
        }

        return $html;
    }


    /**
     * Created column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @throws Exception
     * @return string
     */
    public function column_created($item)
    {
        $html = '';

        if ($item['created_at']) {
            $offsetSeconds = floatval($this->gmtOffset) * 60 * 60;
            $timestamp     = strtotime($item['created_at']) + $offsetSeconds;
            $result        = date('Y-m-d H:i:s', $timestamp);
            $date          = new DateTime($result);

            $html .= sprintf(
                '<span>%s <b>%s, %s</b></span>',
                __('at', 'license-manager-for-woocommerce'),
                $date->format($this->dateFormat),
                $date->format($this->timeFormat)
            );
        }

        if ($item['created_by']) {
            /** @var WP_User $user */
            $user = get_user_by('id', $item['created_by']);

            if ($user instanceof WP_User) {
                $str =  sprintf(
                    '<br><span>%s %s</span>',
                    __('by', 'license-manager-for-woocommerce'),
                    $user->display_name
                );
                if (current_user_can('license_manager_manage_options') && !$this->is_sales()) {
                    $str = sprintf(
                        '<br>%s <a href="%s">%s</a>',
                        __('by', 'license-manager-for-woocommerce'),
                        get_edit_user_link($user->ID),
                        $user->display_name
                    );
                }
                $html .= $str;
            }
        }

        return $html;
    }

    public function column_expired_at($item){
        $return_str = '';
        $obj = json_decode($item['license_backup'], true);
        if (isset($obj['new_value']) && isset($obj['new_value']['data'])) {
            $data = json_decode($obj['new_value']['data'], true);
            $return_str = $data['expires_at'];
        }
        return $return_str;
    }

    public function column_users_number($item){
        $return_str = '';
        $obj = json_decode($item['license_backup'], true);
        if (isset($obj['new_value']) && isset($obj['new_value']['data'])) {
            $data = json_decode($obj['new_value']['data'], true);
            $return_str = $data['users_number'];
        }
        return $return_str;
    }

    /**
     * Default column value.
     *
     * @param array  $item       Associative array of column name and value pairs
     * @param string $columnName Name of the current column
     *
     * @return string
     */
    public function column_default($item, $columnName)
    {
        $item = apply_filters('lmfwc_table_nodefy_operations_logs_column_value', $item, $columnName);

        return $item[$columnName];
    }

    /**
     * Defines sortable columns and their sort value.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortableColumns = array(
            'id'         => array('id', true),
            'order_id'   => array('order_id', true),
            'product_id' => array('product_id', true),
            'user_id'    => array('user_id', true),
            'expires_at' => array('expires_at', true),
            'status'     => array('status', true),
            'created'    => array('created_at', true),
            'updated'    => array('updated_at', true),
            'activation' => array('times_activated_max', true)
        );

        return apply_filters('lmfwc_table_licenses_column_sortable', $sortableColumns);
    }

    /**
     * Defines items in the bulk action dropdown.
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            // 'delete'            => __('Delete', 'license-manager-for-woocommerce'),
        );
        $actions['export_csv'] = __('Export (CSV)', 'license-manager-for-woocommerce');
        if ($this->is_super_admin()){
            $actions['notice'] = __('-----------------', 'license-manager-for-woocommerce');
            $actions['delete'] = __('Permanently Delete', 'license-manager-for-woocommerce');
        }

        return $actions;
    }

    /**
     * Processes the currently selected action.
     */
    private function processBulkActions()
    {
        $action = $this->current_action();

        switch ($action) {
            case 'delete':
                $this->deleteNodefyOperationLogs();
                break;
            case 'export_csv':
                $this->exportNodefyOperationLogList('CSV');
                break;
            default:
                break;
        }
    }

      /**
     * Initiates a file download of the exported licenses (PDF or CSV).
     *
     * @param string $type
     * @throws Exception
     */
    private function exportNodefyOperationLogList($type)
    {
        $this->verifySelection();

        if ($type === 'CSV') {
            $this->verifyNonce('export_csv');
            do_action('lmfwc_export_nodefy_operation_log_lists_csv', (array)$_REQUEST['id']);
        }
    }

    /**
     * Initialization function.
     */
    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->processBulkActions();

        $perPage     = $this->get_items_per_page('lmfwc_licenses_per_page', 10);
        $currentPage = $this->get_pagenum();
        $totalItems  = $this->getNodefyOperationLogCount();

        $this->set_pagination_args(
            array(
                'total_items' => $totalItems,
                'per_page'    => $perPage,
                'total_pages' => ceil($totalItems / $perPage)
            )
        );

        $this->items = $this->getNodefyOperationLogs($perPage, $currentPage);
    }

    /**
     * Retrieves the licenses from the database.
     *
     * @param int $perPage    Default amount of licenses per page
     * @param int $pageNumber Default page number
     *
     * @return array
     */
    private function getNodefyOperationLogs($perPage = 20, $pageNumber = 1)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table} WHERE 1 = 1";

        // Applies the search box filter
        if (array_key_exists('s', $_REQUEST) && $_REQUEST['s']) {
            $sql .= $wpdb->prepare(
                ' AND hash = %s',
                apply_filters('lmfwc_hash', sanitize_text_field($_REQUEST['s']))
            );
        }

        // Applies the license filter
        if (isset($_REQUEST['license-id']) && is_numeric($_REQUEST['license-id'])) {
            $sql .= $wpdb->prepare(' AND license_id = %d', intval($_REQUEST['license-id']));
        }

        // Applies the order filter
        if (isset($_REQUEST['order-id']) && is_numeric($_REQUEST['order-id'])) {
            $sql .= $wpdb->prepare(' AND order_id = %d', intval($_REQUEST['order-id']));
        }

        // Applies the product filter
        if (isset($_REQUEST['product-id']) && is_numeric($_REQUEST['product-id'])) {
            $sql .= $wpdb->prepare(' AND product_id = %d', intval($_REQUEST['product-id']));
        }

        // Applies the user filter
        if (isset($_REQUEST['user-id']) && is_numeric($_REQUEST['user-id'])) {
            $sql .= $wpdb->prepare(' AND user_id = %d', intval($_REQUEST['user-id']));
        }

        $sql .= ' ORDER BY ' . (empty($_REQUEST['orderby']) ? 'id' : esc_sql($_REQUEST['orderby']));
        $sql .= ' '          . (empty($_REQUEST['order'])   ? 'DESC'  : esc_sql($_REQUEST['order']));
        $sql .= " LIMIT {$perPage}";
        $sql .= ' OFFSET ' . ($pageNumber - 1) * $perPage;

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Retrieves the nodefy operation log table row count.
     *
     * @return int
     */
    private function getNodefyOperationLogCount()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1 = 1";

        if (isset($_REQUEST['order-id'])) {
            $sql .= $wpdb->prepare(' AND order_id = %d', intval($_REQUEST['order-id']));
        }

        if (array_key_exists('s', $_REQUEST) && $_REQUEST['s']) {
            $sql .= $wpdb->prepare(
                ' AND hash = %s',
                apply_filters('lmfwc_hash', sanitize_text_field($_REQUEST['s']))
            );
        }

        return $wpdb->get_var($sql);
    }

    /**
     * Output in case no items exist.
     */
    public function no_items()
    {
        _e('No operation logs found.', 'license-manager-for-woocommerce');
    }

    /**
     * Set the table columns.
     */
    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'id'          => __('Id', 'license-manager-for-woocommerce'),
            'license_id'  => __('License', 'license-manager-for-woocommerce'),
            'order_id'    => __('Order', 'license-manager-for-woocommerce'),
            'product_id'  => __('Product', 'license-manager-for-woocommerce'),
            'user_id'     => __('Customer', 'license-manager-for-woocommerce'),
            'operation'   => __('Operation', 'license-manager-for-woocommerce'),
            'users_number'=> __('Users Number', 'license-manager-for-woocommerce'),
            'expired_at'  => __('Expired At', 'license-manager-for-woocommerce'),
            'note'        => __('Note', 'license-manager-for-woocommerce'),
            'created'     => __('Created', 'license-manager-for-woocommerce'),
        );

        return apply_filters('lmfwc_table_licenses_column_name', $columns);
    }

    /**
     * Checks if the given nonce is (still) valid.
     *
     * @param string $nonce The nonce to check
     * @throws Exception
     */
    private function verifyNonce($nonce)
    {
        $currentNonce = $_REQUEST['_wpnonce'];

        if (!wp_verify_nonce($currentNonce, $nonce)
            && !wp_verify_nonce($currentNonce, 'bulk-' . $this->_args['plural'])
        ) {
            AdminNotice::error(__('The nonce is invalid or has expired.', 'license-manager-for-woocommerce'));
            wp_redirect(
                admin_url(sprintf('admin.php?page=%s', AdminMenus::NODEFY_OPERATION_LOG_PAGE))
            );

            exit();
        }
    }

    /**
     * Makes sure that license keys were selected for the bulk action.
     */
    private function verifySelection()
    {
        // No ID's were selected, show a warning and redirect
        if (!array_key_exists('id', $_REQUEST)) {
            $message = sprintf(esc_html__('No license keys were selected.', 'license-manager-for-woocommerce'));
            AdminNotice::warning($message);

            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=%s', AdminMenus::NODEFY_OPERATION_LOG_PAGE)
                )
            );

            exit();
        }
    }

    /**
     * Removes the license key(s) permanently from the database.
     *
     * @throws Exception
     */
    private function deleteNodefyOperationLogs()
    {
        $this->verifyNonce('delete');
        $this->verifySelection();

        $operationLogIds = (array)$_REQUEST['id'];
        $count         = 0;

        foreach ($operationLogIds as $logId) {
            /** @var NodefyOperationLogModel $license */
            $license = NodefyOperationLogRepository::instance()->find($logId);

            if (!$license) {
                continue;
            }

            $result = NodefyOperationLogRepository::instance()->delete((array)$logId);

            if ($result) {
                $count += $result;
            }
        }

        $message = sprintf(esc_html__('%d operation log(s) permanently deleted.', 'license-manager-for-woocommerce'), $count);

        // Set the admin notice
        AdminNotice::success($message);

        // Redirect and exit
        wp_redirect(
            admin_url(
                sprintf('admin.php?page=%s', AdminMenus::NODEFY_OPERATION_LOG_PAGE)
            )
        );
    }

    /**
     * Displays the search box.
     *
     * @param string $text
     * @param string $inputId
     */
    public function search_box($text, $inputId)
    {
        if (empty($_REQUEST['s']) && !$this->has_items()) {
            return;
        }

        $inputId     = $inputId . '-search-input';
        $searchQuery = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="' . esc_attr( $inputId ) . '">' . esc_html( $text ) . ':</label>';
        echo '<input type="search" id="' . esc_attr($inputId) . '" name="s" value="' . esc_attr($searchQuery) . '" />';

        submit_button(
            $text, '', '', false,
            array(
                'id' => 'search-submit',
            )
        );

        echo '</p>';
    }
}
