<?php

namespace PressLoft\Affiliate\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Affiliate extends AbstractModel
{
    /**
     * Name of object order id field
     *
     * @var string
     */
    protected $orderId = 'order_id';

    /**
     * Name of object token field
     *
     * @var string
     */
    protected $token = 'token';

    /**
     * Cookie name
     */
    const TOKEN = 'token';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Affiliate::class);
    }

    /**
     * Order id setter
     *
     * @param mixed $value
     * @return $this
     */
    public function setOrderId($value): Affiliate
    {
        $this->setData($this->orderId, $value);
        return $this;
    }

    /**
     * Token setter
     *
     * @param mixed $value
     * @return $this
     */
    public function setToken($value): Affiliate
    {
        $this->setData($this->token, $value);
        return $this;
    }
}
