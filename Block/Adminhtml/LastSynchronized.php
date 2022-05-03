<?php

namespace PressLoft\Affiliate\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use PressLoft\Affiliate\Helper\Config;
use Magento\Framework\Serialize\SerializerInterface;

class LastSynchronized extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Config $config
     * @param SerializerInterface $serializer
     * @param array<mixed> $data
     */
    public function __construct(Context $context, Config $config, SerializerInterface $serializer, array $data = [])
    {
        $this->config = $config;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Get last synchronized for template
     *
     * @return array<mixed>|bool|float|int|string|null
     */
    public function getLastSynchronized()
    {
        $data = $this->config->getSynchronized();
        if (!empty($data)) {
            return $this->serializer->unserialize($data);
        }
        return $data;
    }
}
