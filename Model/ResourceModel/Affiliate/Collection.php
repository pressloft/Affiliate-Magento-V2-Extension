<?php

namespace PressLoft\Affiliate\Model\ResourceModel\Affiliate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \PressLoft\Affiliate\Model\Affiliate::class,
            \PressLoft\Affiliate\Model\ResourceModel\Affiliate::class
        );
    }
}
