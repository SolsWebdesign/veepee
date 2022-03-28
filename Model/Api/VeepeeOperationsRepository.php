<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api;

use SolsWebdesign\VeePee\Api\VeepeeOperationsRepositoryInterface;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeOperations\CollectionFactory;

class VeepeeOperationsRepository implements VeepeeOperationsRepositoryInterface
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
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee operation with id "%1" does not exist', $id));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getByCode($code)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('code', array('eq' => $code));
        $item = $collection->getFirstItem();
        $itemId = $item->getId();
        if (isset($itemId) && $itemId > 0) {
            return $item;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('VeePee operation with code "%1" does not exist', $code));
        }
    }

    /**
     * { @inheritDoc }
     */
    public function getOperationsList()
    {
        return $this->collectionFactory->create()
            ->getItems();
    }
}
