<?php

namespace PressLoft\Affiliate\Model;

use Magento\Framework\Model\AbstractModel;

class Affiliate extends AbstractModel
{
    /**
     * Cookie name
     */
    const TOKEN = 'token';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\PressLoft\Affiliate\Model\ResourceModel\Affiliate::class);
    }
}
