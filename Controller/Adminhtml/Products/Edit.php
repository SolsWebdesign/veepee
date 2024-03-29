<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Products;

class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if(!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
            return false;
        } else {
            $resultPage = $this->resultPageFactory->create();
            $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('VeePee Order Product - Edit'));
            return $resultPage;
        }
    }

    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('SolsWebdesign_VeePee::orders')
            ->addBreadcrumb(__('Manage VeePee Orders'), __('Edit VeePee Order Product'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SolsWebdesign_VeePee::products_edit');
    }
}
