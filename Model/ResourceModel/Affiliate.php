<?php

namespace PressLoft\Affiliate\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Affiliate extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('sales_order_affiliate', 'id');
    }
}
