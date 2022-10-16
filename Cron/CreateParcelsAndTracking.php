<?php
/**
 * Product : Veepee
 *
 * @copyright Copyright © 2022 Veepee. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Cron;

use SolsWebdesign\VeePee\Model\Config;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders\CollectionFactory;
use SolsWebdesign\VeePee\Helper\VeePeeOrderManager;

class CreateParcelsAndTracking
{
    protected $config;
    protected $collectionFactory;
    protected $veePeeOrderManager;

    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        VeePeeOrderManager $veePeeOrderManager
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->veePeeOrderManager = $veePeeOrderManager;
    }

    public function execute()
    {
        if($this->config->isEnabled()) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('magento_order_id', array('gt' => 0))
                ->addFieldToFilter('parcel_id', array('null' => true))
                ->getItems();
            foreach ($collection as $veepeeOrder) {
                $this->veePeeOrderManager->createAndParcel($veepeeOrder);
            }
        }
    }
}
