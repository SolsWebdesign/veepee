<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Vpitem;

use SolsWebdesign\VeePee\Helper\VeePeeOrderManager;

class Cancel extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $veePeeOrderManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        VeePeeOrderManager $veePeeOrderManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->veePeeOrderManager = $veePeeOrderManager;
        parent::__construct($context);
    }

    public function execute()
    {
        if(!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
            return false;
        }  elseif ($id = $this->getRequest()->getParam('id')) {
            if (isset($id) && $id > 0) {
                $message = $this->veePeeOrderManager->cancelDeliveryOrder($id);
                if(strlen($message) > 5) {
                    $firstPart = substr($message, 0, 5);
                    $firstPart = strtolower($firstPart);
                    if($firstPart == 'error') {
                        $this->messageManager->addErrorMessage($message);
                    } else {
                        $this->messageManager->addSuccessMessage($message);
                    }
                } else {
                    $this->messageManager->addSuccessMessage($message);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Could not retrieve entity_id'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Could not retrieve veepee_order_id'));
        }
        return $this->resultRedirectFactory->create()->setPath('veepee/orders/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SolsWebdesign_VeePee::orders');
    }
}
