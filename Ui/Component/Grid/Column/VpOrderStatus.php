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

class VpOrderStatus extends Column
{
    protected $config;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        ContextInterface                 $context,
        UiComponentFactory               $uiComponentFactory,
        array                            $components = [],
        array                            $data = []
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
                if (isset($item['status'])) {
                    try {
                        if ($status = $this->config->getXmlOrderStatus($item['status'])) {
                            $item[$name] = $status;
                        } else {
                            $item[$name] = __('Unknown order status: ' . $item['status']);
                        }
                    } catch (\Exception $exception) {
                        // just catch
                        $item[$name] = $exception->getMessage();
                    }
                }
            }
            return $dataSource;
        }
    }
}
