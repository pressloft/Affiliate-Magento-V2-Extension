<?php

namespace PressLoft\Affiliate\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{

    /**
     * @param array<mixed> $dataSource
     * @return array<mixed>
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'status';
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName] = ucfirst($item[$fieldName]);
            }
        }
        return $dataSource;
    }
}
