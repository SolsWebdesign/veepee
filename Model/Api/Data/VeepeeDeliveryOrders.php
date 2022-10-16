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

    public function getFirstname()
    {
        return $this->getData('firstname');
    }


    public function setFirstname(string $firstname)
    {
        return $this->setData('firstname', $firstname);
    }

    public function getLastname()
    {
        return $this->getData('lastname');
    }

    public function setLastname(string $lastname)
    {
        return $this->setData('lastname', $lastname);
    }

    public function getCompanyName()
    {
        return $this->getData('company_name');
    }

    public function setCompanyName(string $companyName)
    {
        return $this->setData('company_name', $companyName);
    }

    public function getAddress1()
    {
        return $this->getData('address_1');
    }

    public function setAddress1(string $address1)
    {
        return $this->setData('address_1', $address1);
    }

    public function getAddress2()
    {
        return $this->getData('address_2');
    }

    public function setAddress2(string $address2)
    {
        return $this->setData('address_2', $address2);
    }

    public function getAddress3()
    {
        return $this->getData('address_3');
    }

    public function setAddress3(string $address3)
    {
        return $this->setData('address_3', $address3);
    }

    public function getPickupPoint()
    {
        return $this->getData('pickup_point');
    }

    public function setPickupPoint(string $pickupPoint)
    {
        return $this->setData('pickup_point', $pickupPoint);
    }

    public function getDigicode()
    {
        return $this->getData('digicode');
    }

    public function setDigicode(string $digicode)
    {
        return $this->setData('digicode', $digicode);
    }

    public function getFloor()
    {
        return $this->getData('floor');
    }

    public function setFloor(int $floor)
    {
        return $this->setData('floor', $floor);
    }

    public function getState()
    {
        return $this->getData('state');
    }

    public function setState(string $state)
    {
        return $this->setData('state', $state);
    }

    public function getPhone()
    {
        return $this->getData('phone');
    }

    public function setPhone(string $phone)
    {
        return $this->setData('phone', $phone);
    }

    public function getEmail()
    {
        return $this->getData('email');
    }

    public function setEmail(string $email)
    {
        return $this->setData('email', $email);
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

    public function getParcelId()
    {
        return $this->getData('parcel_id');
    }

    public function setParcelId($parcelId)
    {
        return $this->setData('parcel_id', $parcelId);
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
