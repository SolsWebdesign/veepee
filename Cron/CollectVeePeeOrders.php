<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Cron;

use SolsWebdesign\VeePee\Api\VeepeeOperationsRepositoryInterface;

class CollectVeePeeOrders
{
    protected $config;
    protected $veepeeOperationsRepository;
    protected $veepeeBatchesRepository;
    protected $veePeeConnector;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Api\VeepeeOperationsRepositoryInterface $veepeeOperationsRepository,
        \SolsWebdesign\VeePee\Api\VeepeeBatchesRepositoryInterface $veepeeBatchesRepository,
        \SolsWebdesign\VeePee\Helper\VeePeeConnector $veePeeConnector
    ) {
        $this->config = $config;
        $this->veepeeOperationsRepository = $veepeeOperationsRepository;
        $this->veepeeBatchesRepository = $veepeeBatchesRepository;
        $this->veePeeConnector = $veePeeConnector;
    }

    public function execute()
    {
        if($this->config->isEnabled()) {
            $statusesToCollectFor = ['InProgress', 'Available'];
            $operationsCollection = $this->veepeeOperationsRepository->getOperationsList();
            // first collect operations that are in progress or available
            foreach ($operationsCollection as $operation) {
                $status = $operation->getStatus();
                if(in_array($status, $statusesToCollectFor)) {
                    $this->veePeeConnector->getBatches($operation->getCode());
                }
            }
            // now collect orders for batches with status available
            $batchesCollection = $this->veepeeBatchesRepository->getBatchesByStatus('Available');
            foreach ($batchesCollection as $batch) {
                $this->veePeeConnector->getDeliveryOrdersForBatch($batch->getBatchId());
            }
            // now collect orders for batches with status in progress
            $batchesCollection = $this->veepeeBatchesRepository->getBatchesByStatus('InProgress');
            foreach ($batchesCollection as $batch) {
                $this->veePeeConnector->getDeliveryOrdersForBatch($batch->getBatchId());
            }
        }
    }
}
