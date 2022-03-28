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
use Magento\Framework\UrlInterface;
use SolsWebdesign\VeePee\Block\Adminhtml\Item\Grid\Renderer\Action\UrlBuilder;
use SolsWebdesign\VeePee\Model\Config;

class VpActions extends Column
{
    /** Url path */
    const VP_URL_PATH_EDIT = 'veepee/Vpitem/edit';
    const VP_URL_PATH_CANCEL = 'veepee/Vpitem/cancel';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;

    /** @var UrlInterface */
    protected $urlBuilder;

    protected $config;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->config = $config;
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::VP_URL_PATH_EDIT, ['id' => $item['entity_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['cancel'] = [
                        'href' => $this->urlBuilder->getUrl(self::VP_URL_PATH_CANCEL, ['id' => $item['entity_id']]),
                        'label' => __('Cancel'),
                        'confirm' => [
                            'title' => __('Cancel %1', $item['entity_id']),
                            'message' => __('Are you sure you want to cancel this %1 veepee order?', $item['entity_id'])
                        ]
                    ];
                }
            }
            return $dataSource;
        }
    }
}
