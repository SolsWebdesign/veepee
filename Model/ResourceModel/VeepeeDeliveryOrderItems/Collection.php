<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrderItems;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SolsWebdesign\VeePee\Model\VeepeeDeliveryOrderItems;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrderItems as VeepeeDeliveryOrderItemsResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(VeepeeDeliveryOrderItems::class, VeepeeDeliveryOrderItemsResource::class);
    }
}
