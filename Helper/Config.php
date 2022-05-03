<?php

namespace PressLoft\Affiliate\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * Path to configuration
     */
    const XML_PATH_ENABLED = 'affiliate/affiliate/enable';
    const XML_PATH_AFFILIATE_ID = 'affiliate/affiliate/affiliate_id';
    const XML_PATH_SYNCHRONIZED = 'affiliate/affiliate/synchronized';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
        return $this->scopeConfig->getValue(
            self::XML_PATH_SYNCHRONIZED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
