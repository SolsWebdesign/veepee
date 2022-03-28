<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api;

interface VeepeeBatchesRepositoryInterface
{
    /**
     * @param $id
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeBatchesInterface
     */
    public function getById($id);

    /**
     * @param $batchId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeBatchesInterface
     */
    public function getByBatchId($batchId);

    /**
     * @param $operationId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeBatchesInterface[]
     */
    public function getByOperationId($operationId);

    /**
     * @param $status
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeBatchesInterface[]
     */
    public function getBatchesByStatus($status);
}
