<?php

namespace PressLoft\Affiliate\Plugin\Api;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Model\Affiliate;
use PressLoft\Affiliate\Service\CreateAffiliateService;

/**
 * API Endpoint plugin to save Affiliate data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PaymentInformationManagementInterfacePlugin
{
    /**
     * @var CreateAffiliateService
     */
    protected $createAffiliateService;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var Config
     */
    protected $helper;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @param CreateAffiliateService $createAffiliateService
     * @param Config $helper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        CreateAffiliateService $createAffiliateService,
        Config                   $helper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->createAffiliateService = $createAffiliateService;
        $this->helper = $helper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Set cookies after placing order
     *
     * @param PaymentInformationManagementInterface $subject
     * @param string $orderId
     * @return string
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws FailureToSendException
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        string $orderId
    ): string {
        if ($this->helper->isEnabled()) {
            $token = $this->cookieManager->getCookie(Affiliate::TOKEN);
            if (!empty($token)) {
                $this->createAffiliateService->save($orderId, $token);
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setPath('/');
                $this->cookieManager->deleteCookie(Affiliate::TOKEN, $metadata);
            }
        }
        return $orderId;
    }
}
