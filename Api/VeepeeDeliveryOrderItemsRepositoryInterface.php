<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api;

interface VeepeeDeliveryOrderItemsRepositoryInterface
{
    /**
     * @param $id
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrderItemsInterface
     */
    public function getById($id);

    /**
     * @param $veepeeOrderId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrderItemsInterface[]
     */
    public function getByVeepeeOrderId($veepeeOrderId);

    /**
     * @param $veepeeOrderId
     * @param $productId
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeDeliveryOrderItemsInterface
     */
    public function getByVeepeeOrderIdAndProductId($veepeeOrderId, $productId);
}
