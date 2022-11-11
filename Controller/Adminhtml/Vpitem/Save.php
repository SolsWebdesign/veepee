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
use SolsWebdesign\VeePee\Model\Config as VeepeeConfig;

class Save extends \Magento\Backend\App\Action
{
    protected $veepeeOrdersFactory;
    protected $veepeeConfig;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        VeepeeDeliveryOrdersFactory $veepeeOrdersFactory,
        VeepeeConfig $veepeeConfig
    )
    {
        $this->veepeeOrdersFactory = $veepeeOrdersFactory;
        $this->veepeeConfig = $veepeeConfig;
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
                $comment = trim($data['magento_comment']);
                if(isset($data['canceled'])) {
                    // get original canceled
                    $canceled = $veepeeOrder->getCanceled();
                    $magentoOrderId = $veepeeOrder->getMagentoOrderId();
                    $veepeeOrderStatus = $veepeeOrder->getStatus();
                    if ($canceled > $data['canceled'] || $data['canceled'] > $canceled) {
                        // okay, something has changed
                        if($data['canceled'] == 1) {
                            // we cannot cancel the order if:
                            // 1. the order has any other status then Available (0) / Canceled (9) / Unknown (10) /Stockout (8)
                            // 2. the order already has a magento order id
                            $allowedStatusses = [0,9,10,8];
                            if(($magentoOrderId == 0) && in_array($veepeeOrderStatus, $allowedStatusses)) {
                                $canceled = 1;
                                if(strlen($comment) > 0) {
                                    $comment = $comment . ' Canceled for Magento';
                                }
                            } else {
                                if($magentoOrderId > 0) {
                                    $this->messageManager->addErrorMessage('You cannot cancel this order anymore, it has already been processed by Magento.');
                                } else {
                                    $statusName = $this->veepeeConfig->getXmlOrderStatus($veepeeOrderStatus);
                                    $this->messageManager->addErrorMessage('You cannot cancel this order anymore, it has veepee status '.$statusName.'.');
                                }
                            }
                        } elseif($data['canceled'] == 0) {
                            if(($magentoOrderId == 0) && $veepeeOrderStatus == 0) {
                                $canceled = 0;
                                $comment = str_replace('Canceled for Magento', '', $comment);
                            } else {
                                if($magentoOrderId > 0) {
                                    $this->messageManager->addErrorMessage('You cannot UNcancel this order anymore, it has already been processed by Magento.');
                                } else {
                                    $statusName = $this->veepeeConfig->getXmlOrderStatus($veepeeOrderStatus);
                                    $this->messageManager->addErrorMessage('You cannot UNcancel this order anymore, it has veepee status '.$statusName.'.');
                                }
                            }
                        }
                    }
                }
                try {
                    //$veepeeOrder->setMagentoOrderId($data['magento_order_id']);
                    $veepeeOrder->setMagentoComment(trim($comment));
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
                    $veepeeOrder->setCanceled($canceled);
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
