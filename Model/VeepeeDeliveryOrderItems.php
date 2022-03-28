<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model;

class VeepeeDeliveryOrderItems extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'veepee_delivery_order_items';

    protected $_cacheTag = 'veepee_delivery_order_items';

    protected $_eventPrefix = 'veepee_delivery_order_items';

    protected function _construct()
    {
        $this->_init('SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrderItems');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
