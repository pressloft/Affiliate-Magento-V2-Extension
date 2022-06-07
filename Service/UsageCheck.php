<?php

namespace PressLoft\Affiliate\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use PressLoft\Affiliate\Helper\Config as Helper;
use Magento\Config\Model\ResourceModel\Config;

class UsageCheck extends ApiRequest
{
    /**
     * API request endpoint and query param
     */
    const API_REQUEST_ENDPOINT = 'heartbeat?id=';

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
        parent::__construct($clientFactory, $responseFactory);
        $this->helper = $helper;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $response = $this->doRequest(
            static::API_REQUEST_ENDPOINT . $this->helper->getAffiliateId(),
            [],
            Request::HTTP_METHOD_GET
        );
        if (!empty($response)) {
            if ($response->getStatusCode() == self::SUCCESS_STATUS_CODE) {
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
    }
}
