<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api\Data;

interface VeepeeOperationsInterface
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
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

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
     * Get mode
     *
     * @return string|null
     */
    public function getMode();

    /**
     * Set mode
     *
     * @param string $mode
     * @return $this
     */
    public function setMode($mode);

    /**
     * Get start_date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set start_date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * Get end_date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end_date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate);

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
