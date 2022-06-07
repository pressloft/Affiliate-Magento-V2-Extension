<?php

namespace PressLoft\Affiliate\Test\Unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Service\CookiePeriodCheck;

class CookiePeriodCheckTest extends TestCase
{
    /**
     * @var CookiePeriodCheck
     */
    protected $object;

    /**
     * @var ClientFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $clientFactory;

    /**
     * @var ResponseFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Config
     */
    protected $config;

    /**
     * @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $serialize;

    /**
     * @var Client|MockObject
     */
    protected $client;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactory = $this->getMockBuilder(ResponseFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serialize = $this->getMockBuilder(SerializerInterface::class)->getMockForAbstractClass();

        /** @var Client|MockObject $client */
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new CookiePeriodCheck(
            $this->clientFactory,
            $this->responseFactory,
            $this->config,
            $this->serialize
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithoutEnable(): void
    {
        $token = 'token';
        $result = true;
        $uriEndpoint = 'cookieperiod';
        $params = [
            'query' => [
                'token' => $token
            ]
        ];

        $this->config->expects($this->any())
            ->method('isEnabled')
            ->willReturn(false);

        /** @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientFactory->expects($this->never())
            ->method('create')
            ->with([
                'config' => [
                    'base_uri' => 'https://affiliates.pressloft.com/'
                ]
            ])
            ->willReturn($this->client);

        $this->client->expects($this->never())
            ->method('request')
            ->with($uriEndpoint, $params, 'GET')
            ->willReturn($response);

        $response->expects($this->never())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->assertNotNull($response);
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $token = 'token';
        $result = true;
        $uriEndpoint = 'cookieperiod';
        $params = [
            'query' => [
                'token' => $token
            ]
        ];

        $this->config->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        /** @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientFactory->expects($this->any())
            ->method('create')
            ->with([
                'config' => [
                    'base_uri' => 'https://affiliates.pressloft.com/'
                ]
            ])
            ->willReturn($this->client);

        $this->client->method('request')
            ->with($uriEndpoint, $params, 'GET')
            ->willReturn($response);

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->assertEquals($result, $response->getStatusCode());
    }
}
