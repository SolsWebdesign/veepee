<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeBatches\CollectionFactory;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected $batchesCollectionFactory;
    protected $devLog;
    protected $devLogging = false;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        CollectionFactory $batchesCollectionFactory,
        $mainTable = 'veepee_delivery_orders',
        $resourceModel = 'SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders'
    )
    {
        if($this->devLogging) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/veepee_delivery_orders_custom_search.log');
            $this->devLog = new \Zend_Log();
            $this->devLog->addWriter($writer);
        }
        $this->batchesCollectionFactory = $batchesCollectionFactory;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }
    /**
     * Add field to filter.
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null): SearchResult
    {
        if($this->devLogging) {
            $this->devLog->info('field ' . $field);
            $this->devLog->info('condition');
            if (isset($condition)) {
                $this->devLog->info(print_r($condition, true));
            } else {
                $this->devLog->info('none');
            }
        }
        if($field == 'code') {
            $operationId = 0;
            if(is_array($condition)) {
                if(array_key_exists('eq', $condition)) {
                    $operationId = $condition['eq'];
                }
            }
            if($operationId > 0) {
                $bathesIds = $this->getBatchIdsForOperationId($operationId);
                if($this->devLogging) {
                    $this->devLog->info('bathesIds array:');
                    $this->devLog->info(print_r($bathesIds, true));
                }
                return parent::addFieldToFilter('batch_id', array('in' => $bathesIds));
            } else {
                return parent::addFieldToFilter($field, $condition);
            }
        } else {
            return parent::addFieldToFilter($field, $condition);
        }
    }

    public function getBatchIdsForOperationId($operationId)
    {
        $batchIds = [];
        $batchesCollection = $this->batchesCollectionFactory->create()
            ->addFieldToFilter('operation_id', array('eq' => $operationId))->getItems();
        foreach ($batchesCollection as $item) {
            $batchIds[] = $item->getBatchId();
        }
        return $batchIds;
    }
}
