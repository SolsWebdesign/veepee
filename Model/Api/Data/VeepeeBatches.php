<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api\Data;

use SolsWebdesign\VeePee\Api\Data\VeepeeBatchesInterface;

class VeepeeBatches extends \Magento\Framework\DataObject implements VeepeeBatchesInterface
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

    public function getOperationId()
    {
        return $this->getData('operation_id');
    }

    public function setOperationId($operationId)
    {
        return $this->setData('operation_id', $operationId);
    }

    public function getBatchId()
    {
        return $this->getData('batch_id');
    }

    public function setBatchId($batchId)
    {
        return $this->setData('batch_id', $batchId);
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    public function getCreationDate()
    {
        return $this->getData('creation_date');
    }

    public function setCreationDate($creationDate)
    {
        return $this->setData('creation_date', $creationDate);
    }
}
