<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Ui\Component\Grid\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderIncrementId extends Column
{
    protected $orderRepository;

    protected $backendHelper;
    /** @var UrlInterface */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = $urlBuilder;
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
                if (isset($item['magento_order_id'])) {
                    if($item['magento_order_id'] == 0) {
                        $item[$name] = 'n.a.';
                    } else {
                        try {
                            $orderId = $item['magento_order_id'];
                            $order = $this->orderRepository->get($orderId);
                            $orderIncrementId = $order->getIncrementId();
                            $item[$name] = '<a target="_blank" href="' . $this->urlBuilder->getUrl(
                                    'sales/order/view',
                                    ['order_id' => $orderId]
                                ) . '">' . $orderIncrementId . '</a>';
                        } catch (\Exception $exception) {
                            // just catch
                            $item[$name] = $item['magento_order_id'] . ' not found ';
                        }
                    }
                }
            }
            return $dataSource;
        }
    }
}
