<?php
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;

defined('ABSPATH') || exit;

/** @var LicenseResourceModel $license */
?>

<?php 
    function is_sales() {
        $user = wp_get_current_user();
        $roles = $user ? (array)$user->roles : [];
        return count($roles) > 0 && $roles[0] === 'sales' ? true : false;
    }
?>
<h1 class="wp-heading-inline"><?php esc_html_e('View license key', 'license-manager-for-woocommerce'); ?></h1>
<hr class="wp-header-end">

<style>
.wrap .postbox .inside li{
    word-break: break-all;
    margin-top: 15px;
}
</style>
<div class="wrap">
    <div class="postbox request-headers">
        <h3 class="hndle" style="font-size: 14px; padding: 8px 12px; margin: 0px;">
            <span><?php esc_html_e( 'Detail' ); ?></span>
        </h3>
        <div class="inside">
            <ul>
                <li>
                    <strong><?php esc_html_e('ID', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($license->getId()); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('License key', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($licenseKey); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Valid for (days)', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($license->getValidFor()); ?><br/>
                    <i><?php echo esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire. Cannot be used at the same time as the "Expires at" field.', 'license-manager-for-woocommerce'); ?></i>
                </li>
                <li>
                    <strong><?php esc_html_e('Expires at', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($expiresAt); ?><br/>
                    <i><?php echo esc_html_e('The exact date this license key expires on. Leave blank if the license key does not expire. Cannot be used at the same time as the "Valid for (days)" field.', 'license-manager-for-woocommerce'); ?></i>
                </li>
                <li>
                    <strong><?php esc_html_e('Number of users', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($license->getUsersNumber()); ?><br/>
                    <i><?php echo esc_html_e('The maximum number of server users. If you leave it blank, the number of users is not limited.', 'license-manager-for-woocommerce'); ?></i>
                </li>
                <li>
                    <strong><?php esc_html_e('Maximum activation count', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($license->getTimesActivatedMax()); ?><br/>
                    <i><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'license-manager-for-woocommerce'); ?></i>
                </li>
                <li>
                    <strong><?php esc_html_e('Activated count', 'license-manager-for-woocommerce'); ?>: </strong>
                    <?php echo esc_html($license->getTimesActivated()); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Homeserver', 'license-manager-for-woocommerce');?>: </strong>
                    <?php echo esc_html($license->getHomeserver()); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Status', 'license-manager-for-woocommerce');?>: </strong>
                    <?php foreach($statusOptions as $option): 
                            if ($option['value'] === $license->getStatus()): ?>
                                <?php echo esc_html($option['name']); ?>
                    <?php   endif; 
                         endforeach; ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Order', 'license-manager-for-woocommerce');?>: </strong>
                    <?php
                    if ($license->getOrderId()) {
                        $order = wc_get_order($license->getOrderId());
                        if ($order) {
                            echo sprintf(
                                '<a href="%s" target="_blank">#%s</a>',
                                '/wp-admin/post.php?action=edit&post='.$license->getOrderId(),
                                $license->getOrderId() . '-' . $order->get_billing_email()
                            );
                        }
                    }
                    ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Product', 'license-manager-for-woocommerce');?>: </strong>
                    <?php
                    if ($license->getProductId()) {
                        /** @var WC_Order $order */
                        $product = wc_get_product($license->getProductId());
                        if ($product) {
                            if (is_sales()) {
                                echo sprintf(
                                    '#%s',
                                    $product->get_formatted_name()
                                );
                            } else {
                                echo sprintf(
                                    '<a href="%s" target="_blank">#%s</a>',
                                    '/wp-admin/post.php?action=edit&post=' . $product->get_id(),
                                    $product->get_formatted_name()
                                );
                            }
                        }
                    }
                    ?><br/>
                    <i><?php esc_html_e('The product to which the license keys will be assigned.', 'license-manager-for-woocommerce');?></i>
                </li>

                <li>
                    <strong><?php esc_html_e('Customer', 'license-manager-for-woocommerce');?>: </strong>
                    <?php
                    if ($license->getUserId()) {
                        /** @var WP_User $user */
                        $user = get_userdata($license->getUserId());
                        if ($user) {
                            if (is_sales()) {
                                echo sprintf(
                                    '#%s',
                                    $user->user_nicename . '(' . $user->ID . '-'  . $user->user_email . ')'
                                );
                            } else {
                                echo sprintf(
                                    '<a href="%s" target="_blank">#%s</a>',
                                    '/wp-admin/post.php?action=edit&post=' . $user->ID,
                                    $user->user_nicename . '(' . $user->ID . '-'  . $user->user_email . ')'
                                );
                            }
                            
                        }
                    }
                    ?><br/>
                    <i><?php esc_html_e('The user to which the license keys will be assigned.', 'license-manager-for-woocommerce');?></i>
                </li>
            </ul>
        </div>
    </div>
</div>
