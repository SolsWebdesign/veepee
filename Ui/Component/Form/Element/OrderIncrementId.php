<?php
/**
 * Product : Veepee
 *
 * @copyright Copyright © 2022 Veepee. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Ui\Component\Form\Element;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\Http;
use SolsWebdesign\VeePee\Api\VeepeeDeliveryOrdersRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderIncrementId extends \Magento\Ui\Component\Form\Element\Input
{
    protected $authSession;
    protected $http;
    protected $veepeeDeliveryOrdersRepository;
    protected $orderRepository;

    public function __construct(
        Context $context,
        Session $authSession,
        VeepeeDeliveryOrdersRepositoryInterface $veepeeDeliveryOrdersRepository,
        OrderRepositoryInterface $orderRepository,
        Http $http,
        array $components = [],
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->veepeeDeliveryOrdersRepository = $veepeeDeliveryOrdersRepository;
        $this->orderRepository = $orderRepository;
        $this->http = $http;

        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();

        $id = $this->http->getParam('id');
        $model = $this->veepeeDeliveryOrdersRepository->getById($id);
        if(isset($model)) {
            $orderId = $model->getOrderId();
            if(isset($orderId) && $orderId > 0) {
                $order = $this->orderRepository->get($orderId);
                $orderIncrementId = $order->getIncrementId();
            }
        }
        $config = $this->getData('config');

        if(isset($config['dataScope']) && $config['dataScope'] == 'order_increment_id'){
            if(isset($orderIncrementId)) {
                $config['default'] = $orderIncrementId;
            } else {
                $config['default'] = 'not available';
            }
            $this->setData('config', (array)$config);
        }
    }
}

