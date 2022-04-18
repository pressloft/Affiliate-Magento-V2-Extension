<?php

namespace PressLoft\Affiliate\Model\Block\Source;

use Magento\Framework\Data\OptionSourceInterface;
use PressLoft\Affiliate\Model\AffiliateSchedule;

class Status implements OptionSourceInterface
{
    /**
     * @var AffiliateSchedule
     */
    protected $affiliateSchedule;

    /**
     * Constructor
     *
     * @param AffiliateSchedule $affiliateSchedule
     */
    public function __construct(
        AffiliateSchedule $affiliateSchedule
    ) {
        $this->affiliateSchedule = $affiliateSchedule;
    }

    /**
     * Get options
     *
     * @return array<int, array<string, mixed>>
     */
    public function toOptionArray(): array
    {
        $availableOptions = $this->affiliateSchedule->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
