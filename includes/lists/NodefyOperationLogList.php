<?php

namespace LicenseManagerForWooCommerce\Lists;

use DateTime;
use Exception;
use LicenseManagerForWooCommerce\AdminMenus;
use LicenseManagerForWooCommerce\AdminNotice;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\NodefyOperationLog as NodefyOperationLogModel;
use LicenseManagerForWooCommerce\Repositories\Resources\NodefyOperationLog as NodefyOperationLogRepository;
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

        $this->table      = $wpdb->prefix . Setup::NODEFY_OPERATION_LOG_TABLE_NAME;
        $this->dateFormat = get_option('date_format');
        $this->timeFormat = get_option('time_format');
        $this->gmtOffset  = get_option('gmt_offset');
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
        return $statusLinks;
    }

    /**
     * Adds the order and product filters to the licenses list.
     *
     * @param string $which
     */
    protected function extra_tablenav($which)
    {
       
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
        $actions['id'] = sprintf(__('ID: %d', 'license-manager-for-woocommerce'), intval($item['id']));

        // Edit
        $actions['edit'] = sprintf(
            '<a href="%s">%s</a>',
            admin_url(
                wp_nonce_url(
                    sprintf(
                        'admin.php?page=%s&action=edit&id=%d',
                        AdminMenus::NODEFY_OPERATION_LOG_PAGE,
                        intval($item['id'])
                    ),
                    'lmfwc_edit_nodefy_operation_log'
                )
            ),
            __('Edit', 'license-manager-for-woocommerce')
        );

        // Delete
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
            __('Delete', 'license-manager-for-woocommerce')
        );

        return $item['id'] . $this->row_actions($actions);
    }

    // /**
    //  * License id key column.
    //  *
    //  * @param array $item Associative array of column name and value pairs
    //  *
    //  * @return string
    //  */
    // public function column_license_id($item)
    // {
    //     $title = '';

    //     if ($order = wc_get_order($item['order_id'])) {
    //         $html = sprintf(
    //             '<a href="%s" target="_blank">#%s</a>',
    //             get_edit_post_link($item['order_id']),
    //             $order->get_order_number()
    //         );
    //     }
    // }

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
                $html = sprintf(
                    '<a href="%s" target="_blank">#%s - %s</a>',
                    get_edit_post_link($item['product_id']),
                    $product->get_id(),
                    $product->get_name()
                );
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
                if (current_user_can('manage_options')) {
                    $html .= sprintf(
                        '<a href="%s">%s (#%d - %s)</a>',
                        get_edit_user_link($user->ID),
                        $user->display_name,
                        $user->ID,
                        $user->user_email
                    );
                }

                else {
                    $html .= sprintf(
                        '<span>%s</span>',
                        $user->display_name
                    );
                }
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
                if (current_user_can('manage_options')) {
                    $html .= sprintf(
                        '<br>%s <a href="%s">%s</a>',
                        __('by', 'license-manager-for-woocommerce'),
                        get_edit_user_link($user->ID),
                        $user->display_name
                    );
                }

                else {
                    $html .= sprintf(
                        '<br><span>%s %s</span>',
                        __('by', 'license-manager-for-woocommerce'),
                        $user->display_name
                    );
                }
            }
        }

        return $html;
    }

    /**
     * Info column.
     *
     * @param array $item Associative array of column name and value pairs
     *
     * @throws Exception
     * @return string
     */
    public function column_info($item)
    {
        if (!$item['info']) {
            return '';
        }

        $infos = unserialize($item['info']);
        return sprintf(
            '<span>mac: %s<br>ip: %s</span>',
            $infos['mac'],
            $infos['ip']
        );
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
            'delete'            => __('Delete', 'license-manager-for-woocommerce'),
        );

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
            default:
                break;
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
