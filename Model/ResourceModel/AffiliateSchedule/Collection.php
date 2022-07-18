<?php

namespace PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\Data\OrderInterface;
use PressLoft\Affiliate\Model\AffiliateSchedule;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Collection extends AbstractCollection
{
    /**
     * Maximum number of errors
     */
    private const MAX_NUMBER_OF_ERROR = 3;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            AffiliateSchedule::class,
            \PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule::class
        );
    }

    /**
     * Get items for cronjob with statuses error and new
     *
     * @return Collection<AffiliateSchedule>
     */
    public function getItemsForSendData(): Collection
    {
        $affiliateTable = $this->getTable('sales_order_affiliate');
        $salesOrderTable = $this->getTable('sales_order');
        $collection = $this
            ->join(
                $affiliateTable,
                'main_table.affiliate_id=' . $affiliateTable . '.id',
                ['token', AffiliateSchedule::ORDER_ID]
            )
            ->join(
                $salesOrderTable,
                $affiliateTable.'.order_id=' . $salesOrderTable . '.entity_id',
                [OrderInterface::BASE_TOTAL_DUE]
            );

        //load only fully paid orders
        $collection->getSelect()->where(
            'main_table.status IN (?)',
            [AffiliateSchedule::STATUS_NEW, AffiliateSchedule::STATUS_ERROR]
        )->where(
            'main_table.failures_num <= ?',
            self::MAX_NUMBER_OF_ERROR
        )->where(
            sprintf('%s.%s = ?', $salesOrderTable, OrderInterface::BASE_TOTAL_DUE),
            0.0000
        );

        return  $this->addOrderToItems($collection);
    }

    /**
     * Set order to schedule
     *
     * @param Collection<AffiliateSchedule> $scheduleCollection
     * @return Collection<AffiliateSchedule>
     */
    private function addOrderToItems(Collection $scheduleCollection)
    {
        $orderIds = $scheduleCollection->getColumnValues(AffiliateSchedule::ORDER_ID);
        if (empty($orderIds)) {
            return $scheduleCollection;
        }

        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToFilter(\Magento\Sales\Api\Data\OrderInterface::ENTITY_ID, ['in' => $orderIds]);

        foreach ($scheduleCollection as $schedule) {
            if ($schedule->getOrderId() === null) {
                continue;
            }
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $orderCollection->getItemById($schedule->getOrderId());
            $schedule->setOrder($order);
        }

        return $scheduleCollection;
    }
}
