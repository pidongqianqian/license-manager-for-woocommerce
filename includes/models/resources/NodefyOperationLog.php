<?php

namespace LicenseManagerForWooCommerce\Models\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceModel as AbstractResourceModel;
use LicenseManagerForWooCommerce\Interfaces\Model as ModelInterface;
use stdClass;

defined('ABSPATH') || exit;

class NodefyOperationLog extends AbstractResourceModel implements ModelInterface
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
     * @var int
     */
    protected $productId;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var string
     */
    protected $licenseBackup;

    /**
     * @var string
     */
    protected $createdAt;

    /**
     * @var int
     */
    protected $createdBy;

    /**
     * nodefyOperationLog constructor.
     *
     * @param stdClass $nodefyOperationLog
     */
    public function __construct($nodefyOperationLog)
    {
        if (!$nodefyOperationLog instanceof stdClass) {
            return;
        }

        $this->id                = $nodefyOperationLog->id           === null ? null : intval($nodefyOperationLog->id);
        $this->licenseId         = $nodefyOperationLog->license_id   === null ? null : intval($nodefyOperationLog->license_id);
        $this->orderId           = $nodefyOperationLog->order_id     === null ? null : intval($nodefyOperationLog->order_id);
        $this->productId         = $nodefyOperationLog->product_id   === null ? null : intval($nodefyOperationLog->product_id);
        $this->userId            = $nodefyOperationLog->user_id      === null ? null : intval($nodefyOperationLog->user_id);
        $this->operation         = $nodefyOperationLog->operation;
        $this->info              = $nodefyOperationLog->info;
        $this->note              = $nodefyOperationLog->note;
        $this->licenseBackup     = $nodefyOperationLog->licenseBackup;
        $this->createdAt         = $nodefyOperationLog->created_at;
        $this->createdBy         = $nodefyOperationLog->created_by === null ? null : intval($nodefyOperationLog->created_by);
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
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param string $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return string
     */
    public function getLicenseBackup()
    {
        return $this->licenseBackup;
    }

    /**
     * @param string $licenseBackup
     */
    public function setLicenseBackup($licenseBackup)
    {
        $this->licenseBackup = $licenseBackup;
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
