<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Vpitem;

use SolsWebdesign\VeePee\Helper\VeePeeOrderManager;
use SolsWebdesign\VeePee\Model\VeepeeDeliveryOrdersFactory;

class Push extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $veePeeOrderManager;
    protected $veepeeOrdersFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        VeePeeOrderManager $veePeeOrderManager,
        VeepeeDeliveryOrdersFactory $veepeeOrdersFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->veePeeOrderManager = $veePeeOrderManager;
        $this->veepeeOrdersFactory = $veepeeOrdersFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if(!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
            return false;
        } elseif ($id = $this->getRequest()->getParam('id')) {
            if (isset($id) && $id > 0) {
                $veepeeOrder = $this->veepeeOrdersFactory->create()->load($id);
                $message = $this->veePeeOrderManager->pushDeliveryOrder($veepeeOrder->getVeepeeOrderId());
                $this->messageManager->addSuccessMessage($message);
            } else {
                $this->messageManager->addErrorMessage(__('Could not retrieve entity_id'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Could not retrieve veepee_order_id'));
        }
        return $this->resultRedirectFactory->create()->setPath('veepee/orders/index');
    }

    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('SolsWebdesign_VeePee::orders')
            ->addBreadcrumb(__('Manage VeePee Orders'), __('Edit VeePee Orders'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SolsWebdesign_VeePee::orders');
    }
}
