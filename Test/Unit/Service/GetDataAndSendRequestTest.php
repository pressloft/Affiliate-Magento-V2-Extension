<?php

namespace PressLoft\Affiliate\Test\Unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Model\AffiliateScheduleFactory;
use PressLoft\Affiliate\Model\AffiliateSchedule;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule as ResourceModel;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\Collection;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\CollectionFactory;
use PressLoft\Affiliate\Service\ApiRequest;
use PressLoft\Affiliate\Service\GetDataAndSendRequest;

class GetDataAndSendRequestTest extends TestCase
{
    /**
     * @var ClientFactory|MockObject
     */
    private $clientFactory;

    /**
     * @var ResponseFactory|MockObject
     */
    private $responseFactory;

    /**
     * @var AffiliateSchedule|MockObject
     */
    private $resourceModel;

    /**
     * @var MockObject|Config
     */
    private $helper;

    /**
     * @var MockObject|AffiliateScheduleFactory
     */
    private $affiliateScheduleFactory;

    /**
     * @var MockObject|CollectionFactory
     */
    private $collectionFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->clientFactory = $this->getMockBuilder(ClientFactory::class)
        ->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();
        $this->responseFactory = $this->getMockBuilder(ResponseFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resourceModel = $this->getMockBuilder(ResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->affiliateScheduleFactory = $this->getMockBuilder(AffiliateScheduleFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $affiliateId = '12345';
        $configApi = ['config' => [
            'base_uri' => ApiRequest::API_REQUEST_URI
        ]];
        $uriEndpoint = GetDataAndSendRequest::API_REQUEST_ENDPOINT;
        $params = [
            RequestOptions::JSON => [
                "token" => "8C6B183C19C81E16C2A2261C72254",
                "affiliate_id"=> "12345",
                "order_id"=>"6789",
                "order_details" => [
                    "order_subtotal"=> "34.99",
                    "discount" => "0.00",
                    "tax" => "2.00",
                    "postage" => "5.00",
                    "order_total" => "39.99"
                    ]
                ]
        ];
        $statusCode = 200;

        $this->helper->expects($this->any())
            ->method('getAffiliateId')
            ->willReturn($affiliateId);

        /** @var Collection|MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);

        $collection->method('getItemsForSendData')
            ->willReturn($collection);
        $collection->method('getItems')
            ->willReturn($collection);

        /** @var AffiliateSchedule|MockObject $affiateSchedule */
        $affiateSchedule = $this->getMockBuilder(AffiliateSchedule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->affiliateScheduleFactory->expects($this->any())
            ->method('create')
            ->willReturn($affiateSchedule);
        $affiateSchedule->method('getPendingItems');

        /** @var Client|MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientFactory->method('create')
            ->willReturn($client);
        $client->method('request')
            ->with($configApi, $uriEndpoint, $params)
            ->willReturn($response);
        $response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->assertNotEmpty($response);
    }
}
