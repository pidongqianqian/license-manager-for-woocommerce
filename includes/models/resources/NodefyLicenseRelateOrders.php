<?php

namespace LicenseManagerForWooCommerce\Models\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceModel as AbstractResourceModel;
use LicenseManagerForWooCommerce\Interfaces\Model as ModelInterface;
use stdClass;

defined('ABSPATH') || exit;

class NodefyLicenseRelateOrders extends AbstractResourceModel implements ModelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $licenseId;

    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $createdAt;

    /**
     * @var int
     */
    protected $createdBy;

    /**
     * licenseRelateOrder constructor.
     *
     * @param stdClass $licenseRelateOrder
     */
    public function __construct($licenseRelateOrder)
    {
        if (!$licenseRelateOrder instanceof stdClass) {
            return;
        }

        $this->id                = $licenseRelateOrder->id           === null ? null : intval($licenseRelateOrder->id);
        $this->licenseId         = $licenseRelateOrder->license_id   === null ? null : intval($licenseRelateOrder->license_id);
        $this->orderId           = $licenseRelateOrder->order_id     === null ? null : intval($licenseRelateOrder->order_id);
        $this->createdAt         = $licenseRelateOrder->created_at;
        $this->createdBy         = $licenseRelateOrder->created_by   === null ? null : intval($licenseRelateOrder->created_by);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getLicenseId()
    {
        return $this->licenseId;
    }

    /**
     * @param int $licenseId
     */
    public function setLicenseId($licenseId)
    {
        $this->licenseId = $licenseId;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param int $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    
    
}
