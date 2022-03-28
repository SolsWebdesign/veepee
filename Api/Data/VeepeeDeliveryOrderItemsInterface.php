<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api\Data;

interface VeepeeDeliveryOrderItemsInterface
{
    /**
     * Get id
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setEntityId($id);

    /**
     * Get veepee order id
     *
     * @return int|null
     */
    public function getVeepeeOrderId();

    /**
     * Set veepee order id
     *
     * @param int $veepeeOrderId
     * @return $this
     */
    public function setVeepeeOrderId($veepeeOrderId);

    /**
     * Get supplier reference
     *
     * @return string|null
     */
    public function getSupplierReference();

    /**
     * Set supplier reference
     *
     * @param string $supplierReference
     * @return $this
     */
    public function setSupplierReference($supplierReference);

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get veepee product name
     *
     * @return string|null
     */
    public function getVeepeeProductName();

    /**
     * Set veepee product name
     *
     * @param string $veepeeProductName
     * @return $this
     */
    public function setVeepeeProductName($veepeeProductName);

    /**
     * Get qty
     *
     * @return integer|null
     */
    public function getQty();

    /**
     * Set qty
     *
     * @param integer $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get qty parcelled
     *
     * @return integer|null
     */
    public function getQtyParcelled();

    /**
     * Set qty parcelled
     *
     * @param integer $qtyParcelled
     * @return $this
     */
    public function setQtyParcelled($qtyParcelled);

    /**
     * Get qty labeled
     *
     * @return integer|null
     */
    public function getQtyLabeled();

    /**
     * Set qty labeled
     *
     * @param integer $qtyLabeled
     * @return $this
     */
    public function setQtyLabeled($qtyLabeled);

    /**
     * Get qty shipped
     *
     * @return integer|null
     */
    public function getQtyShipped();

    /**
     * Set qty shipped
     *
     * @param integer $qtyShipped
     * @return $this
     */
    public function setQtyShipped($qtyShipped);

    /**
     * Get qty stockout
     *
     * @return integer|null
     */
    public function getQtyStockout();

    /**
     * Set qty stockout
     *
     * @param integer $qtyStockout
     * @return $this
     */
    public function setQtyStockout($qtyStockout);

    /**
     * Get weight
     *
     * @return float|null
     */
    public function getWeight();

    /**
     * Set weight
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Get ean_list
     *
     * @return string|null
     */
    public function getEanList();

    /**
     * Set ean_list
     *
     * @param string $eanList
     * @return $this
     */
    public function setEanList($eanList);

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

}
