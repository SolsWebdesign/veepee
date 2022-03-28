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

class Products extends Column
{
    protected $config;
    protected $veepeeDeliveryOrderItemsRepository;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Api\VeepeeDeliveryOrderItemsRepositoryInterface $veepeeDeliveryOrderItemsRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->config = $config;
        $this->veepeeDeliveryOrderItemsRepository =$veepeeDeliveryOrderItemsRepository;
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
                if (isset($item['veepee_order_id']) && $item['veepee_order_id'] > 0) {
                    try {
                        $orderDetails = [];
                        $veepeeDeliveryOrderDetails = $this->veepeeDeliveryOrderItemsRepository->getByVeepeeOrderId($item['veepee_order_id']);
                        foreach($veepeeDeliveryOrderDetails as $detail) {
                            $add = $detail->getQty().' x '.$detail->getSupplierReference();
                            $shipped = $detail->getQtyShipped();
                            $stockout = $detail->getQtyStockout();
                            if($shipped > 0) {
                                $add .= " <strong>(shipped:$shipped)</strong>";
                            }
                            if($stockout > 0) {
                                $add .= " <strong>(stockout:$stockout)</strong>";
                            }
                            $orderDetails[] = $add;
                        }
                        $item[$name] = implode("<br />",$orderDetails);
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
