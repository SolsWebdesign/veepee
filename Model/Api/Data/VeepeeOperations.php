<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api\Data;

use SolsWebdesign\VeePee\Api\Data\VeepeeOperationsInterface;

class VeepeeOperations extends \Magento\Framework\DataObject implements VeepeeOperationsInterface
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

    public function getCode()
    {
        return $this->getData('code');
    }

    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    public function getMode()
    {
        return $this->getData('mode');
    }

    public function setMode($mode)
    {
        return $this->setData('mode', $mode);
    }

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function setStartDate($startDate)
    {
        return $this->setData('start_date', $startDate);
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    public function setEndDate($endDate)
    {
        return $this->setData('end_date', $endDate);
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
