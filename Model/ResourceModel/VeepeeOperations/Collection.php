<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\ResourceModel\VeepeeOperations;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SolsWebdesign\VeePee\Model\VeepeeOperations;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeOperations as VeepeeOperationsResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(VeepeeOperations::class, VeepeeOperationsResource::class);
    }
}
