<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Api\Data;

use SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrdersInterface;

class VeepeeDeliveryOrders extends \Magento\Framework\DataObject implements VeepeeDeliveryOrdersInterface
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

    public function getBatchId()
    {
        return $this->getData('batch_id');
    }

    public function setBatchId($batchId)
    {
        return $this->setData('batch_id', $batchId);
    }

    public function getVeepeeId()
    {
        return $this->getData('veepee_id');
    }

    public function setVeepeeId($veepeeId)
    {
        return $this->setData('veepee_id', $veepeeId);
    }

    public function getVeepeeOrderId()
    {
        return $this->getData('veepee_order_id');
    }

    public function setVeepeeOrderId($veepeeOrderId)
    {
        return $this->setData('veepee_order_id', $veepeeOrderId);
    }

    public function getMagentoOrderId()
    {
        return $this->getData('magento_order_id');
    }

    public function setMagentoOrderId($magentoOrderId)
    {
        return $this->setData('magento_order_id', $magentoOrderId);
    }

    public function getMagentoComment()
    {
        return $this->getData('magento_comment');
    }

    public function setMagentoComment(string $magentoComment)
    {
        return $this->setData('magento_comment', $magentoComment);
    }

    public function getZipCode()
    {
        return $this->getData('zip_code');
    }

    public function setZipCode(string $zipCode)
    {
        return $this->setData('zip_code', $zipCode);
    }

    public function getCity()
    {
        return $this->getData('city');
    }

    public function setCity(string $city)
    {
        return $this->setData('city', $city);
    }

    public function getCountry()
    {
        return $this->getData('country');
    }

    public function setCountry(string $country)
    {
        return $this->setData('country', $country);
    }

    public function getCarrierKey()
    {
        return $this->getData('carrier_key');
    }

    public function setCarrierKey(string $carrierKey)
    {
        return $this->setData('carrier_key', $carrierKey);
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function setStatus($status)
    {
        return $this->setData('status',$status);
    }

    public function getIsMonoRef()
    {
        return $this->getData('is_mono_ref');
    }

    public function setIsMonoRef(int $isMonoRef)
    {
        return $this->setData('is_mono_ref', $isMonoRef);
    }

    public function getCanceled()
    {
        return $this->getData('canceled');
    }

    public function setCanceled($canceled)
    {
        return $this->setData('canceled', $canceled);
    }

    public function getCreationDate()
    {
        return $this->getData('creation_date');
    }

    public function setCreationDate($creationDate)
    {
        return $this->setData('creation_date', $creationDate);
    }

    public function getLogisticCommitmentDate()
    {
        return $this->getData('logistic_commitment_date');
    }

    public function setLogisticCommitmentDate($logisticCommitmentDate)
    {
        return $this->setData('logistic_commitment_date', $logisticCommitmentDate);
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
