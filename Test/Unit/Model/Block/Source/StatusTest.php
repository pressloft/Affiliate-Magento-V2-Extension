<?php

namespace PressLoft\Affiliate\Test\Unit\Model\Block\Source;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Model\AffiliateSchedule;
use PressLoft\Affiliate\Model\Block\Source\Status;

class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    private $object;

    /**
     * @var MockObject|AffiliateSchedule
     */
    private $affiliateSchedule;

    protected function setUp(): void
    {
        $this->affiliateSchedule = $this->getMockBuilder(AffiliateSchedule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new Status(
            $this->affiliateSchedule
        );
    }

    /**
     * @return void
     */
    public function testToOptionArray(): void
    {
        $availableOptions = [
            AffiliateSchedule::STATUS_NEW => __('New'),
            AffiliateSchedule::STATUS_PENDING => __('Pending'),
            AffiliateSchedule::STATUS_SUCCESS => __('Success'),
            AffiliateSchedule::STATUS_MISSED => __('Missed'),
            AffiliateSchedule::STATUS_ERROR => __('Error')
        ];

        $this->affiliateSchedule->expects($this->any())
            ->method('getAvailableStatuses')
            ->willReturn($availableOptions);

        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        $this->assertEquals($options, $this->object->toOptionArray());
    }
}
