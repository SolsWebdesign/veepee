<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Operations;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use SolsWebdesign\VeePee\Helper\VeePeeConnector;

class Update extends \Magento\Backend\App\Action
{
    protected $veepeeConnector;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        VeePeeConnector $veepeeConnector
    )
    {
        $this->veepeeConnector = $veepeeConnector;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
        } else {
            try {
                $resultMessage = $this->veepeeConnector->getOperations();
                $this->messageManager->addSuccessMessage(__($resultMessage));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('veepee/operations/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SolsWebdesign_VeePee::operations');
    }
}
