<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Orders;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use SolsWebdesign\VeePee\Helper\VeePeeConnector;
use SolsWebdesign\VeePee\Model\VeepeeBatchesFactory;

class Collect extends \Magento\Backend\App\Action
{
    protected $veepeeConnector;
    protected $veepeeBatchesFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        VeePeeConnector $veepeeConnector,
        VeepeeBatchesFactory $veepeeBatchesFactory
    )
    {
        $this->veepeeConnector = $veepeeConnector;
        $this->veepeeBatchesFactory = $veepeeBatchesFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
        } elseif ($id = $this->getRequest()->getParam('id')) {
            if (isset($id) && $id > 0) {
                $batch = $this->veepeeBatchesFactory->create()->load($id);
                $batchId = $batch->getBatchId();
                if(isset($batchId) && $batchId > 0) {
                    try {
                        $resultMessage = $this->veepeeConnector->getDeliveryOrdersForBatch($batchId);
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
        return $this->resultRedirectFactory->create()->setPath('veepee/batches/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SolsWebdesign_VeePee::orders');
    }
}
