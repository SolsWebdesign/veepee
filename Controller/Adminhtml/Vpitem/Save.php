<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Vpitem;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use SolsWebdesign\VeePee\Model\VeepeeDeliveryOrdersFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $veepeeOrdersFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        VeepeeDeliveryOrdersFactory $veepeeOrdersFactory
    )
    {
        $this->veepeeOrdersFactory = $veepeeOrdersFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage('Not authorized');
        } else {
            $data = $this->getRequest()->getPostValue();
            if ($data) {
                $id = $this->getRequest()->getParam('entity_id');
                if (isset($id) && $id > 0) {
                    $veepeeOrder = $this->veepeeOrdersFactory->create()->load($id);
                } else {
                    $veepeeOrder = $this->veepeeOrdersFactory->create();
                }
                try {
                    $veepeeOrder->setMagentoOrderId($data['magento_order_id']);
                    $veepeeOrder->setMagentoComment($data['magento_comment']);
                    $veepeeOrder->setFirstname($data['firstname']);
                    $veepeeOrder->setLastname($data['lastname']);
                    $veepeeOrder->setCompanyName($data['company_name']);
                    $veepeeOrder->setAddress1($data['address_1']);
                    $veepeeOrder->setAddress2($data['address_2']);
                    $veepeeOrder->setAddress3($data['address_3']);
                    $veepeeOrder->setZipCode($data['zip_code']);
                    $veepeeOrder->setCity($data['city']);
                    $veepeeOrder->setCountry($data['country']);
                    $veepeeOrder->setPickupPoint($data['pickup_point']);
                    $veepeeOrder->setFloor($data['floor']);
                    $veepeeOrder->setState($data['state']);
                    $veepeeOrder->setPhone($data['phone']);
                    $veepeeOrder->setEmail($data['email']);
                    $veepeeOrder->setCarrierKey($data['carrier_key']);
                    $veepeeOrder->save();
                    $this->messageManager->addSuccessMessage(__('Veepee order saved'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the veepee order.'));
                }
                return $this->resultRedirectFactory->create()->setPath('veepee/orders/index');
            }
        }
    }

    protected function _isAllowed()
    {
        if($this->_authorization->isAllowed('SolsWebdesign_VeePee::orders') || $this->_authorization->isAllowed('SolsWebdesign_VeePee::edit')){
            return true;
        }
        return false;
    }
}
