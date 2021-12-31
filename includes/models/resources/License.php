<?php

namespace LicenseManagerForWooCommerce\Models\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceModel as AbstractResourceModel;
use LicenseManagerForWooCommerce\Interfaces\Model as ModelInterface;
use stdClass;

defined('ABSPATH') || exit;

class License extends AbstractResourceModel implements ModelInterface
{
    /**
     * @var int
     */
    protected $id;

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
    protected $licenseKey;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $expiresAt;

    /**
     * @var int
     */
    protected $validFor;

    /**
     * @var int
     */
    protected $source;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $timesActivated;

    /**
     * @var int
     */
    protected $timesActivatedMax;

    /**
     * @var string
     */
    protected $activatedAt;

    /**
     * @var string
     */
    protected $deactivatedAt;

    /**
     * @var string
     */
    protected $createdAt;

    /**
     * @var int
     */
    protected $createdBy;

    /**
     * @var string
     */
    protected $updatedAt;

    /**
     * @var int
     */
    protected $updatedBy;

    /**
     * @var int
     */
    protected $usersNumber;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var string
     */
    protected $homeserver;

    /**
     * License constructor.
     *
     * @param stdClass $license
     */
    public function __construct($license)
    {
        if (!$license instanceof stdClass) {
            return;
        }

        $this->id                = $license->id         === null ? null : intval($license->id);
        $this->orderId           = $license->order_id   === null ? null : intval($license->order_id);
        $this->productId         = $license->product_id === null ? null : intval($license->product_id);
        $this->userId            = $license->user_id    === null ? null : intval($license->user_id);
        $this->licenseKey        = $license->license_key;
        $this->hash              = $license->hash;
        $this->expiresAt         = $license->expires_at;
        $this->validFor          = $license->valid_for           === null ? null : intval($license->valid_for);
        $this->source            = $license->source              === null ? null : intval($license->source);
        $this->status            = $license->status              === null ? null : intval($license->status);
        $this->timesActivated    = $license->times_activated     === null ? null : intval($license->times_activated);
        $this->timesActivatedMax = $license->times_activated_max === null ? null : intval($license->times_activated_max);
        $this->usersNumber       = $license->users_number        === null ? null : intval($license->users_number);
        $this->info              = $license->info;
        $this->homeserver        = $license->homeserver;
        $this->productInfo       = $license->product_info;
        $this->activatedAt       = $license->activated_at;
        $this->deactivatedAt     = $license->deactivated_at;
        $this->createdAt         = $license->created_at;
        $this->createdBy         = $license->created_by === null ? null : intval($license->created_by);
        $this->updatedAt         = $license->updated_at;
        $this->updatedBy         = $license->updated_by === null ? null : intval($license->updated_by);
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
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     */
    public function setLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;
    }

    /**
     * @return string
     */
    public function getDecryptedLicenseKey()
    {
        return apply_filters('lmfwc_decrypt', $this->licenseKey);
    }

    /**
     * @return string
     */
    public function getShortDecryptedLicenseKey()
    {
        return '...'.substr(apply_filters('lmfwc_decrypt', $this->licenseKey), -13);
    }


    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param string $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return int
     */
    public function getValidFor()
    {
        return $this->validFor;
    }

    /**
     * @param int $validFor
     */
    public function setValidFor($validFor)
    {
        $this->validFor = $validFor;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param int $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getTimesActivated()
    {
        return $this->timesActivated;
    }

    /**
     * @param int $timesActivated
     */
    public function setTimesActivated($timesActivated)
    {
        $this->timesActivated = $timesActivated;
    }

    /**
     * @return int
     */
    public function getTimesActivatedMax()
    {
        return $this->timesActivatedMax;
    }

    /**
     * @param int $timesActivatedMax
     */
    public function setTimesActivatedMax($timesActivatedMax)
    {
        $this->timesActivatedMax = $timesActivatedMax;
    }

    /**
     * @return string
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * @param string $activatedAt
     */
    public function setActivatedAt($activatedAt)
    {
        $this->activatedAt = $activatedAt;
    }

    /**
     * @return string
     */
    public function getDeactivatedAt()
    {
        return $this->deactivatedAt;
    }

    /**
     * @param string $deactivatedAt
     */
    public function setDeactivatedAt($deactivatedAt)
    {
        $this->deactivatedAt = $deactivatedAt;
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

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return int
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param int $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return int
     */
    public function getUsersNumber()
    {
        return $this->usersNumber;
    }

    /**
     * @param int $usersNumber
     */
    public function setUsersNumber($usersNumber)
    {
        $this->usersNumber = $usersNumber;
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
    public function getHomeserver()
    {
        return $this->homeserver;
    }

    /**
     * @param string $homeserver
     */
    public function setHomeserver($homeserver)
    {
        $this->homeserver = $homeserver;
    }

     /**
     * @return string
     */
    public function getProductInfo()
    {
        return $this->productInfo;
    }

    /**
     * @param string $productInfo
     */
    public function setProductInfo($productInfo)
    {
        $this->productInfo = $productInfo;
    }
}
