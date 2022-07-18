<?php

namespace PressLoft\Affiliate\Helper;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * Path to configuration
     */
    private const XML_PATH_ENABLED = 'affiliate/affiliate/enable';
    private const XML_PATH_AFFILIATE_ID = 'affiliate/affiliate/affiliate_id';
    public const XML_PATH_SYNCHRONIZED = 'affiliate/affiliate/synchronized';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Check if enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get affiliate id
     *
     * @return string
     */
    public function getAffiliateId(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AFFILIATE_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get last synchronized
     *
     * @return string|null
     */
    public function getSynchronized(): ?string
    {
        $collection = $this->collectionFactory->create();
        $collection->addScopeFilter('default', 0, 'affiliate')
            ->addFieldToFilter('path', self::XML_PATH_SYNCHRONIZED);
        return $collection->getFirstItem()->getValue();
    }
}
