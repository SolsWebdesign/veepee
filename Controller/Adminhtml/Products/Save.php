<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Controller\Adminhtml\Products;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use SolsWebdesign\VeePee\Model\VeepeeDeliveryOrderItemsFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $veepeeOrderItemsFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        VeepeeDeliveryOrderItemsFactory         $veepeeOrderItemsFactory
    )
    {
        $this->veepeeOrderItemsFactory = $veepeeOrderItemsFactory;
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
                    $veepeeOrder = $this->veepeeOrderItemsFactory->create()->load($id);
                } else {
                    $veepeeOrder = $this->veepeeOrderItemsFactory->create();
                }
                try {
                    $veepeeOrder->setVeepeeOrderId($data['veepee_order_id']);
                    $veepeeOrder->setSupplierReference($data['supplier_reference']); // sku
                    $veepeeOrder->setProductId($data['product_id']);
                    $veepeeOrder->setQty($data['qty']);
                    $veepeeOrder->setQtyParcelled($data['qty_parcelled']);
                    $veepeeOrder->setQtyLabeled($data['qty_labeled']);
                    $veepeeOrder->setQtyShipped($data['qty_shipped']);
                    $veepeeOrder->setQtyStockout($data['qty_stockout']);
                    $veepeeOrder->setWeight($data['weight']);
                    $veepeeOrder->setEanList($data['ean_list']);
                    $veepeeOrder->setVeepeeProductName($data['veepee_product_name']);
                    $veepeeOrder->save();
                    $this->messageManager->addSuccessMessage(__('Veepee order item (product) saved'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the veepee order item.'));
                }
                return $this->resultRedirectFactory->create()->setPath('veepee/orders/index');
            }
        }
    }

    protected function _isAllowed()
    {
        if ($this->_authorization->isAllowed('SolsWebdesign_VeePee::orders') || $this->_authorization->isAllowed('SolsWebdesign_VeePee::edit')) {
            return true;
        }
        return false;
    }
}
