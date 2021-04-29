<?php

namespace LicenseManagerForWooCommerce\Repositories\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Enums\ColumnType as ColumnTypeEnum;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\NodefyApiLog as NodefyApiLogModel;

defined('ABSPATH') || exit;

class NodefyApiLog extends AbstractResourceRepository implements ResourceRepositoryInterface
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
        $this->model      = NodefyApiLogModel::class;

        $this->mapping    = array(
            'name'         => ColumnTypeEnum::VARCHAR,
            'uri'          => ColumnTypeEnum::VARCHAR,
            'type'         => ColumnTypeEnum::VARCHAR,
            'request'      => ColumnTypeEnum::VARCHAR,
            'response'     => ColumnTypeEnum::VARCHAR,
        );
    }
}
