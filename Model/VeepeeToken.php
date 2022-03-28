<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model;

class VeepeeToken extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'veepee_token';

    protected $_cacheTag = 'veepee_token';

    protected $_eventPrefix = 'veepee_token';

    protected function _construct()
    {
        $this->_init('SolsWebdesign\VeePee\Model\ResourceModel\VeepeeToken');
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
