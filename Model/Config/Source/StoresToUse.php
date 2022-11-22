<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StoresToUse implements OptionSourceInterface
{
    protected $storeRepository;

    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    )
    {
        $this->storeRepository = $storeRepository;
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
        $optionArray = [];
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if($storeId > 0) {
                $optionArray[$store->getId()] = $store->getName();
            }
        }
        return $optionArray;
    }
}
