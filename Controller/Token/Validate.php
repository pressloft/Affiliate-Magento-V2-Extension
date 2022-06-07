<?php

namespace PressLoft\Affiliate\Controller\Token;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PressLoft\Affiliate\Model\Affiliate;
use PressLoft\Affiliate\Service\CookiePeriodCheck;
use Psr\Log\LoggerInterface;

class Validate implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CookiePeriodCheck
     */
    private $cookiePeriodCheck;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param CookiePeriodCheck $cookiePeriodCheck
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        CookiePeriodCheck $cookiePeriodCheck,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cookiePeriodCheck = $cookiePeriodCheck;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $token = $this->request->getParam(Affiliate::TOKEN);

        if (!$token) {
            $result = [
                'success' => false,
                'message' => __('Invalid token.')
            ];
        } else {
            $result = $this->processToken($token);
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * @param string $token
     * @return array<string, bool|string>
     */
    private function processToken(string $token): array
    {
        $cookiePeriod = $this->cookiePeriodCheck->execute($token);

        if (!$cookiePeriod) {
            return [
                'success' => false,
                'message' => __('Token is invalid.')
            ];
        }

        try {
            $this->cookieManager->setPublicCookie(
                Affiliate::TOKEN,
                $token,
                $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setPath('/')
                    ->setDuration($cookiePeriod)
            );
            $result = [
                'success' => true,
                'message' => __('Token is valid.')
            ];
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $result = [
                'success' => false,
                'message' => __('Error processing Token.')
            ];
        }

        return $result;
    }
}
