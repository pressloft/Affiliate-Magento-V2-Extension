<?php

namespace PressLoft\Affiliate\Test\Unit\Block;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Block\Adminhtml\LastSynchronized;
use PressLoft\Affiliate\Helper\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;

class LastSynchronizedTest extends TestCase
{
    /**
     * @var MockObject|Config
     */
    private $config;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var MockBuilder|Context
     */
    private $context;

    /**
     * @var LastSynchronized
     */
    private $object;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new LastSynchronized(
            $this->context,
            $this->config,
            $this->serializer
        );
    }

    /**
     * @return void
     */
    public function testGetLastSynchronized()
    {
        $json = "{'status':200,'time':'2022-05-02 12:00:04'}";
        $this->config->expects($this->any())
            ->method('getSynchronized')
            ->willReturn($json);
        $data = $this->serializer->expects($this->any())
            ->method('unserialize')
            ->with($json);
        $this->assertNotNull($data);
    }
}
