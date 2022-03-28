<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api;

interface VeepeeDeliveryOrdersRepositoryInterface
{
    /**
     * @param $id
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrdersInterface
     */
    public function getById($id);

    /**
     * @param $batchId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrdersInterface[]
     */
    public function getByBatchId($batchId);

    /**
     * @param $veepeeId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrdersInterface
     */
    public function getByVeepeeId($veepeeId);

    /**
     * @param $veepeeOrderId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrdersInterface
     */
    public function getByVeepeeOrderId($veepeeOrderId);

    /**
     * @param $magentoOrderid
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrdersInterface
     */
    public function getByMagentoOrderId($magentoOrderid);
}
