<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Helper;

// magento
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
// quote and order
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
// product, customer and storemanager
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
//use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku; // looks like Forza does not have this
use Magento\Framework\Module\Manager;
use Magento\Customer\Api\Data\GroupInterface;
// invoice
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
//use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

//use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;
// veepee
use SolsWebdesign\VeePee\Model\Config;
use SolsWebdesign\VeePee\Api\VeepeeDeliveryOrdersRepositoryInterface;
use SolsWebdesign\VeePee\Api\VeepeeDeliveryOrderItemsRepositoryInterface;
use SolsWebdesign\VeePee\Model\ResourceModel\VeepeeDeliveryOrders\CollectionFactory as DeliveryOrdersCollectionFactory;
use SolsWebdesign\VeePee\Helper\VeePeeConnector;

class VeePeeOrderManager
{
    // magento stuff
    private $checkoutSession;
    protected $quoteFactory;
    protected $quoteManagement;
    protected $orderRepository;
    protected $shipmentRepository;
    protected $productModel;
    protected $productRepository;
    protected $getSalableQuantityDataBySku;
    protected $stockRegistry;
    protected $moduleManager;
    protected $storeManager;
    protected $customerRepository;
    protected $addressFactory;
    // protected $orderSender; // not (yet) needed
    protected $transaction;
    protected $invoiceService;
    // protected $invoiceSender; // not (yet) needed

    // veepee stuff
    protected $config;
    protected $veepeeDeliveryOrdersRepository;
    protected $veepeeDeliveryOrderItemsRepository;
    protected $deliveryOrdersCollectionFactory;
    protected $veePeeConnector;
    protected $paymentMethodVeepee;
    protected $deliveryMethodVeepee;
    protected $dryRun = false; // use for testing cron without placing actual orders
    protected $msiEnabled = false;
    protected $devLog;
    protected $devLogging;
    private $logger;

    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface  $shipmentRepository,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        AddressFactory $addressFactory,
        Product $productModel,
        ProductRepositoryInterface $productRepository,
        //GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        Manager $moduleManager,
        // OrderSender $orderSender,
        Transaction $transaction,
        InvoiceService $invoiceService,
        // InvoiceSender $invoiceSender,
        Config $config,
        VeepeeDeliveryOrdersRepositoryInterface $veepeeDeliveryOrdersRepository,
        VeepeeDeliveryOrderItemsRepositoryInterface $veepeeDeliveryOrderItemsRepository,
        DeliveryOrdersCollectionFactory $deliveryOrdersCollectionFactory,
        VeePeeConnector $veePeeConnector,
        LoggerInterface $logger,
        // Forza specific
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->config = $config;
        $this->veepeeDeliveryOrdersRepository = $veepeeDeliveryOrdersRepository;
        $this->veepeeDeliveryOrderItemsRepository = $veepeeDeliveryOrderItemsRepository;
        $this->deliveryOrdersCollectionFactory = $deliveryOrdersCollectionFactory;
        $this->veePeeConnector = $veePeeConnector;
        $this->productRepository = $productRepository;
        $this->productModel = $productModel;
        //$this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->quoteFactory = $quoteFactory;
        //$this->orderSender = $orderSender; // currently not used
        $this->transaction = $transaction;
        $this->invoiceService = $invoiceService;
        //$this->invoiceSender = $invoiceSender; // currently not used
        $this->logger = $logger;

        if ($this->config->isLoggingEnabled()) {
            $monthNumber = date("m");
            $this->devLogging = true;
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/veepee_order_manager_' . $monthNumber . '.log');
            $this->devLog = new \Zend_Log();
            $this->devLog->addWriter($writer);
        } else {
            $this->devLogging = false;
        }

        if ($this->config->isEnabled()) {
            $this->veepeeApiUrl = $this->config->getVeePeeApiUrl();
            $this->paymentMethodVeepee = $this->config->getPaymentMethodCode();
            $this->deliveryMethodVeepee = $this->config->getDeliveryMethodCode();
        }
        if($this->moduleManager->isEnabled('Magento_Inventory')) {
            $this->msiEnabled = true;
            if($this->devLogging) {
                $this->devLog->info(print_r('msi is enabled', true));
            }
        } else {
            if($this->devLogging) {
                $this->devLog->info(print_r('msi is disabled', true));
            }
        }
    }

    public function pushDeliveryOrders()
    {
        if($this->config->isEnabled() && $this->config->getAutoProcessOrders() == true) {
            $max = (int)$this->config->getAutoProcessOrdersMax();
            if ($this->devLogging) {
                $autoInvoice = $this->config->getAutoInvoiceOrders();
                if(isset($max) && $max > 0) {
                    $maxSet = 'Max orders to push is set to '.$max.'.';
                } else {
                    $maxSet = 'Max orders to push is not set.';
                }
                if($autoInvoice == true) {
                    $canAutoInvoice = 'AutoInvoice is set to true.';
                } else {
                    $canAutoInvoice = 'AutoInvoice is set to false.';
                }
                $this->devLog->info(print_r('pushDeliveryOrders (cron). '.$maxSet.' '.$canAutoInvoice, true));
            }
            $collection = $this->deliveryOrdersCollectionFactory->create();
            $collection->addFieldToFilter('status', array('eq' => 0)); // available
            $collection->addFieldToFilter('magento_order_id', array('eq' => 0)); // no magento order yet
            if(isset($max) && $max > 0) {
                $collection->setPageSize($max); //->setCurPage($offset)
            }
            $collection->getItems();
            $i = 0;
            foreach ($collection as $deliveryOrderItem) {
                $resultingMessage = $this->pushDeliveryOrder($deliveryOrderItem->getVeepeeOrderId());
                if ($this->devLogging) {
                    $this->devLog->info(print_r($resultingMessage, true));
                }
                $i++;
            }
            if ($this->devLogging) {
                $this->devLog->info(print_r('Done pushing delivery orders, '.$i.' were pushed.', true));
            }
        }
    }

    public function pushDeliveryOrder($veepeeOrderId)
    {
        if (isset($veepeeOrderId)
            && strlen($veepeeOrderId) > 0
            && $this->config->isEnabled()
            && strlen($this->paymentMethodVeepee) > 0
            && strlen($this->deliveryMethodVeepee) > 0)
        {
            if ($this->devLogging) {
                $this->devLog->info(print_r('PaymentMethod ' . $this->paymentMethodVeepee . ' deliveryMethod ' . $this->deliveryMethodVeepee, true));
            }
            try {
                $veepeeDeliveryOrder = $this->veepeeDeliveryOrdersRepository->getByVeepeeOrderId($veepeeOrderId);
            } catch (\Exception $exception) {
                // just catch
            }
            if (isset($veepeeDeliveryOrder)) {
                // see what the status is
                $statusId = $veepeeDeliveryOrder->getStatus();
                $status = $this->config->getXmlOrderStatus($statusId);
                $magentoOrderId = $veepeeDeliveryOrder->getMagentoOrderId();
                // only push orders that are available and have no magento order id!
                if ($status == 'Available' && $magentoOrderId == 0) {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Found delivery order with status ' . $status . ' for veepee_order_id ' . $veepeeOrderId, true));
                    }
                    // get products for order and find them by sku (supplier_reference)
                    $deliveryOrderItems = $this->veepeeDeliveryOrderItemsRepository->getByVeepeeOrderId($veepeeOrderId);
                    if (is_array($deliveryOrderItems) && count($deliveryOrderItems) > 0) {
                        $results = $this->walkThroughVeepeeDeliveryOrderItems($deliveryOrderItems);
                        if(!$results['can_place']) {
                            $magentoComment = 'Cannot place Order:' .$results['magento_comment'];
                            try {
                                $veepeeDeliveryOrder->setMagentoComment($magentoComment);
                                $veepeeDeliveryOrder->save();
                            } catch (\Exception $exception) {
                                if ($this->devLogging) {
                                    $this->devLog->info(print_r('Error could not save veepee delivery order ' . $exception->getMessage(), true));
                                }
                                $this->logger->critical('ERROR could not save veepee delivery order ' . $exception->getMessage());
                            }
                            return $magentoComment;
                        } else {
                            $productIdsAndQtysNeeded = $results['products_and_qtys'];
                            if ($this->dryRun) {
                                $magentoComment = 'Can place Order: product(s) found and enough stock.';
                                try {
                                    $veepeeDeliveryOrder->setMagentoComment($magentoComment);
                                    $veepeeDeliveryOrder->save();
                                } catch (\Exception $exception) {
                                    if ($this->devLogging) {
                                        $this->devLog->info(print_r('Error could not save veepee delivery order ' . $exception->getMessage(), true));
                                    }
                                    $this->logger->critical('ERROR could not save veepee delivery order ' . $exception->getMessage());
                                }
                            } else {
                                $result = $this->placeOrder($veepeeDeliveryOrder, $productIdsAndQtysNeeded);
                                try {
                                    $veepeeDeliveryOrder->setMagentoComment($result);
                                    $veepeeDeliveryOrder->save();
                                } catch (\Exception $exception) {
                                    // just catch
                                }
                                return $result;
                            }
                            return 'We can place Delivery Order with veepee_order_id ' . $veepeeOrderId;
                        }
                    } else {
                        return 'No items found for Delivery Order with veepee_order_id ' . $veepeeOrderId;
                    }
                } else {
                    return 'Delivery Order with veepee_order_id ' . $veepeeOrderId . ' already has status ' . $status;
                }
            } else {
                return 'Delivery Order with veepee_order_id ' . $veepeeOrderId . ' not found.';
            }
        }
    }

    public function walkThroughVeepeeDeliveryOrderItems($deliveryOrderItems) {
        $cannotPlaceOrder = false;
        $productIdsAndQtysNeeded = [];
        $magentoComment = '';
        foreach ($deliveryOrderItems as $item) {
            $productId = $qty = $qtyParcelled = 0; // empty them
            $sku = $item->getSupplierReference(); // sku
            $qty = $item->getQty();
            $qtyParcelled = $item->getQtyParcelled();
            if ($this->devLogging) {
                $this->devLog->info(print_r('found item with sku ' . $sku . ' and qty ' . $qty . ' and qtyParcelled ' . $qtyParcelled, true));
            }
            if($cannotPlaceOrder) {
                break; // no use continuing
            }
            if ($qty > $qtyParcelled) {
                $stillNeeded = $qty - $qtyParcelled;
                try {
                    // fastest way:
                    $productId = $this->productModel->getIdBySku($sku);
                } catch (\Exception $exception) {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Error could not load product ' . $exception->getMessage(), true));
                    }
                    $this->logger->critical('ERROR ' . $exception->getMessage());
                }
                if (isset($productId) && $productId > 0) {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('product with ' . $sku.' exists.', true));
                    }
                    // first check if it is enabled!
                    $product = $this->productRepository->getById($productId); // or productRepository->get($sku)
                    if($product->getStatus() != 1) {
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('product with ' . $sku. ' is NOT enabled.', true));
                            $this->devLog->info(print_r('status', true));
                            $this->devLog->info(print_r($product->getStatus(), true));
                        }
                        $magentoComment .= 'Product sku ' . $sku . ' exists but is NOT enabled.';
                        $cannotPlaceOrder = true;
                    } else {
                        // check if product has stock management:
                        $stockRegistryItem = $this->stockRegistry->getStockItemBySku($sku);
                        $stockManagement = $stockRegistryItem->getManagementStock();
                        if ($stockManagement) {
                            if ($this->devLogging) {
                                $this->devLog->info(print_r('Stock is being managed for sku ' . $sku, true));
                            }
                            if ($this->msiEnabled) {
                                $stockAvailable = $this->getStockUsingMsiForProduct($product);
                                if ($this->devLogging) {
                                    $this->devLog->info(print_r('Product with sku ' . $sku . ' has (msi) stock of : ', true));
                                    $this->devLog->info(print_r($stockAvailable, true));
                                }
                            } else {
                                //$stockAvailable = $this->stockState->getStockQty($productId, 1);
                                $stockAvailable = $stockRegistryItem->getQty();
                                if ($this->devLogging) {
                                    $this->devLog->info(print_r('Product with sku ' . $sku . ' has (non-msi) stock of : ', true));
                                    $this->devLog->info(print_r($stockAvailable, true));
                                }
                            }
                            if ($stockAvailable >= $stillNeeded) {
                                // so we actually can buy this product
                                if ($this->devLogging) {
                                    $this->devLog->info(print_r('Product with sku ' . $sku . ' is available.', true));
                                }
                                $productIdsAndQtysNeeded[] = array('product_id' => $productId, 'qty' => $stillNeeded);
                            } else {
                                $magentoComment .= 'Product sku ' . $sku . ' has ' . $stockAvailable . ' and still needed is ' . $stillNeeded . ', so it is not enough. ';
                                $cannotPlaceOrder = true;
                            }
                        } else {
                            // product can always be sold
                            $productIdsAndQtysNeeded[] = array('product_id' => $productId, 'qty' => $stillNeeded);
                        }
                    }
                } else {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Product with sku ' . $sku . ' was NOT found!', true));
                    }
                    $magentoComment .= ' Product sku ' . $sku . ' not found. ';
                    $cannotPlaceOrder = true;
                }
            }
        }
        if($cannotPlaceOrder) {
            return ['can_place' => false, 'magento_comment' => $magentoComment];
        } else {
            return ['can_place' => true, 'products_and_qtys' => $productIdsAndQtysNeeded];
        }
    }

    public function getStockUsingMsiForProduct($sku)
    {
        $quantityAvailable = 0;
        // okay, use msi
        // why do we use object manager here?
        // Because shops that have MSI disabled will break
        // if the construct contains InventorySalesAdminUi .. so we need to call it here directly
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->getSalableQuantityDataBySku = $objectManager->create('Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $salableQtyArray = $this->getSalableQuantityDataBySku->execute($sku);
        // salableQty looks like:
        //[0] => Array (
        //  [stock_name] => Default Stock
        //  [qty] => 100
        //  [manage_stock] => 1
        //)
        if ($this->devLogging) {
            $this->devLog->info(print_r('Product with sku ' . $sku . ' has salableQty of:', true));
            $this->devLog->info(print_r($salableQtyArray, true));
        }
        // go through all available stock management locations:
        if (is_array($salableQtyArray) && count($salableQtyArray) > 0) {
            foreach($salableQtyArray as $salableQty) {
                $quantityAvailable += $salableQty['qty'];
            }
        }
        return $quantityAvailable;
    }

    public function placeOrder($veepeeDeliveryOrder, $productIdsAndQtysNeeded, $storeId = 1)
    {
        // storeId cannot be 0 (admin store) because then payment won't work
        // and also you get a Magento\InventoryIndexer\Model\Queue\ReservationData error
        // so it must be 1 or higher (one of the stores, usually 1 will be the main store)
        $address = $this->getAddress($veepeeDeliveryOrder);
        // create quote (cart)
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCurrency();
        // add veepee customer as quest
        $quote = $this->assignCustomer($quote, $veepeeDeliveryOrder);
        // set billing and shipping Address
        if($this->config->isUseCustomerForBillingEnabled() == true) {
            $useCustomerForBillingInfo = $this->config->getUseCustomerForBillingInfo();
            if(isset($useCustomerForBillingInfo['customer_id'])) {
                $success = false;
                try {
                    $customer = $this->customerRepository->getById($useCustomerForBillingInfo['customer_id']);
                    $success = true;
                } catch (\Exception $exception) {
                    // just catch
                }
                if($success && isset($customer)) {
                    $billingAddressId = $customer->getDefaultBilling();
                    $billingAddress = $this->addressFactory->create()->load($billingAddressId);

                    $billingAddressArray =array(
                        'firstname' => $billingAddress->getFirstname(),
                        'lastname' => $billingAddress->getLastname(),
                        'company' => $billingAddress->getCompany(),
                        'prefix' => '',
                        'suffix' => '',
                        'street' => $billingAddress->getStreet(),
                        'city' => $billingAddress->getCity(),
                        'country_id' => $billingAddress->getCountryId(),
                        'region' => $billingAddress->getRegionId(),
                        'postcode' => $billingAddress->getPostcode(),
                        'telephone' => $billingAddress->getTelephone(),
                        'fax' => '',
                        'save_in_address_book' => 0
                    );

                    $quote->getBillingAddress()->addData($billingAddressArray);
                } else {
                    $quote->getBillingAddress()->addData($address);
                }
            }
        }elseif($this->config->isCustomBillingAddressEnabled() == true) {
            $billingAddress = $this->getCustomBillingAddress();
            if ($this->devLogging) {
                $this->devLog->info(print_r('Custom billing address: ', true));
                $this->devLog->info(print_r($billingAddress, true));
            }
            $quote->getBillingAddress()->addData($billingAddress);
        } else {
            $quote->getBillingAddress()->addData($address);
        }
        $quote->getShippingAddress()->addData($address);
        $quote->setInventoryProcessed(false);
        // fill with items
        foreach($productIdsAndQtysNeeded as $productAndQty) {
            $product = null;
            try {
                $product = $this->productRepository->getById($productAndQty['product_id']);
            } catch (\Exception $exception) {
                if ($this->devLogging) {
                    $this->devLog->info(print_r('Error could not load product ' . $exception->getMessage(), true));
                }
                $this->logger->critical('ERROR could not load product ' . $exception->getMessage());
            }
            if(isset($product) && $product->getId() > 0) {
                $quote->addProduct($product, intval($productAndQty['qty']));
            }
        }
        // to be able to convert the quote to order we need a session:
        $this->checkoutSession->setVeepeeEnabled(1);
        $this->checkoutSession->setVeepeeShipping(0); // can be a shipping price

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod(trim($this->deliveryMethodVeepee));

        $quote->setPaymentMethod(trim($this->paymentMethodVeepee));
        $quote->setInventoryProcessed(false); // this reserves the items
        $quote->save(); // this is needed to avoid the error: Call to a member function getStoreId() on null in vendor/magento/module-quote/Model/Quote/Payment.php
        $quote->getPayment()->importData(['method' => trim($this->paymentMethodVeepee)]);
        $totals = $quote->getTotals();

        $quote->setTotals($totals);
        $quote->collectTotals();
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        $saved = false;
        $message = '';
        try {
            $quote->save();
            $saved = true;
            if ($this->devLogging) {
                $this->devLog->info(print_r('Saved quote with quoteId ' . $quote->getId(), true));
            }
        } catch (\Exception $exception) {
            if ($this->devLogging) {
                $this->devLog->info(print_r('Error could not save quote ' . $exception->getMessage(), true));
            }
            $this->logger->critical('ERROR could not save quote ' . $exception->getMessage());
            $message = 'ERROR could not save quote ' . $exception->getMessage();
        }
         if($saved) {
             $exceptionMessage = '';
            try {
                $orderId = $this->quoteManagement->placeOrder($quote->getId());
                $order = $this->orderRepository->get($orderId);
                // don't send email
                $order->setEmailSent(0); // or if you do want to send email : $this->orderSender->send($order);
            } catch (\Exception $exception) {
                if ($this->devLogging) {
                    $this->devLog->info(print_r('Error could not create order ' . $exception->getMessage(), true));
                }
                $this->logger->critical('ERROR could not create order ' . $exception->getMessage());
                $message = 'ERROR could not create order ' . $exception->getMessage();
                $exceptionMessage = $exception->getMessage();
            }
            if(isset($order) && $order->getId() > 0) {
                if($this->config->getAutoInvoiceOrders() == true) {
                    $invoiced = $this->createInvoice($order);
                }
                $veepeeDeliveryOrder->setMagentoOrderId($order->getId());
                if(isset($invoiced) && $invoiced == true) {
                    $veepeeDeliveryOrder->setMagentoComment('Order has been submitted (and invoiced) to Magento');
                } else {
                    $veepeeDeliveryOrder->setMagentoComment('Order has been submitted to Magento');
                }
                try {
                    $veepeeDeliveryOrder->save();
                } catch (\Exception $exception) {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Error could not save veepee delivery order ' . $exception->getMessage(), true));
                    }
                    $this->logger->critical('ERROR could not save veepee delivery order ' . $exception->getMessage());
                }
                if(isset($invoiced) && $invoiced == true) {
                    $message = 'Successfully submitted order, invoiced order and retrieved order id ' . $order->getId();
                } else {
                    $message = 'Successfully submitted order and retrieved order id ' . $order->getId();
                }
            } else {
                $message = 'Error: '.$exceptionMessage;
            }
        } else {
            $message = 'Error: could not save quote';
        }
        $this->checkoutSession->unsVeepeeEnabled();
        $this->checkoutSession->unsVeepeeShipping();
        return $message;
    }

    public function assignCustomer($quote, $veepeeDeliveryOrder)
    {
        if($veepeeDeliveryOrder->getFirstname() !== null && strlen($veepeeDeliveryOrder->getFirstname()) > 0) {
            $firstname = $veepeeDeliveryOrder->getFirstname();
        } else {
            $firstname = $veepeeDeliveryOrder->getVeepeeOrderId();
        }
        if($veepeeDeliveryOrder->getLastname() !== null && strlen($veepeeDeliveryOrder->getLastname()) > 0) {
            $lastname = $veepeeDeliveryOrder->getLastname();
        } else {
            $lastname = 'Veepee';
        }
        if($veepeeDeliveryOrder->getEmail() !== null && strlen($veepeeDeliveryOrder->getEmail()) > 0) {
            $email = $veepeeDeliveryOrder->getEmail();
        } else {
            $email = $veepeeDeliveryOrder->getVeepeeOrderId().'@veepee.com';
        }

        // use customer for billing is load customer by id
        $useCustomerForBilling = $this->config->isUseCustomerForBillingEnabled();
        if($useCustomerForBilling == true) {
            $useCustomerForBillingInfo = $this->config->getUseCustomerForBillingInfo();
            if(isset($useCustomerForBillingInfo['customer_id'])) {
                $success = false;
                try {
                    $customer = $this->customerRepository->getById($useCustomerForBillingInfo['customer_id']);
                    $success = true;
                } catch (\Exception $exception) {
                    // just catch
                }
                if($success && isset($customer)) {
                    $customerGroupId = $customer->getGroupId();
                    if(isset($useCustomerForBillingInfo['firstname']) && strlen($useCustomerForBillingInfo['firstname']) > 0) {
                        $firstname = $useCustomerForBillingInfo['firstname'];
                    } else {
                        $firstname = $customer->getFirstname();
                    }
                    if(isset($useCustomerForBillingInfo['lastname']) && strlen($useCustomerForBillingInfo['lastname']) > 0) {
                        $lastname = $useCustomerForBillingInfo['lastname'];
                    } else {
                        $lastname = $customer->getLastname();
                    }
                    $email = $customer->getEmail();
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('assignCustomer useCustomerForBilling customer id '.$useCustomerForBillingInfo['customer_id'], true));
                        $this->devLog->info(print_r('assignCustomer useCustomerForBilling firstname '.$firstname, true));
                        $this->devLog->info(print_r('assignCustomer useCustomerForBilling lastname '.$lastname, true));
                        $this->devLog->info(print_r('assignCustomer useCustomerForBilling email '.$email, true));
                        $this->devLog->info(print_r('assignCustomer useCustomerForBilling customer group id '.$customerGroupId, true));
                    }
                    $quote->setCustomerId($useCustomerForBillingInfo['customer_id']); //$customer->getId());
                    $quote->setCustomerEmail($email);
                    $quote->setCustomerFirstname($firstname);
                    $quote->setCustomerLastname($lastname);
                    $quote->setCustomerIsGuest(0);
                    $quote->setCustomerGroupId($customerGroupId);
                    $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
                } else {
                    $quote->setCustomerId(0);
                    $quote->setCustomerEmail($email);
                    $quote->setCustomerFirstname($firstname);
                    $quote->setCustomerLastname($lastname);
                    $quote->setCustomerIsGuest(1);
                    $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
                    $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
                }
            }
        } else {
            $quote->setCustomerId(0);
            $quote->setCustomerEmail($email);
            $quote->setCustomerFirstname($firstname);
            $quote->setCustomerLastname($lastname);
            $quote->setCustomerIsGuest(1);
            $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
            $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        }
        return $quote;
    }

    public function createAndParcel($veepeeOrder)
    {
        if ($this->devLogging) {
            $this->devLog->info(print_r('createAndParcel for order with magento order id ' .$veepeeOrder->getMagentoOrderId(), true));
        }
        // we are only interested in the first shipment
        $firstShipment = null;
        $trackAndTraces = array();
        // getting error
        /*
        {"":["Cannot deserialize the current JSON array (e.g. [1,2,3]) into type 'DropShipment.WebApi.Model.TrackingData'
because the type requires a JSON object (e.g. {\"name\":\"value\"}) to deserialize correctly.\n
To fix this error either change the JSON to a JSON object (e.g. {\"name\":\"value\"}) or change the deserialized type to an array
or a type that implements a collection interface (e.g. ICollection, IList) like List<T> that can be deserialized from a JSON array.
JsonArrayAttribute can also be added to the type to force it to deserialize from a JSON array.\nPath '', line 1, position 1."]} */
        // so maybe they can only handle one t&t, so for now we just get the first t&t that is available
        try {
            $order = $this->orderRepository->get($veepeeOrder->getMagentoOrderId());
            $shipmentCollection = $order->getShipmentsCollection();
            foreach ($shipmentCollection as $shipment) {
                if($shipment->getId() > 0) {
                    $firstShipment = $shipment;
                    break;
                }
            }
        } catch (\Exception $exception) {
            // just catch
        }
        if(isset($firstShipment)) {
            $this->devLog->info(print_r('createAndParcel found a shipment', true));
            $tracksCollection = $firstShipment->getTracksCollection();
            foreach ($tracksCollection->getItems() as $track) {
                $trackAndTraces = array('Carrier' => $track->getTitle(), 'ParcelTracker' => $track->getTrackNumber());
                break; // see error above, so sticking to 1 t&t
            }
        }
        if(count($trackAndTraces) > 0) {
            if ($this->devLogging) {
                $this->devLog->info(print_r('Found track&traces for order with magento order id ' .$veepeeOrder->getMagentoOrderId(), true));
            }
            $shipped = [];
            // good, we also need the products and their shipped qty's
            // but we need to use veepee's own product ids!
            foreach ($order->getAllVisibleItems() as $item){
                $qtyShipped = $item->getQtyShipped();
                if($qtyShipped > 0) {
                    //$shipped[] = array('sku' => $item->getSku(), 'qty_shipped' => $item->getQtyShipped());
                    try {
                        $vpOrderItem = $this->veepeeDeliveryOrderItemsRepository->getByVeepeeOrderIdAndSku($veepeeOrder->getVeepeeOrderId(), $item->getSku());
                        $vpProductId = $vpOrderItem->getProductId(); // in this way we make sure we use veepee's product id!!!
                        $shipped[] = array('product_id' => $vpProductId, 'qty_shipped' => $item->getQtyShipped());
                    } catch (\Exception $exception) {
                        //
                    }
                }
            }
            $this->veePeeConnector->createParcelAndTracking($veepeeOrder, $shipped, $trackAndTraces);
        }
    }

    public function getCustomBillingAddress()
    {
        $customBillingAddress = $this->config->getCustomBillingAddress();
        /*
            'firstname' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_FIRSTNAME)),
            'lastname' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_LASTNAME)),
            'company' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_COMPANY)),
            'telephone' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_TELEPHONE)),
            'street_name' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_STREET_NAME)),
            'house_number' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_HOUSE_NUMBER)),
            'house_number_addition' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_HOUSE_NUMBER_ADDITION)),
            'postcode' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_POSTCODE)),
            'city' => trim($this->config->getValue(self::XML_INVOICE_ADDRESS_CITY)),
            'country' => $this->config->getValue(self::XML_INVOICE_ADDRESS_COUNTRY)
         */
        if(isset($customBillingAddress['firstname']) && strlen($customBillingAddress['firstname']) > 0) {
            $firstname = $customBillingAddress['firstname'];
        } else {
            $firstname = '';
        }
        if(isset($customBillingAddress['lastname']) && strlen($customBillingAddress['lastname']) > 0) {
            $lastname = $customBillingAddress['lastname'];
        } else {
            $lastname = 'Veepee';
        }
        if(isset($customBillingAddress['telephone']) && strlen($customBillingAddress['telephone']) > 0) {
            $phone = $customBillingAddress['telephone'];
        } else {
            $phone = '0123456789';
        }
        $street = $customBillingAddress['street_name'].' '.$customBillingAddress['house_number'].' '.$customBillingAddress['house_number_addition'];
        return array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'company' => $customBillingAddress['company'],
            'prefix' => '',
            'suffix' => '',
            'street' => $street,
            'city' => $customBillingAddress['city'],
            'country_id' => $customBillingAddress['country'],
            'region' => '',
            'postcode' => $customBillingAddress['postcode'],
            'telephone' => $phone,
            'fax' => '',
            'save_in_address_book' => 0
        );
    }

    public function getAddress($veepeeDeliveryOrder)
    {
        if($veepeeDeliveryOrder->getFirstname() !== null && strlen($veepeeDeliveryOrder->getFirstname()) > 0) {
            $firstname = $veepeeDeliveryOrder->getFirstname();
        } else {
            $firstname = $veepeeDeliveryOrder->getVeepeeOrderId();
        }
        if($veepeeDeliveryOrder->getLastname() !== null && strlen($veepeeDeliveryOrder->getLastname()) > 0) {
            $lastname = $veepeeDeliveryOrder->getLastname();
        } else {
            $lastname = 'Veepee';
        }
        if($veepeeDeliveryOrder->getState() !== null && strlen($veepeeDeliveryOrder->getState()) > 0) {
            $state = $veepeeDeliveryOrder->getState();
        } else {
            $state = '';
        }
        if($veepeeDeliveryOrder->getPhone() !== null && strlen($veepeeDeliveryOrder->getPhone()) > 0) {
            $phone = $veepeeDeliveryOrder->getPhone();
        } else {
            $phone = '0123456789';
        }
        // get the address lines
        $address = [];
        if($veepeeDeliveryOrder->getAddress1() !== null && strlen($veepeeDeliveryOrder->getAddress1()) > 0) {
            $address[] = $veepeeDeliveryOrder->getAddress1();
        }
        if($veepeeDeliveryOrder->getAddress2() !== null && strlen($veepeeDeliveryOrder->getAddress2()) > 0) {
            $address[] = $veepeeDeliveryOrder->getAddress2();
        }
        if($veepeeDeliveryOrder->getAddress3() !== null && strlen($veepeeDeliveryOrder->getAddress3()) > 0) {
            $address[] = $veepeeDeliveryOrder->getAddress3() !== null ;
        }
        if(count($address) > 0) {
            $addressLine = implode(', ', $address);
        } else {
            $addressLine = 'n.a.';
        }
        return array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'prefix' => '',
            'suffix' => '',
            'street' => $addressLine,
            'city' => $veepeeDeliveryOrder->getCity(),
            'country_id' => $veepeeDeliveryOrder->getCountry(),
            'region' => $state,
            'postcode' => $veepeeDeliveryOrder->getZipCode(),
            'telephone' => $phone,
            'fax' => '',
            'save_in_address_book' => 0
        );
    }

    public function createInvoice($order)
    {
        $success = false;
        if ($order->canInvoice()) {
            try {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                // perform transaction
                $this->transaction->addObject($invoice);
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus(Order::STATE_PROCESSING);
                $this->transaction->addObject($order)->save();
                $success = true;
            } catch (\Exception $exception) {
                if ($this->devLogging) {
                    $this->devLog->info(print_r('Error could not create invoice ' . $exception->getMessage(), true));
                }
                $this->logger->critical('ERROR could not create invoice ' . $exception->getMessage());
            }
            // here we can send the invoice
            // $this->sendInvoice($invoice, $order);
        }
        return $success;
    }

    public function sendInvoice($invoice, $order)
    {
        //try {
        //    $this->invoiceSender->send($invoice);
        //    $message = __('Notified customer about invoice #%1', $invoice->getIncrementId());
        //} catch (Throwable $exception) {
        //    $message = __('Unable to send the invoice: %1', $exception->getMessage());
        //}
    }
}
