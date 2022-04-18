<?php

namespace PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PressLoft\Affiliate\Model\AffiliateSchedule;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Collection extends AbstractCollection
{
    /**
     * Maximum number of errors
     */
    const MAX_NUMBER_OF_ERROR = 3;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \PressLoft\Affiliate\Model\AffiliateSchedule::class,
            \PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule::class
        );
    }

    /**
     * Get items for cronjob with statuses error and new
     *
     * @return Collection
     */
    public function getItemsForSendData(): Collection
    {
        $affiliateTable = $this->getTable('sales_order_affiliate');
        $salesOrderTable = $this->getTable('sales_order');
        $collection = $this->addFieldToSelect('*')
            ->join($affiliateTable, 'main_table.affiliate_id=' . $affiliateTable . '.id', ['token', 'order_id'])
            ->join(
                $salesOrderTable,
                $affiliateTable.'.order_id=' . $salesOrderTable . '.entity_id',
                ['subtotal_incl_tax', 'discount_amount', 'tax_amount', 'shipping_amount', 'grand_total']
            );
        $collection->getSelect()->where(
            'main_table.status IN (?)',
            [AffiliateSchedule::STATUS_NEW, AffiliateSchedule::STATUS_ERROR]
        )->where('main_table.failures_num <= ?', self::MAX_NUMBER_OF_ERROR);

        return $collection;
    }
}
