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
use SolsWebdesign\VeePee\Model\Config as VeepeeConfig;

class VpOrderStatus extends \Magento\Ui\Component\Form\Element\Input
{
    protected $authSession;
    protected $http;
    protected $veepeeDeliveryOrdersRepository;
    protected $veepeeConfig;

    public function __construct(
        Context $context,
        Session $authSession,
        VeepeeDeliveryOrdersRepositoryInterface $veepeeDeliveryOrdersRepository,
        VeepeeConfig $veepeeConfig,
        Http $http,
        array $components = [],
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->veepeeDeliveryOrdersRepository = $veepeeDeliveryOrdersRepository;
        $this->veepeeConfig = $veepeeConfig;
        $this->http = $http;

        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();

        $id = $this->http->getParam('id');
        $model = $this->veepeeDeliveryOrdersRepository->getById($id);
        if(isset($model)) {
            $statusId = $model->getStatus();
            if(isset($statusId)) {
                $statusName = $this->veepeeConfig->getXmlOrderStatus($statusId);
            }
        }
        $config = $this->getData('config');

        if(isset($config['dataScope']) && $config['dataScope'] == 'status'){
            if(isset($statusName)) {
                $config['default'] = $statusName;
            } else {
                $config['default'] = 'not available';
            }
            $this->setData('config', (array)$config);
        }
    }
}
