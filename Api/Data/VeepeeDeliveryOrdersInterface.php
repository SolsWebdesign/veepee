<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api\Data;

interface VeepeeDeliveryOrdersInterface
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
     * Get batch id
     *
     * @return int|null
     */
    public function getBatchId();

    /**
     * Set batch id
     *
     * @param int $batchId
     * @return $this
     */
    public function setBatchId($batchId);

    /**
     * Get veepee id
     *
     * @return int|null
     */
    public function getVeepeeId();

    /**
     * Set veepee id
     *
     * @param int $veepeeId
     * @return $this
     */
    public function setVeepeeId($veepeeId);

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
     * Get magento order id
     *
     * @return int|null
     */
    public function getMagentoOrderId();

    /**
     * Set magento order id
     *
     * @param int $magentoOrderId
     * @return $this
     */
    public function setMagentoOrderId($magentoOrderId);

    /**
     * Get magento comment
     *
     * @return string|null
     */
    public function getMagentoComment();

    /**
     * Set magento comment
     *
     * @param string $magentoComment
     * @return $this
     */
    public function setMagentoComment(string $magentoComment);

    /**
     * Get firstname
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname);

    /**
     * Get lastname
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname);

    /**
     * Get company_name
     *
     * @return string|null
     */
    public function getCompanyName();

    /**
     * Set companyName
     *
     * @param string $companyName
     * @return $this
     */
    public function setCompanyName(string $companyName);

	/**
     * Get address_1
     *
     * @return string|null
     */
    public function getAddress1();

	/**
     * Set address_1
     *
     * @param string $address1
     * @return $this
     */
    public function setAddress1(string $address1);

	/**
     * Get address_2
     *
     * @return string|null
     */
    public function getAddress2();

	/**
     * Set address_2
     *
     * @param string $address2
     * @return $this
     */
    public function setAddress2(string $address2);

	/**
     * Get address_3
     *
     * @return string|null
     */
    public function getAddress3();

	/**
     * Set address_3
     *
     * @param string $address3
     * @return $this
     */
    public function setAddress3(string $address3);

	/**
     * Get pickup_point
     *
     * @return string|null
     */
    public function getPickupPoint();

	/**
     * Set pickup_point
     *
     * @param string $pickupPoint
     * @return $this
     */
    public function setPickupPoint(string $pickupPoint);

	/**
     * Get digicode
     *
     * @return string|null
     */
    public function getDigicode();

    /**
     * Set digicode
     *
     * @param string $digicode
     * @return $this
     */
    public function setDigicode(string $digicode);

	/**
     * Get floor
     *
     * @return int|null
     */
    public function getFloor();

    /**
     * Set floor
     *
     * @param int $floor
     * @return $this
     */
    public function setFloor(int $floor);

	/**
     * Get state
     *
     * @return string|null
     */
    public function getState();

    /**
     * Set state
     *
     * @param string $state
     * @return $this
     */
    public function setState(string $state);

	/**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone);

	/**
     * Get email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * Get zip code
     *
     * @return string|null
     */
    public function getZipCode();

    /**
     * Set zip code
     *
     * @param string $zipCode
     * @return $this
     */
    public function setZipCode(string $zipCode);

    /**
     * Get city
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city);

    /**
     * Get country
     *
     * @return string|null
     */
    public function getCountry();

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country);

    /**
     * Get carrier key
     *
     * @return string|null
     */
    public function getCarrierKey();

    /**
     * Set carrier key
     *
     * @param string $carrierKey
     * @return $this
     */
    public function setCarrierKey(string $carrierKey);

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get is mono ref
     *
     * @return int|null
     */
    public function getIsMonoRef();

    /**
     * Set is mono ref
     *
     * @param int $isMonoRef
     * @return $this
     */
    public function setIsMonoRef(int $isMonoRef);

    /**
     * Get canceled
     *
     * @return int|null
     */
    public function getCanceled();

    /**
     * Set canceled
     *
     * @param int $canceled
     * @return $this
     */
    public function setCanceled($canceled);

    /**
     * Get creation date
     *
     * @return string|null
     */
    public function getCreationDate();

    /**
     * Set creation date
     *
     * @param string $creationDate
     * @return $this
     */
    public function setCreationDate($creationDate);

    /**
     * Get logistic commitment date
     *
     * @return string|null
     */
    public function getLogisticCommitmentDate();

    /**
     * Set logistic commitment date
     *
     * @param string $logisticCommitmentDate
     * @return $this
     */
    public function setLogisticCommitmentDate($logisticCommitmentDate);

    /**
     * Get parcel id
     *
     * @return int|null
     */
    public function getParcelId();

    /**
     * Set parcel id
     *
     * @param int $parcelId
     * @return $this
     */
    public function setParcelId($parcelId);

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
