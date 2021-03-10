<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableLicenses = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
$tableGenerators = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableLicenses}'") != $tableLicenses) {
    return;
}

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableGenerators}'") != $tableGenerators) {
    return;
}

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $sql = "
        ALTER TABLE {$tableLicenses}
            ADD COLUMN `users_number` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of users' AFTER `times_activated_max`,
    ";

    $wpdb->query($sql);
    
    $sql = "
        ALTER TABLE {$tableGenerators}
            ADD COLUMN `users_number` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of users' AFTER `times_activated_max`,
    ";

    $wpdb->query($sql);
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $sql = "
        ALTER TABLE {$tableLicenses}
            DROP COLUMN `users_number`,
    ";

    $wpdb->query($sql);
    
    $sql = "
        ALTER TABLE {$tableGenerators}
            DROP COLUMN `users_number`,
    ";

    $wpdb->query($sql);
}
