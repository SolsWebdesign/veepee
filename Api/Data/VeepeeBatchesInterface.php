<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api\Data;

interface VeepeeBatchesInterface
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
     * Get operation id
     *
     * @return int|null
     */
    public function getOperationId();

    /**
     * Set operation id
     *
     * @param int $operationId
     * @return $this
     */
    public function setOperationId($operationId);

    /**
     * Get batch id
     *
     * @return int|null
     */
    public function getBatchId();

    /**
     * Set id
     *
     * @param int $batchId
     * @return $this
     */
    public function setBatchId($batchId);

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
}
