<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Cron;

class PushVeePeeOrders
{
    protected $config;
    protected $veePeeOrderManager;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Helper\VeePeeOrderManager $veePeeOrderManager
    ) {
        $this->config = $config;
        $this->veePeeOrderManager = $veePeeOrderManager;
    }

    public function execute()
    {
        if($this->config->isEnabled() && $this->config->getAutoProcessOrders()) {
            $this->veePeeOrderManager->pushDeliveryOrders();
        }
    }
}
