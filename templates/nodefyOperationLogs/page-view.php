<?php defined('ABSPATH') || exit; ?>
<?php 
    function is_sales() {
        $user = wp_get_current_user();
        $roles = $user ? (array)$user->roles : [];
        return count($roles) > 0 && $roles[0] === 'sales' ? true : false;
    }
?>
<style>
.wrap .postbox .inside li{
    word-break: break-all;
    margin-top: 15px;
}
</style>
<div class="title">
    <h2>ID: <?php echo $log->getID(); ?></h2>
</div>
<div class="wrap">
    <div class="postbox request-headers">
        <h3 class="hndle" style="font-size: 14px; padding: 8px 12px; margin: 0px;">
            <span><?php esc_html_e( 'Detail' ); ?></span>
        </h3>
        <div class="inside">
            <ul>
                <li>
                    <strong><?php esc_html_e( 'License ID' ); ?></strong>: 
                    <?php echo $license ? sprintf(
                        '<a href="%s" target="_blank">#%s</a>',
                        '/wp-admin/admin.php?page=lmfwc_licenses&action=edit&id='.$log->getLicenseId(),
                        $log->getLicenseId()
                    ) : ''; ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Decrypted License Key' ); ?>: </strong><br/>
                    <?php echo esc_html_e($license ? $license->getDecryptedLicenseKey() : ''); ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Order' ); ?>:  </strong>
                    <?php echo sprintf(
                            '<a href="%s" target="_blank">#%s</a>',
                            get_edit_post_link($log->getOrderId()),
                            $log->getOrderId()
                        ); ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Product' ); ?>: </strong>
                    <?php if (is_sales()): ?>
                        <?php echo '#'. $log->getProductId() . '-' . $product->get_name(); ?>
                    <?php else: ?>
                        <?php echo sprintf(
                            '<a href="%s" target="_blank">#%s - %s</a>',
                            get_edit_post_link($log->getProductId()),
                            $log->getProductId(),
                            $product->get_name()
                        ); ?>
                    <?php endif; ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Customer' ); ?>:  </strong>
                    <?php if (is_sales()): ?>
                        <?php echo '(#'.$user->ID . '-' . $user->user_email .')'; ?>
                    <?php else: ?>
                        <?php echo sprintf(
                            '<a href="%s">%s (#%d - %s)</a>',
                            get_edit_user_link($user->ID),
                            $user->display_name,
                            $user->ID,
                            $user->user_email
                        ); ?>
                    <?php endif; ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Operation' ); ?>: </strong> 
                    <?php echo esc_html_e($log->getOperation()); ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Note' ); ?>: </strong>
                    <?php echo esc_html_e($log->getNote()); ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'License Backup' ); ?>:</strong> <br/>
                    <pre style="margin: 0px; padding: 0px;">
                        <code class="json" style="display: block;"><?php print_r(json_decode($log->getLicenseBackup(), true)); ?></code>
                    </pre>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Created At' ); ?>:</strong>
                    <?php echo esc_html_e($log->getCreatedAt()); ?>
                </li>
            </ul>
        </div>
    </div>
</div>