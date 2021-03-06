<?php

namespace PressLoft\Affiliate\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\Data\OrderInterface;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Model\AffiliateSchedule;
use PressLoft\Affiliate\Model\AffiliateScheduleFactory;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule as AffiliateScheduleResourceModel;

class GetDataAndSendRequest extends ApiRequest
{
    /**
     * Maximum number of errors
     */
    private const MAX_NUMBER_ERRORS = 3;

    /**
     * API request endpoint to PressLoft
     */
    public const API_REQUEST_ENDPOINT = 'sale';

    /**
     * Fields name for params
     */
    public const TOKEN = 'token';
    private const AFFILIATE_ID = 'affiliate_id';
    private const ORDER_ID = 'order_id';
    private const ORDER_DETAILS = 'order_details';
    private const ORDER_DATETIME = 'order_datetime';
    private const ORDER_SUBTOTAL = 'order_subtotal';
    private const DISCOUNT = 'discount';
    private const TAX = 'tax';
    private const POSTAGE = 'postage';
    private const ORDER_TOTAL = 'order_total';
    private const ORDER_CURRENCY = 'order_currency';
    private const ORDER_LINES = 'order_lines';
    private const ORDER_LINE_PREFIX = 'order_line_';
    private const ORDER_LINE_SKU = 'sku';
    private const ORDER_LINE_PRODUCT_NAME = 'product_name';
    private const ORDER_LINE_QUANTITY = 'quantity';
    private const ORDER_LINE_UNIT_PRICE = 'unit_price';
    private const ORDER_LINE_TOTAL = 'line_total';

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
     * @var string
     */
    private $affiliateId;

    /**
     * GetDataAndSendRequest constructor
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
        parent::__construct($clientFactory, $responseFactory);
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
     * @param AffiliateSchedule $item
     * @return void
     * @throws AlreadyExistsException
     */
    private function processingData($item)
    {
        $params = $this->prepareParams($item);

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
        $item->setData('updated_at', null);
        $this->resourceModel->save($item);
    }

    /**
     * Prepare params for send request
     *
     * @param AffiliateSchedule $scheduleItem
     * @return array<string, array>
     */
    private function prepareParams($scheduleItem)
    {
        $order = $scheduleItem->getOrder();
        if ($order === null) {
            return [];
        }

        $orderDetails = [
            self::ORDER_SUBTOTAL => $order->getSubtotalInclTax(),
            self::ORDER_DATETIME => $order->getCreatedAt(),
            self::DISCOUNT => $order->getDiscountAmount(),
            self::TAX => $order->getTaxAmount(),
            self::POSTAGE => $order->getShippingAmount(),
            self::ORDER_TOTAL => $order->getGrandTotal(),
            self::ORDER_CURRENCY => $order->getOrderCurrencyCode(),
            self::ORDER_LINES => $this->prepareOrderItems($order)
        ];

        $params = [
            RequestOptions::JSON => [
                self::TOKEN => $scheduleItem->getToken(),
                self::AFFILIATE_ID => $this->affiliateId,
                self::ORDER_ID => $order->getIncrementId(),
                self::ORDER_DETAILS => $orderDetails
            ]
        ];

        return $params;
    }

    /**
     * Getting information about products
     *
     * @param OrderInterface $order
     * @return array<string, array>
     */
    private function prepareOrderItems($order)
    {
        $orderLines = [];
        $orderLineId = 1;
        foreach ($order->getItems() as $orderItem) {
            $orderLines[self::ORDER_LINE_PREFIX . $orderLineId] = [
                self::ORDER_LINE_SKU => $orderItem->getSku(),
                self::ORDER_LINE_PRODUCT_NAME => $orderItem->getName(),
                self::ORDER_LINE_QUANTITY => $orderItem->getQtyOrdered(),
                self::ORDER_LINE_UNIT_PRICE => $orderItem->getPrice(),
                self::ORDER_LINE_TOTAL => $orderItem->getRowTotal()
            ];

            ++$orderLineId;
        }

        return $orderLines;
    }
}
