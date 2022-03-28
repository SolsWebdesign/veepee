<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class VpCampaign implements OptionSourceInterface
{
    protected $config;
    protected $vpOperationsColFactory;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Model\ResourceModel\VeepeeOperations\CollectionFactory $collectionFactory
    ) {
        $this->config = $config;
        $this->vpOperationsColFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => __($label),
            ];
        }

        return $result;
    }

    public function getOptions()
    {
        $collection = $this->vpOperationsColFactory->create();
        $count = $collection->count();
        $optionArray = [];
        if($count > 0) {
            foreach ($collection as $collectionItem) {
                $optionArray[$collectionItem->getId()] = $collectionItem->getCode();
            }
            return $optionArray;
        } else {
            return [0 => 'n.a.'];
        }
    }
}
