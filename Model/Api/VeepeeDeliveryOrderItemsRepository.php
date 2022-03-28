<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api;

use SolsWebdesign\VeePee\Api\VeepeeDeliveryOrderItemsRepositoryInterface;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrderItems\CollectionFactory;

class VeepeeDeliveryOrderItemsRepository implements VeepeeDeliveryOrderItemsRepositoryInterface
{
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory

    ){
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * { @inheritDoc }
     */
    public function getById($id)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', array('eq' => $id));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee delivery order item with id "%1" does not exist', $id));
        }
    }

    /**
     * @inheritDoc
     */
    public function getByVeepeeOrderId($veepeeOrderId)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('veepee_order_id', array('eq' => $veepeeOrderId))
            ->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getByVeepeeOrderIdAndProductId($veepeeOrderId, $productId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('veepee_order_id', array('eq' => $veepeeOrderId))
            ->addFieldToFilter('product_id', array('eq' => $productId));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee delivery order item with product id "%1" and veepee order id "%2" does not exist', $productId, $veepeeOrderId));
        }
    }
}
