<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SolsWebdesign\VeePee\Model\VeepeeDeliveryOrders;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders as VeepeeDeliveryOrdersResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(VeepeeDeliveryOrders::class, VeepeeDeliveryOrdersResource::class);
    }
}
