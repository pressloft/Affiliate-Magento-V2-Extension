<?php

namespace PressLoft\Affiliate\Plugin\Api;

use Ebizmarts\SagePaySuite\Api\Data\PiRequest as PiDataRequest;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use PressLoft\Affiliate\Model\Affiliate;
use PressLoft\Affiliate\Service\CreateAffiliateService;
use PressLoft\Affiliate\Helper\Config;

/**
 * API Endpoint plugin to save Affiliate data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PiRequestManagementPlugin
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
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @param CreateAffiliateService $createAffiliateService
     * @param Config $helper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param OrderInterface $orderInterface
     */
    public function __construct(
        CreateAffiliateService $createAffiliateService,
        Config $helper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        OrderInterface $orderInterface
    ) {
        $this->createAffiliateService = $createAffiliateService;
        $this->helper = $helper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->orderInterface = $orderInterface;
    }

    /**
     * Save data to database and remove cookies
     *
     * @param PiRequestManagement $subject
     * @param mixed $result
     * @param mixed $cartId
     * @param PiDataRequest $requestData
     * @return mixed $result
     * @throws AlreadyExistsException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        PiRequestManagement $subject,
        $result,
        $cartId,
        PiDataRequest $requestData
    ) {
        if ($this->helper->isEnabled()) {
            $token = $this->cookieManager->getCookie(Affiliate::TOKEN);
            if (!empty($token)) {
                $orderId = $this->getOrderId((string)$subject->getQuoteById($cartId)->getReservedOrderId());
                $this->createAffiliateService->save($orderId, $token);
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setPath('/');
                $this->cookieManager->deleteCookie(Affiliate::TOKEN, $metadata);
            }
        }
        return $result;
    }

    /**
     * Get order ID by increment ID
     *
     * @param string $incrementId
     * @return string
     *
     */
    private function getOrderId(string $incrementId): string
    {
        // @phpstan-ignore-next-line
        return (string)$this->orderInterface->loadByIncrementId($incrementId)->getId();
    }
}
