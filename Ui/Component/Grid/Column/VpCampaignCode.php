<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Ui\Component\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class VpCampaignCode extends Column
{
    protected $config;
    protected $batchesRepository;
    protected $vpCampaign;

    public function __construct(
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Api\VeepeeBatchesRepositoryInterface $batchesRepository,
        \SolsWebdesign\VeePee\Model\Config\Source\VpCampaign $vpCampaign,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->config = $config;
        $this->batchesRepository = $batchesRepository;
        $this->vpCampaign = $vpCampaign;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $options = $this->vpCampaign->getOptions();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['batch_id']) && $item['batch_id'] > 0) {
                    try {
                        $batch = $this->batchesRepository->getByBatchId($item['batch_id']);
                        $operationId = $batch->getOperationId();
                        if(array_key_exists($operationId, $options)) {
                            $item[$name] = $options[$operationId];
                        } else {
                            $item[$name] = __('n.a.');
                        }
                    } catch (\Exception $exception) {
                        // just catch
                        $item[$name] = $exception->getMessage();
                    }
                }
            }
            return $dataSource;
        }
    }
}

