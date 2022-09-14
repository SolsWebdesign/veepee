<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PaymentMethods implements OptionSourceInterface
{
    protected $paymentHelperData;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelperData
    )
    {
        $this->paymentHelperData = $paymentHelperData;
    }

    public function toOptionArray()
    {
        return $this->paymentHelperData->getPaymentMethodList($sorted = true, $asLabelValue = true, $withGroups = false, $store = null);
    }
}
