<?php

namespace PressLoft\Affiliate\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\Collection;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class AffiliateSchedule extends AbstractModel
{
    /**
     * Statuses
     */
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_MISSED = 'missed';
    const STATUS_ERROR = 'error';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModel\AffiliateSchedule
     */
    protected $_resource;

    /**
     * Name of object affiliate id field
     *
     * @var string
     */
    protected $orderId = 'affiliate_id';

    /**
     * Name of object status field
     *
     * @var string
     */
    protected $status = 'status';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel\AffiliateSchedule $resourceModel
     * @param Collection $collection
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        Registry                         $registry,
        CollectionFactory                $collectionFactory,
        ResourceModel\AffiliateSchedule  $resourceModel,
        Collection $collection,
        array                            $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $resourceModel, $collection, $data);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\AffiliateSchedule::class);
    }

    /**
     * Prepare block's statuses
     *
     * @return array<string>
     */
    public function getAvailableStatuses(): array
    {
        return [
            self::STATUS_NEW => __('New'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_SUCCESS => __('Success'),
            self::STATUS_MISSED => __('Missed'),
            self::STATUS_ERROR => __('Error')
        ];
    }

    /**
     * Get items for cronjob
     *
     * @return array<DataObject>
     */
    public function getPendingItems(): array
    {
        $collection = $this->collectionFactory->create()->getItemsForSendData();
        $ids = $collection->getAllIds();
        $items = $collection->getItems();
        $this->_resource->tryLockItems($ids);
        return $items;
    }

    /**
     * Affiliate id setter
     *
     * @param mixed $value
     * @return $this
     */
    public function setAffiliateId($value): AffiliateSchedule
    {
        $this->setData($this->orderId, $value);
        return $this;
    }

    /**
     * Status setter
     *
     * @param mixed $value
     * @return $this
     */
    public function setStatus($value): AffiliateSchedule
    {
        $this->setData($this->status, $value);
        return $this;
    }
}
