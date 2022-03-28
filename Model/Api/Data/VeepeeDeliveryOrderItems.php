<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api\Data;

use SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrderItemsInterface;

class VeepeeDeliveryOrderItems extends \Magento\Framework\DataObject implements VeepeeDeliveryOrderItemsInterface
{
    public function __construct(
        array $data = []
    )
    {
        parent::__construct($data);
    }

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function setEntityId($id)
    {
        return $this->setData('entity_id', $id);
    }

    public function getVeepeeOrderId()
    {
        return $this->getData('veepee_order_id');
    }

    public function setVeepeeOrderId($veepeeOrderId)
    {
        return $this->setData('veepee_order_id', $veepeeOrderId);
    }

    public function getSupplierReference()
    {
        return $this->getData('supplier_reference');
    }

    public function setSupplierReference($supplierReference)
    {
        return $this->setData('supplier_reference', $supplierReference);
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    public function setProductId($productId)
    {
        return $this->setData('product_id', $productId);
    }

    public function getVeepeeProductName()
    {
        return $this->getData('veepee_product_name');
    }

    public function setVeepeeProductName($veepeeProductName)
    {
        return $this->setData('veepee_product_name', $veepeeProductName);
    }

    public function getQty()
    {
        return $this->getData('qty');
    }

    public function setQty($qty)
    {
        return $this->setData('qty', $qty);
    }

    public function getQtyParcelled()
    {
        return $this->getData('qty_parcelled');
    }

    public function setQtyParcelled($qtyParcelled)
    {
        return $this->setData('qty_parcelled', $qtyParcelled);
    }

    public function getQtyLabeled()
    {
        return $this->getData('qty_labeled');
    }

    public function setQtyLabeled($qtyLabeled)
    {
        return $this->setData('qty_labeled', $qtyLabeled);
    }

    public function getQtyShipped()
    {
        return $this->getData('qty_shipped');
    }

    public function setQtyShipped($qtyShipped)
    {
        return $this->setData('qty_shipped', $qtyShipped);
    }

    public function getQtyStockout()
    {
        return $this->getData('qty_stockout');
    }

    public function setQtyStockout($qtyStockout)
    {
        return $this->setData('qty_stockout', $qtyStockout);
    }

    public function getWeight()
    {
        return $this->getData('weight');
    }

    public function setWeight($weight)
    {
        return $this->setData('weight', $weight);
    }

    public function getEanList()
    {
        return $this->getData('ean_list');
    }

    public function setEanList($eanList)
    {
        return $this->setData('ean_list', $eanList);
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData('updated_at', $updatedAt);
    }
}
