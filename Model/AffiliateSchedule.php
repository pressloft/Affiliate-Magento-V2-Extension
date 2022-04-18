<?php

namespace PressLoft\Affiliate\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\Collection;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\CollectionFactory;

class AffiliateSchedule extends AbstractModel
{
    const STATUS_NEW = 'new';

    const STATUS_PENDING = 'pending';

    const STATUS_SUCCESS = 'success';

    const STATUS_MISSED = 'missed';

    const STATUS_ERROR = 'error';

    /**
     * @var ResourceModel\AffiliateSchedule
     */
    protected $resourceModel;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel\AffiliateSchedule $resourceModel
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        Registry                         $registry,
        AbstractResource                 $resource = null,
        AbstractDb                       $resourceCollection = null,
        CollectionFactory                $collectionFactory,
        ResourceModel\AffiliateSchedule  $resourceModel,
        array                            $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
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
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create()->getItemsForSendData();
        $ids = $collection->getAllIds();
        $items = $collection->getItems();
        $this->resourceModel->tryLockItems($ids);
        return $items;
    }
}
