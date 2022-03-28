<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api;

use SolsWebdesign\VeePee\Api\VeepeeBatchesRepositoryInterface;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeBatches\CollectionFactory;

class VeepeeBatchesRepository implements VeepeeBatchesRepositoryInterface
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
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee batch with id "%1" does not exist', $id));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getByOperationId($operationId)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('operation_id', array('eq' => $operationId))
            ->getItems();
    }

    /**
     * { @inheritDoc }
     */
    public function getByBatchId($batchId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('batch_id', array('eq' => $batchId));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee batch with batch_id "%1" does not exist', $batchId));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getBatchesByStatus($status)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('status', array('eq' => $status))
            ->getItems();
    }
}
