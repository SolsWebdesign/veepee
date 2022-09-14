<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DeliveryMethods implements OptionSourceInterface
{
    protected $shippingAllmethods;

    public function __construct(
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
    )
    {
        $this->shippingAllmethods = $shippingAllmethods;
    }

    public function toOptionArray()
    {
        $anArray = $this->shippingAllmethods->toOptionArray();
        $shippingCodes = [];

        foreach ($anArray as $shippingProvider => $shippingOptions) {
            if(is_array($shippingOptions['value'])) {
                foreach ($shippingOptions['value'] as $option) {
                    if (isset($option['value']) && strlen($option['value']) > 0) {
                        $shippingCodes[] = ['label' => $shippingOptions['label'] . ' ' . $option['label'], 'value' => $option['value']];
                    }
                }
            }
        }
        return $shippingCodes;
    }
}

