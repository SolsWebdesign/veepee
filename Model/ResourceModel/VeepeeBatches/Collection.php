<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\ResourceModel\VeepeeBatches;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SolsWebdesign\VeePee\Model\VeepeeBatches;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeBatches as VeepeeBatchesResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(VeepeeBatches::class, VeepeeBatchesResource::class);
    }
}
