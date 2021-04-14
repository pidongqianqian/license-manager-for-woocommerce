<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Operation logs', 'license-manager-for-woocommerce'); ?></h1>
<hr class="wp-header-end">

<?php  ?>

<form method="post" id="lmfwc-license-table">
    <?php
        $nodefyOperationLogs->prepare_items();
        $nodefyOperationLogs->views();
        // $nodefyOperationLogs->search_box(__( 'Search logs', 'license-manager-for-woocommerce' ), 'user_id');
        $nodefyOperationLogs->display();
    ?>
</form>

<span class="lmfwc-txt-copied-to-clipboard" style="display: none"><?php esc_html_e('Copied to clipboard', 'license-manager-for-woocommerce'); ?></span>