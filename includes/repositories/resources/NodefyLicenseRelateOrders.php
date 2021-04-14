<?php

namespace LicenseManagerForWooCommerce\Repositories\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Enums\ColumnType as ColumnTypeEnum;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\NodefyLicenseRelateOrders as NodefyLicenseRelateOrdersModel;

defined('ABSPATH') || exit;

class NodefyLicenseRelateOrders extends AbstractResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var string
     */
    const TABLE = 'lmfwc_nodefy_license_relate_orders';

    /**
     * Country constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->table      = $wpdb->prefix . self::TABLE;
        $this->primaryKey = 'id';
        $this->model      = NodefyLicenseRelateOrdersModel::class;

        $this->mapping    = array(
            'license_id'          => ColumnTypeEnum::BIGINT,
            'order_id'            => ColumnTypeEnum::BIGINT,
        );
    }
}
