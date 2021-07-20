<?php

use LicenseManagerForWooCommerce\Lists\GeneratorsList;

defined('ABSPATH') || exit;

/**
 * @var string         $addGeneratorUrl
 * @var string         $generateKeysUrl
 * @var GeneratorsList $generators
 */

?>
<?php 
$is_administrator = in_array( 'administrator', (array) wp_get_current_user()->roles ) ? true : false;
$is_nodefy_admin = in_array( 'nodefy_admin', (array) wp_get_current_user()->roles ) ? true : false;
?>
<h1 class="wp-heading-inline"><?php esc_html_e('Generators', 'license-manager-for-woocommerce'); ?></h1>
<?php if($is_administrator): ?>
<a href="<?php echo esc_url($addGeneratorUrl); ?>" class="page-title-action">
    <span><?php esc_html_e('Add new', 'license-manager-for-woocommerce');?></span>
</a>
<?php endif; ?>
<?php if($is_administrator || $is_nodefy_admin): ?>
<a href="<?php echo esc_url($generateKeysUrl); ?>" class="page-title-action">
    <span><?php esc_html_e('Generate', 'license-manager-for-woocommerce');?></span>
</a>
<?php endif; ?>
<p>
    <b><?php esc_html_e('Important', 'license-manager-for-woocommerce');?>:</b>
    <span><?php esc_html_e('You can not delete generators which are still assigned to active products! To delete those, please remove the generator from all of its assigned products first.', 'license-manager-for-woocommerce');?></span>
</p>
<hr class="wp-header-end">

<form method="post">
    <?php
        $generators->prepare_items();
        $generators->display();
    ?>
</form>
