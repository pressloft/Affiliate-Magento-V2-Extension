<?php

namespace PressLoft\Affiliate\Test\Unit\Cron;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Service\GetDataAndSendRequest;

class SendDataToPressLoftTest extends TestCase
{
    /**
     * @var MockObject|Config
     */
    private $helper;

    /**
     * @var MockObject|GetDataAndSendRequest
     */
    private $processingRequest;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processingRequest = $this->getMockBuilder(GetDataAndSendRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->helper->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $this->processingRequest->expects($this->any())
            ->method('execute');

        $this->assertNotEmpty($this->processingRequest);
    }
}
