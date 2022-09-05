<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Batches;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use SolsWebdesign\VeePee\Helper\VeePeeConnector;
use SolsWebdesign\VeePee\Model\VeepeeOperationsFactory;

class Collect extends \Magento\Backend\App\Action
{
    protected $veepeeConnector;
    protected $veepeeOperationsFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        VeePeeConnector $veepeeConnector,
        VeepeeOperationsFactory $veepeeOperationsFactory
    )
    {
        $this->veepeeConnector = $veepeeConnector;
        $this->veepeeOperationsFactory = $veepeeOperationsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
        } elseif ($id = $this->getRequest()->getParam('id')) {
            if (isset($id) && $id > 0) {
                $operation = $this->veepeeOperationsFactory->create()->load($id);
                $operationCode = $operation->getCode();
                if(isset($operationCode) && strlen($operationCode) > 0) {
                    try {
                        $resultMessage = $this->veepeeConnector->getBatches($operationCode);
                        $this->messageManager->addSuccessMessage(__($resultMessage));
                    } catch (LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e->getMessage());
                    }
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong'));
        }
        return $this->resultRedirectFactory->create()->setPath('veepee/operations/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SolsWebdesign_VeePee::batches');
    }
}

