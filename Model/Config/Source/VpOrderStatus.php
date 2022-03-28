<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class VpOrderStatus implements OptionSourceInterface
{
    protected $config;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config
    ) {
        $this->config = $config;
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
        return $this->config->getXmlOrderStatuses();
    }
}
