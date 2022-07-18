<?php

namespace PressLoft\Affiliate\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\Collection;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\CollectionFactory;

/**
 * @method string|null getToken()
 * @method int|string|null getOrderId()
 * @method int|string|null getFailuresNum()
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class AffiliateSchedule extends AbstractModel
{
    /**
     * Statuses
     */
    public const STATUS_NEW = 'new';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_MISSED = 'missed';
    public const STATUS_ERROR = 'error';

    private const AFFILIATE_ID = 'affiliate_id';
    private const STATUS = 'status';
    private const ORDER = 'order';
    public const ORDER_ID = 'order_id';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModel\AffiliateSchedule
     */
    protected $_resource;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel\AffiliateSchedule $resourceModel
     * @param Collection<\PressLoft\Affiliate\Model\AffiliateSchedule> $collection
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
     * Initialize model
     *
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
     * @return AffiliateSchedule[]
     */
    public function getPendingItems(): array
    {
        $collection = $this->collectionFactory->create()->getItemsForSendData();
        $ids = $collection->getAllIds();
        /** @var AffiliateSchedule[] $items */
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
        $this->setData(self::AFFILIATE_ID, $value);
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
        $this->setData(self::STATUS, $value);
        return $this;
    }

    /**
     * Order setter
     *
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order): AffiliateSchedule
    {
        $this->setData(self::ORDER, $order);
        return $this;
    }

    /**
     * Order getter
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        return $this->_getData(self::ORDER);
    }
}
