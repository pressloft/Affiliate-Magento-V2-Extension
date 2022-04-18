<?php

namespace PressLoft\Affiliate\Plugin\Api;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PressLoft\Affiliate\Model\Affiliate;
use PressLoft\Affiliate\Service\CreateAffiliateService;
use PressLoft\Affiliate\Helper\Config;

/**
 * API Endpoint plugin to save Affiliate data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GuestPaymentInformationManagementInterfacePlugin
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
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $orderId
     * @return string
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws FailureToSendException
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
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
