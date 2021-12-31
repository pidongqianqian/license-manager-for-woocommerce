<?php

namespace LicenseManagerForWooCommerce;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key as DefuseCryptoKey;
use Exception;
use function dbDelta;

defined('ABSPATH') || exit;

class Setup
{
    /**
     * @var string
     */
    const LICENSES_TABLE_NAME = 'lmfwc_licenses';

    /**
     * @var string
     */
    const GENERATORS_TABLE_NAME = 'lmfwc_generators';

    /**
     * @var string
     */
    const API_KEYS_TABLE_NAME = 'lmfwc_api_keys';

    /**
     * @var string
     */
    const LICENSE_META_TABLE_NAME = 'lmfwc_licenses_meta';

    /**
     * @var string
     */
    const NODEFY_OPERATION_LOG_TABLE_NAME = 'lmfwc_nodefy_operation_log';

    /**
     * @var string
     */
    const NODEFY_LICENSE_RELATE_ORDERS_TABLE_NAME = 'lmfwc_nodefy_license_relate_orders';

    /**
     * @var int
     */
    const DB_VERSION = 109;

    /**
     * Installation script.
     *
     * @throws EnvironmentIsBrokenException
     * @throws Exception
     */
    public static function install()
    {
        self::checkRequirements();
        self::createTables();
        self::setDefaultFilesAndFolders();
        self::setDefaultSettings();

        flush_rewrite_rules();
    }

    /**
     * Deactivation script.
     */
    public static function deactivate()
    {
        // Nothing for now...
    }

    /**
     * Uninstall script.
     */
    public static function uninstall()
    {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . self::LICENSES_TABLE_NAME,
            $wpdb->prefix . self::GENERATORS_TABLE_NAME,
            $wpdb->prefix . self::API_KEYS_TABLE_NAME,
            $wpdb->prefix . self::LICENSE_META_TABLE_NAME,
            $wpdb->prefix . self::NODEFY_OPERATION_LOG_TABLE_NAME,
            $wpdb->prefix . self::NODEFY_LICENSE_RELATE_ORDERS_TABLE_NAME,
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        delete_option('lmfwc_settings_general');
        delete_option('lmfwc_settings_order_status');
        delete_option('lmfwc_settings_tools');
        delete_option('lmfwc_db_version');
    }

    /**
     * Migration script.
     */
    public static function migrate()
    {
        $currentDatabaseVersion = get_option('lmfwc_db_version');

        if ($currentDatabaseVersion != self::DB_VERSION) {
            if ($currentDatabaseVersion < self::DB_VERSION) {
                Migration::up($currentDatabaseVersion);
            }

            if ($currentDatabaseVersion > self::DB_VERSION) {
                Migration::down($currentDatabaseVersion);
            }
        }
    }

    /**
     * Checks if all required plugin components are present.
     *
     * @throws Exception
     */
    public static function checkRequirements()
    {
        if (version_compare(phpversion(), '5.3.29', '<=')) {
            throw new Exception('PHP 5.3 or lower detected. License Manager for WooCommerce requires PHP 5.6 or greater.');
        }
    }

    /**
     * Create the necessary database tables.
     */
    public static function createTables()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $table1 = $wpdb->prefix . self::LICENSES_TABLE_NAME;
        $table2 = $wpdb->prefix . self::GENERATORS_TABLE_NAME;
        $table3 = $wpdb->prefix . self::API_KEYS_TABLE_NAME;
        $table4 = $wpdb->prefix . self::LICENSE_META_TABLE_NAME;
        $table5 = $wpdb->prefix . self::NODEFY_OPERATION_LOG_TABLE_NAME;
        $table6 = $wpdb->prefix . self::NODEFY_LICENSE_RELATE_ORDERS_TABLE_NAME;

        dbDelta("
            CREATE TABLE IF NOT EXISTS $table1 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `product_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `license_key` LONGTEXT NOT NULL,
                `hash` LONGTEXT NOT NULL,
                `expires_at` DATETIME NULL DEFAULT NULL,
                `valid_for` INT(32) UNSIGNED NULL DEFAULT NULL,
                `source` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) UNSIGNED NOT NULL,
                `times_activated` INT(10) UNSIGNED NULL DEFAULT NULL,
                `times_activated_max` INT(10) UNSIGNED NULL DEFAULT NULL,
                `users_number` INT(10) UNSIGNED NULL DEFAULT NULL,
                `info` TEXT NOT NULL DEFAULT '' COMMENT 'server info: mac, ip',
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $homeserver_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table1."' AND column_name = 'homeserver'"  );
        if(empty($homeserver_row)){
            $wpdb->query("ALTER TABLE `".$table1."` ADD homeserver VARCHAR(255) AFTER info");
        }

        $product_info_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table1."' AND column_name = 'product_info'"  );
        if(empty($product_info_row)){
            $wpdb->query("ALTER TABLE `".$table1."` ADD product_info TEXT AFTER info");
        }

        $activated_at_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table1."' AND column_name = 'activated_at'"  );
        if(empty($activated_at_row)){
            $wpdb->query("ALTER TABLE `".$table1."` ADD activated_at DATETIME AFTER homeserver");
        }

        $deactivated_at_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table1."' AND column_name = 'deactivated_at'"  );
        if(empty($deactivated_at_row)){
            $wpdb->query("ALTER TABLE `".$table1."` ADD deactivated_at DATETIME AFTER homeserver");
        }

        dbDelta("
            CREATE TABLE IF NOT EXISTS $table2 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `charset` VARCHAR(255) NOT NULL,
                `chunks` INT(10) UNSIGNED NOT NULL,
                `chunk_length` INT(10) UNSIGNED NOT NULL,
                `times_activated_max` INT(10) UNSIGNED NULL DEFAULT NULL,
                `users_number` INT(10) UNSIGNED NULL DEFAULT NULL,
                `separator` VARCHAR(255) NULL DEFAULT NULL,
                `prefix` VARCHAR(255) NULL DEFAULT NULL,
                `suffix` VARCHAR(255) NULL DEFAULT NULL,
                `expires_in` INT(10) UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        dbDelta("
            CREATE TABLE IF NOT EXISTS $table3 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `description` VARCHAR(200) NULL DEFAULT NULL,
                `permissions` VARCHAR(10) NOT NULL,
                `consumer_key` CHAR(64) NOT NULL,
                `consumer_secret` CHAR(43) NOT NULL,
                `nonces` LONGTEXT NULL,
                `truncated_key` CHAR(7) NOT NULL,
                `last_access` DATETIME NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `consumer_key` (`consumer_key`),
                INDEX `consumer_secret` (`consumer_secret`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        dbDelta("
            CREATE TABLE IF NOT EXISTS $table4 (
                `meta_id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED DEFAULT 0 NOT NULL,
                `meta_key` VARCHAR(255) NULL,
                `meta_value` LONGTEXT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`meta_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        dbDelta("
            CREATE TABLE IF NOT EXISTS $table5 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `order_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `product_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `operation` VARCHAR(255) NULL,
                `info` TEXT NOT NULL DEFAULT '' COMMENT 'server info: mac, ip',
                `note` LONGTEXT NULL,
                `license_backup` LONGTEXT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        dbDelta("
            CREATE TABLE IF NOT EXISTS $table6 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `order_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    /**
     * Sets up the default folder structure and creates the default files,
     * if needed.
     *
     * @throws EnvironmentIsBrokenException
     */
    public static function setDefaultFilesAndFolders()
    {
        /**
         * When the cryptographic secrets are loaded into these constants,
         * no other files are needed.
         *
         * @see https://www.licensemanager.at/docs/handbook/setup/security/
         */
        if (defined('LMFWC_PLUGIN_SECRET') && defined('LMFWC_PLUGIN_DEFUSE')) {
            return;
        }

        $uploads      = wp_upload_dir(null, false);
        $dirLmfwc     = $uploads['basedir'] . '/lmfwc-files';
        $fileHtaccess = $dirLmfwc . '/.htaccess';
        $fileDefuse   = $dirLmfwc . '/defuse.txt';
        $fileSecret   = $dirLmfwc . '/secret.txt';

        $oldUmask = umask(0);

        // wp-contents/lmfwc-files/
        if (!file_exists($dirLmfwc)) {
            @mkdir($dirLmfwc, 0775, true);
        } else {
            $permsDirLmfwc = substr(sprintf('%o', fileperms($dirLmfwc)), -4);

            if ($permsDirLmfwc != '0775') {
                @chmod($permsDirLmfwc, 0775);
            }
        }

        // wp-contents/lmfwc-files/.htaccess
        if (!file_exists($fileHtaccess)) {
            $fileHandle = @fopen($fileHtaccess, 'w');

            if ($fileHandle) {
                fwrite($fileHandle, 'deny from all');
                fclose($fileHandle);
            }

            @chmod($fileHtaccess, 0664);
        } else {
            $permsFileHtaccess = substr(sprintf('%o', fileperms($fileHtaccess)), -4);

            if ($permsFileHtaccess != '0664') {
                @chmod($permsFileHtaccess, 0664);
            }
        }

        // wp-contents/lmfwc-files/defuse.txt
        if (!file_exists($fileDefuse)) {
            $defuse = DefuseCryptoKey::createNewRandomKey();
            $fileHandle = @fopen($fileDefuse, 'w');

            if ($fileHandle) {
                fwrite($fileHandle, $defuse->saveToAsciiSafeString());
                fclose($fileHandle);
            }

            @chmod($fileDefuse, 0664);
        } else {
            $permsFileDefuse = substr(sprintf('%o', fileperms($fileDefuse)), -4);

            if ($permsFileDefuse != '0664') {
                @chmod($permsFileDefuse, 0664);
            }
        }

        // wp-contents/lmfwc-files/secret.txt
        if (!file_exists($fileSecret)) {
            $fileHandle = @fopen($fileSecret, 'w');

            if ($fileHandle) {
                fwrite($fileHandle, bin2hex(openssl_random_pseudo_bytes(32)));
                fclose($fileHandle);
            }

            @chmod($fileSecret, 0664);
        } else {
            $permsFileSecret = substr(sprintf('%o', fileperms($fileSecret)), -4);

            if ($permsFileSecret != '0664') {
                @chmod($permsFileSecret, 0664);
            }
        }

        umask($oldUmask);
    }

    /**
     * Set the default plugin options.
     */
    public static function setDefaultSettings()
    {
        $defaultSettingsGeneral = array(
            'lmfwc_hide_license_keys' => 0,
            'lmfwc_auto_delivery' => 1,
            'lmfwc_disable_api_ssl' => 0,
            'lmfwc_enabled_api_routes' => array(
                '000' => '1',
                '001' => '1',
                '002' => '1',
                '003' => '1',
                '004' => '1',
                '005' => '1',
                '006' => '1',
                '007' => '1',
                '008' => '1',
                '009' => '1',
                '010' => '1',
                '011' => '1',
                '012' => '1',
                '013' => '1',
                '014' => '1',
                '015' => '1',
                '016' => '1',
                '017' => '1',
                '018' => '1',
                '019' => '1',
                '020' => '1',
                '021' => '1'
            )
        );
        $defaultSettingsOrderStatus = array(
            'lmfwc_license_key_delivery_options' => array(
                'wc-completed' => array(
                    'send' => '1'
                )
            )
        );
        $defaultSettingsTools = array(
            'lmfwc_csv_export_columns' => array(
                'id'                  => '1',
                'order_id'            => '1',
                'order_num'           => '1',
                'product_id'          => '1',
                'product_name'        => '1',
                'user_id'             => '1',
                'user_name'           => '1',
                'user_email'          => '1',
                'license_key'         => '1',
                'expires_at'          => '1',
                'valid_for'           => '1',
                // 'status'              => '1',
                'times_activated'     => '1',
                'times_activated_max' => '1',
                'activated_at'        => '1',
                'deactivated_at'      => '1',
                'created_at'          => '1',
                'created_by'          => '1',
                'updated_at'          => '1',
                'updated_by'          => '1',
            )
        );

        // The defaults for the Setting API.
        update_option('lmfwc_settings_general', $defaultSettingsGeneral);
        update_option('lmfwc_settings_order_status', $defaultSettingsOrderStatus);
        update_option('lmfwc_settings_tools', $defaultSettingsTools);
        update_option('lmfwc_db_version', self::DB_VERSION);
    }
}
