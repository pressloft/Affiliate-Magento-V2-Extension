<?php

namespace PressLoft\Affiliate\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use PressLoft\Affiliate\Helper\Config as Helper;
use Magento\Config\Model\ResourceModel\Config;

class UsageCheck
{
    /**
     * API request URL
     */
    const API_REQUEST_URI = 'https://affiliates.pressloft.com/';

    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = 'heartbeat?id=';

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Helper $helper
     * @param Config $resourceConfig
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Helper $helper,
        Config $resourceConfig
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->helper = $helper;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $response = $this->doRequest(static::API_REQUEST_ENDPOINT . $this->helper->getAffiliateId());
        if (!empty($response)) {
            $data = json_encode([
                'status' => $response->getStatusCode(),
                'time' => date('Y-m-d H:i:s')
            ]);
            if ($data) {
                $this->resourceConfig->saveConfig(
                    Helper::XML_PATH_SYNCHRONIZED,
                    $data
                );
            }
        }
    }

    /**
     * Do API requested with provided params
     *
     * @param string $uriEndpoint
     * @param array<mixed> $params
     * @param string $requestMethod
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
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
