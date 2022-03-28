<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api;

use SolsWebdesign\VeePee\Api\VeepeeDeliveryOrdersRepositoryInterface;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders\CollectionFactory;

class VeepeeDeliveryOrdersRepository implements VeepeeDeliveryOrdersRepositoryInterface
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
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee delivery order with id "%1" does not exist', $id));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getByBatchId($batchId)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('batch_id', array('eq' => $batchId))
            ->getItems();
    }

    /**
     * { @inheritDoc }
     */
    public function getByVeepeeId($veepeeId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('veepee_id', array('eq' => $veepeeId));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee delivery order with veepee_id "%1" does not exist', $veepeeId));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getByVeepeeOrderId($veepeeOrderId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('veepee_order_id', array('eq' => $veepeeOrderId));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee delivery order with veepee_order_id "%1" does not exist', $veepeeOrderId));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getByMagentoOrderId($magentoOrderid)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('magento_order_id', array('eq' => $magentoOrderid));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee delivery order with magento_order_id "%1" does not exist', $magentoOrderid));
        }
    }

}
