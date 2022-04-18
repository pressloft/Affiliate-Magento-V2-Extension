<?php

namespace PressLoft\Affiliate\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Webapi\Rest\Request;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Model\AffiliateSchedule;
use PressLoft\Affiliate\Model\AffiliateScheduleFactory;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule as AffiliateScheduleResourceModel;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GetDataAndSendRequest
{
    /**
     * Maximum number of errors
     */
    const MAX_NUMBER_ERRORS = 3;

    /**
     * Success status code from response
     */
    const SUCCESS_STATUS_CODE = 200;

    /**
     * API request URL
     */
    const API_REQUEST_URI = 'https://affiliates.pressloft.com/';

    /**
     * API request endpoint to PressLoft
     */
    const API_REQUEST_ENDPOINT = 'sale';

    /**
     * Fields name for params
     */
    const TOKEN = 'token';
    const AFFILIATE_ID = 'affiliate_id';
    const ORDER_ID = 'order_id';
    const ORDER_DETAILS = 'order_details';
    const ORDER_SUBTOTAL = 'order_subtotal';
    const DISCOUNT = 'discount';
    const TAX = 'tax';
    const POSTAGE = 'postage';
    const ORDER_TOTAL = 'order_total';

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var AffiliateScheduleResourceModel
     */
    private $resourceModel;

    /**
     * @var Config
     */
    private $helper;

    /**
     * @var AffiliateScheduleFactory
     */
    private $affiliateScheduleFactory;

    /**
     * Affiliate Id
     *
     * @var string
     */
    private $affiliateId;

    /**
     * GitApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param AffiliateScheduleResourceModel $resourceModel
     * @param Config $helper
     * @param AffiliateScheduleFactory $affiliateScheduleFactory
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        AffiliateScheduleResourceModel $resourceModel,
        Config $helper,
        AffiliateScheduleFactory $affiliateScheduleFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->resourceModel = $resourceModel;
        $this->helper = $helper;
        $this->affiliateScheduleFactory = $affiliateScheduleFactory;
    }

    /**
     * Prepare data for API and get response from API
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function execute(): void
    {
        $this->affiliateId = $this->helper->getAffiliateId();
        $items = $this->affiliateScheduleFactory->create()->getPendingItems();
        foreach ($items as $item) {
            $this->processingData($item);
        }
    }

    /**
     * Processing data and receiving a response from the API
     *
     * @param mixed $item
     * @return void
     * @throws AlreadyExistsException
     */
    private function processingData($item)
    {
        $params = [
            RequestOptions::JSON => [
                self::TOKEN => $item->getToken(),
                self::AFFILIATE_ID => $this->affiliateId,
                self::ORDER_ID => $item->getOrderId(),
                self::ORDER_DETAILS => [
                    self::ORDER_SUBTOTAL => $item->getSubtotalInclTax(),
                    self::DISCOUNT => $item->getDiscountAmount(),
                    self::TAX => $item->getTaxAmount(),
                    self::POSTAGE => $item->getShippingAmount(),
                    self::ORDER_TOTAL => $item->getGrandTotal()
                ]
            ]
        ];
        $response = $this->doRequest(static::API_REQUEST_ENDPOINT, $params);
        $status = $response->getStatusCode();
        if ($status == self::SUCCESS_STATUS_CODE) {
            $item->setData('status', AffiliateSchedule::STATUS_SUCCESS);
        } else {
            $failuresNum = $item->getFailuresNum();
            if ($item->getFailuresNum() == self::MAX_NUMBER_ERRORS) {
                $item->setData('status', AffiliateSchedule::STATUS_MISSED);
            } else {
                $item->setData('status', AffiliateSchedule::STATUS_ERROR);
            }
            $item->setData('failures_num', ++$failuresNum);
        }
        $this->resourceModel->save($item);
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array<mixed> $params
     * @param string $requestMethod
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_POST
    ): Response {
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => self::API_REQUEST_URI
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }
        /** @var Response $response */
        return $response;
    }
}
