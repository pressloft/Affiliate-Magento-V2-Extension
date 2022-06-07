<?php

declare(strict_types=1);

namespace PressLoft\Affiliate\Test\Unit\Model;

use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\CollectionFactory;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Model\AffiliateSchedule;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule as AffiliateScheduleResource;

class AffiliateScheduleTest extends TestCase
{
    /**
     * @var Context | MockObject
     */
    private $context;
    /**
     * @var Registry | MockObject
     */
    private $registry;
    /**
     * @var AffiliateScheduleResource | MockObject
     */
    private $resource;
    /**
     * @var Collection | MockObject
     */
    private $resourceCollection;
    /**
     * @var CollectionFactory | MockObject
     */
    private $collectionFactory;
    /**
     * @var AffiliateSchedule
     */
    private $affiliateSchedule;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);

        $this->resource = $this->createPartialMock(AffiliateScheduleResource::class, ['getIdFieldName']);
        $this->resource->expects($this->any())
            ->method('getIdFieldName')
            ->willReturn('id');

        $this->resourceCollection = $this->createMock(Collection::class);
        $this->collectionFactory = $this->getMockBuilder(
            '\PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule\CollectionFactory'
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->collectionFactory->method('create')
            ->willReturn($this->resourceCollection);

        $this->affiliateSchedule = new AffiliateSchedule(
            $this->context,
            $this->registry,
            $this->collectionFactory,
            $this->resource,
            $this->resourceCollection,
            []
        );
    }

    /**
     * @covers \PressLoft\Affiliate\Model\AffiliateSchedule::getAvailableStatuses
     * @covers \PressLoft\Affiliate\Model\AffiliateSchedule::__construct
     */
    public function testGetAvailableStatuses()
    {
        $allStatusesList = [
            \PressLoft\Affiliate\Model\AffiliateSchedule::STATUS_NEW,
            \PressLoft\Affiliate\Model\AffiliateSchedule::STATUS_PENDING,
            \PressLoft\Affiliate\Model\AffiliateSchedule::STATUS_SUCCESS,
            \PressLoft\Affiliate\Model\AffiliateSchedule::STATUS_MISSED,
            \PressLoft\Affiliate\Model\AffiliateSchedule::STATUS_ERROR
        ];

        $statuses = $this->affiliateSchedule->getAvailableStatuses();

        $this->assertSame($allStatusesList, array_keys($statuses));
    }
}
