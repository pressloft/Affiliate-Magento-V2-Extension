<?php

namespace PressLoft\Affiliate\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class AffiliateSchedule extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('affiliate_schedule', 'id');
    }

    /**
     * Lock items for cronjob
     *
     * @param array<int> $ids
     * @return void
     */
    public function tryLockItems(array $ids): void
    {
        $connection = $this->getConnection();
        /** @var AdapterInterface $connection */
        $connection->update(
            $this->getTable('affiliate_schedule'),
            ['status' => \PressLoft\Affiliate\Model\AffiliateSchedule::STATUS_PENDING],
            ['id IN (?)' => $ids]
        );
    }
}
