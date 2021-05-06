<?php defined('ABSPATH') || exit; ?>
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
                    <?php esc_html_e( 'License ID' ); ?>: 
                    <?php echo $license ? sprintf(
                        '<a href="%s" target="_blank">#%s</a>',
                        '/wp-admin/admin.php?page=lmfwc_licenses&action=edit&id='.$log->getLicenseId(),
                        $log->getLicenseId()
                    ) : ''; ?>
                </li>
                <li>
                    <?php esc_html_e( 'Decrypted License Key' ); ?>: <br/>
                    <?php echo esc_html_e($license ? $license->getDecryptedLicenseKey() : ''); ?>
                </li>
                <li>
                    <?php esc_html_e( 'Order' ); ?>: 
                    <?php echo sprintf(
                            '<a href="%s" target="_blank">#%s</a>',
                            get_edit_post_link($log->getOrderId()),
                            $log->getOrderId()
                        ); ?>
                </li>
                <li>
                    <?php esc_html_e( 'Product' ); ?>:
                    <?php echo sprintf(
                        '<a href="%s" target="_blank">#%s - %s</a>',
                        get_edit_post_link($log->getProductId()),
                        $log->getProductId(),
                        $product->get_name()
                    ); ?>
                </li>
                <li>
                    <?php esc_html_e( 'Customer' ); ?>: 
                    <?php echo sprintf(
                        '<a href="%s">%s (#%d - %s)</a>',
                        get_edit_user_link($user->ID),
                        $user->display_name,
                        $user->ID,
                        $user->user_email
                    ); ?>
                </li>
                <li>
                    <?php esc_html_e( 'Operation' ); ?>: 
                    <?php echo esc_html_e($log->getOperation()); ?>
                </li>
                <li>
                    <?php esc_html_e( 'Note' ); ?>: 
                    <?php echo esc_html_e($log->getNote()); ?>
                </li>
                <li>
                    <?php esc_html_e( 'License Backup' ); ?>: <br/>
                    <pre style="margin: 0px; padding: 0px;">
                        <code class="json" style="display: block;"><?php print_r(json_decode($log->getLicenseBackup(), true)); ?></code>
                    </pre>
                </li>
                <li>
                    <?php esc_html_e( 'Created At' ); ?>:
                    <?php echo esc_html_e($log->getCreatedAt()); ?>
                </li>
            </ul>
        </div>
    </div>
</div>