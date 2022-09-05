<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */

namespace SolsWebdesign\VeePee\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use SolsWebdesign\VeePee\Api\VeepeeDeliveryOrderItemsRepositoryInterface;
use SolsWebdesign\VeePee\Model\VeepeeDeliveryOrdersFactory;

class ProductList extends Template
{
    protected $request;
    protected $urlBuilder;
    protected $veepeeDeliveryOrderItemsRepository;
    protected $veepeeDeliveryOrdersFactory;

    public function __construct(
        Http $request,
        UrlInterface $urlBuilder,
        VeepeeDeliveryOrderItemsRepositoryInterface $veepeeDeliveryOrderItemsRepository,
        VeepeeDeliveryOrdersFactory $veepeeDeliveryOrdersFactory,
        Context $context
    ) {
        parent::__construct($context);

        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->veepeeDeliveryOrderItemsRepository = $veepeeDeliveryOrderItemsRepository;
        $this->veepeeDeliveryOrdersFactory = $veepeeDeliveryOrdersFactory;
    }

    public function getVeepeeOrderProducts()
    {
        $id = $this->request->getParam('id');
        $productArray = [];
        if(isset($id) && $id > 0) {
            $deliveryOrder = $this->veepeeDeliveryOrdersFactory->create()->load($id);
            $deliveryOrderVeepeeOrderId = $deliveryOrder->getVeepeeOrderId();
            if(isset($deliveryOrderVeepeeOrderId) && $deliveryOrderVeepeeOrderId > 0) {
                $collection = $this->veepeeDeliveryOrderItemsRepository->getByVeepeeOrderId($deliveryOrderVeepeeOrderId);
                foreach ($collection as $doItemProduct) {
                    $doItemsId = $doItemProduct->getId();
                    if(isset($doItemsId) && $doItemsId > 0) {
                        $link = $this->getEditLink($doItemsId);
                    } else {
                        $link = '';
                    }
                    $productArray[] = array('id' => $doItemsId,
                        'supplier_reference' => $doItemProduct->getSupplierReference(),
                        'veepee_product_name' => $doItemProduct->getVeepeeProductName(),
                        'veepee_product_id' => $doItemProduct->getProductId(), // note: this is the id veepee sends with its product(s)
                        'ean_list' => $doItemProduct->getEanList(),
                        'qty' => $doItemProduct->getQty(),
                        'qty_parcelled' => $doItemProduct->getQtyParcelled(),
                        'qty_labeled' => $doItemProduct->getQtyLabeled(),
                        'qty_shipped' => $doItemProduct->getQtyShipped(),
                        'qty_stockout' => $doItemProduct->getQtyStockout(),
                        'edit_link' => $link
                    );
                }
            }
        }
        return $productArray;
    }

    public function getEditLink($doItemsId)
    {
        return $this->urlBuilder->getRouteUrl('veepee/Products/edit',['id' => $doItemsId,'key'=>$this->urlBuilder->getSecretKey('veepee','Products','edit')]);
    }
}
