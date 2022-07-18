<?php

namespace PressLoft\Affiliate\Cron;

use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Service\UsageCheck as Check;

class UsageCheck
{
    /**
     * @var Config
     */
    private $helper;

    /**
     * @var Check
     */
    private $usageCheck;

    /**
     * @param Config $helper
     * @param Check $usageCheck
     */
    public function __construct(
        Config $helper,
        Check $usageCheck
    ) {
        $this->helper = $helper;
        $this->usageCheck = $usageCheck;
    }

    /**
     * Cause service
     *
     * @return void
     */
    public function execute(): void
    {
        if ($this->helper->isEnabled()) {
            $this->usageCheck->execute();
        }
    }
}
