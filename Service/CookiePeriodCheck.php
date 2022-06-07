<?php

namespace PressLoft\Affiliate\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use PressLoft\Affiliate\Helper\Config;

class CookiePeriodCheck extends ApiRequest
{
    /**
     * API request endpoint to PressLoft
     */
    const API_REQUEST_ENDPOINT = 'cookieperiod';

    const API_TOKEN_PARAM = 'token';

    /**
     * Default cookie period 30 days
     */
    const DEFAULT_COOKIE_PERIOD = 2592000;

    const COOKIE_PERIOD_PARAM = 'cookiePeriod';

    /**
     * @var Config
     */
    private $helper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * CookiePeriodCheck constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Config $helper
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Config $helper,
        SerializerInterface $serializer
    ) {
        parent::__construct($clientFactory, $responseFactory);
        $this->helper = $helper;
        $this->serializer = $serializer;
    }

    /**
     * @param string $token
     * @return int|false
     */
    public function execute(string $token)
    {
        $result = false;

        if (!$this->helper->isEnabled()) {
            return $result;
        }

        $params = [
            RequestOptions::QUERY => [
                self::API_TOKEN_PARAM => $token
            ]
        ];

        $response = $this->doRequest(
            static::API_REQUEST_ENDPOINT,
            $params,
            Request::HTTP_METHOD_GET
        );

        if (!empty($response)) {
            if ($response->getStatusCode() == self::SUCCESS_STATUS_CODE) {
                $result = $this->getCookiePeriod($response);
            } elseif ($response->getStatusCode() >= self::ERROR_STATUS_CODE) {
                $result = self::DEFAULT_COOKIE_PERIOD;
            }
        }

        return $result;
    }

    /**
     * Return cookie duration period in seconds
     * @param Response $response
     * @return int
     */
    public function getCookiePeriod(Response $response)
    {
        $result = self::DEFAULT_COOKIE_PERIOD;

        $content = $this->serializer->unserialize(
            $response->getBody()->getContents()
        );

        if (is_array($content)
            && array_key_exists(self::COOKIE_PERIOD_PARAM, $content)
        ) {
            $result = $content[self::COOKIE_PERIOD_PARAM]; // API return period in days
            $result = 3600 * 24 * $result;
        }

        return $result;
    }
}
