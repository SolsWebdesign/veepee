<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model;

class VeepeeBatches extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'veepee_batches';

    protected $_cacheTag = 'veepee_batches';

    protected $_eventPrefix = 'veepee_batches';

    protected function _construct()
    {
        $this->_init('SolsWebdesign\VeePee\Model\ResourceModel\VeepeeBatches');
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
