<?php

namespace LicenseManagerForWooCommerce\Repositories\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Enums\ColumnType as ColumnTypeEnum;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\NodefyOperationLog as NodefyOperationLogModel;

defined('ABSPATH') || exit;

class NodefyOperationLog extends AbstractResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var string
     */
    const TABLE = 'lmfwc_nodefy_operation_log';

    /**
     * Country constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->table      = $wpdb->prefix . self::TABLE;
        $this->primaryKey = 'id';
        $this->model      = NodefyOperationLogModel::class;

        $this->mapping    = array(
            'license_id'          => ColumnTypeEnum::BIGINT,
            'order_id'            => ColumnTypeEnum::BIGINT,
            'product_id'          => ColumnTypeEnum::BIGINT,
            'user_id'             => ColumnTypeEnum::BIGINT,
            'operation'           => ColumnTypeEnum::VARCHAR,
            'info'                => ColumnTypeEnum::VARCHAR,
            'note'                => ColumnTypeEnum::LONGTEXT,
            'license_backup'      => ColumnTypeEnum::LONGTEXT,
        );
    }
}
