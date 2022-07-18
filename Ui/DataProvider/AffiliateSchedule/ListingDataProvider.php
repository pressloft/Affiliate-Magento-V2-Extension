<?php

namespace PressLoft\Affiliate\Ui\DataProvider\AffiliateSchedule;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class ListingDataProvider extends DataProvider
{
    /**
     * Get additional column
     *
     * @return SearchResultInterface
     */
    public function getSearchResult()
    {
        $result = parent::getSearchResult();

        if ($result->isLoaded()) { //@phpstan-ignore-line
            return $result;
        }

        $result->getSelect()->joinLeft( //@phpstan-ignore-line
            ['soa' => 'sales_order_affiliate'],
            'soa.id' . ' = main_table.affiliate_id',
            [
                'token' => 'soa.token',
                'order_id' => 'soa.order_id',
            ]
        );

        $result->getSelect()->join( //@phpstan-ignore-line
            ['so' => 'sales_order'],
            'so.entity_id' . ' = soa.order_id',
            [
                'increment_id' => 'so.increment_id',
            ]
        );

        return  $result;
    }
}
