<?php

namespace PressLoft\Affiliate\Cron;

use Exception;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Service\GetDataAndSendRequest;
use Psr\Log\LoggerInterface;

class SendDataToPressLoft
{
    /**
     * @var Config
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetDataAndSendRequest
     */
    private $requestService;

    /**
     * SendDataToPressLoft constructor
     *
     * @param Config $helper
     * @param GetDataAndSendRequest $requestService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $helper,
        GetDataAndSendRequest $requestService,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->requestService = $requestService;
        $this->logger = $logger;
    }

    /**
     * Fetch some data from API
     */
    public function execute(): void
    {
        if ($this->helper->isEnabled()) {
            try {
                $this->requestService->execute();
            } catch (Exception $e) {
                $this->logger->info($e->getMessage());
            }
        }
    }
}
