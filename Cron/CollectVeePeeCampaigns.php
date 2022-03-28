<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Cron;

use SolsWebdesign\VeePee\Model\Config;

class CollectVeePeeCampaigns
{
    protected $config;
    protected $veePeeConnector;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Helper\VeePeeConnector $veePeeConnector
    ) {
        $this->config = $config;
        $this->veePeeConnector = $veePeeConnector;
    }

    public function execute()
    {
        if($this->config->isEnabled()) {
            $this->veePeeConnector->getOperations();
        }
    }
}
