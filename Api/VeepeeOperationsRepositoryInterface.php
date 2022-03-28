<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Api;

interface VeepeeOperationsRepositoryInterface
{
    /**
     * @param $id
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeOperationsInterface
     */
    public function getById($id);

    /**
     * @param $code
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeOperationsInterface
     */
    public function getByCode($code);

    /**
     * @return SolsWebdesign\VeePee\Api\Data\VeepeeOperationsInterface[]
     */
    public function getOperationsList();
}
