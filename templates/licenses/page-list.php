<?php defined('ABSPATH') || exit; ?>

<style>
.lmfwc-placeholder{
    margin-left: 0px;
}
</style>
<h1 class="wp-heading-inline"><?php esc_html_e('License keys', 'license-manager-for-woocommerce'); ?></h1>
<?php if(false): ?>
<!-- <a class="page-title-action" href="<?php echo esc_url($addLicenseUrl); ?>">
    <span><?php esc_html_e('Add new', 'license-manager-for-woocommerce');?></span>
</a> -->
<!-- <a class="page-title-action" href="<?php echo esc_url($importLicenseUrl); ?>">
    <span><?php esc_html_e('Import', 'license-manager-for-woocommerce');?></span>
</a> -->
<?php endif; ?>
<hr class="wp-header-end">

<style>
#lmfwc-license-table table{
    white-space: nowrap;
}
</style>

<form method="post" id="lmfwc-license-table" style="overflow-x: auto">
    <?php
        $licenses->prepare_items();
        $licenses->views();
        $licenses->search_box(__( 'Search license key', 'license-manager-for-woocommerce' ), 'license_key');
        $licenses->display();
    ?>
</form>

<span class="lmfwc-txt-copied-to-clipboard" style="display: none"><?php esc_html_e('Copied to clipboard', 'license-manager-for-woocommerce'); ?></span>