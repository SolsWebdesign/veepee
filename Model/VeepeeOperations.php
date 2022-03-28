<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model;

class VeepeeOperations extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'veepee_operations';

    protected $_cacheTag = 'veepee_operations';

    protected $_eventPrefix = 'veepee_operations';

    protected function _construct()
    {
        $this->_init('SolsWebdesign\VeePee\Model\ResourceModel\VeepeeOperations');
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
