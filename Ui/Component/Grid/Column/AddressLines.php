<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Ui\Component\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AddressLines extends Column
{
    protected $config;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->config = $config;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $address = [];
                if(isset($item['address_1']) && strlen($item['address_1']) > 0) {
                    $address[] = $item['address_1'];
                }
                if(isset($item['address_2']) && strlen($item['address_2']) > 0) {
                    $address[] = $item['address_2'];
                }
                if(isset($item['address_3']) && strlen($item['address_3']) > 0) {
                    $address[] = $item['address_3'];
                }
                if(count($address) > 0) {
                    $item[$name] = implode('<br/>', $address);
                } else {
                    $item[$name] = 'n.a.';
                }
            }
            return $dataSource;
        }
    }
}

