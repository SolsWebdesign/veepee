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
// product, customer and storemanager
use Magento\Store\Model\StoreManagerInterface;
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

class VeePeeOrderManager
{
    // magento stuff
    private $checkoutSession;
    protected $quoteFactory;
    protected $quoteManagement;
    protected $orderRepository;
    protected $productModel;
    protected $productRepository;
    protected $getSalableQuantityDataBySku;
    protected $stockRegistry;
    protected $moduleManager;
    protected $storeManager;
    // protected $orderSender; // not (yet) needed
    protected $transaction;
    protected $invoiceService;
    // protected $invoiceSender; // not (yet) needed

    // veepee stuff
    protected $config;
    protected $veepeeDeliveryOrdersRepository;
    protected $veepeeDeliveryOrderItemsRepository;
    protected $deliveryOrdersCollectionFactory;
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
        StoreManagerInterface $storeManager,
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
        LoggerInterface $logger,
        // Forza specific
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->config = $config;
        $this->veepeeDeliveryOrdersRepository = $veepeeDeliveryOrdersRepository;
        $this->veepeeDeliveryOrderItemsRepository = $veepeeDeliveryOrderItemsRepository;
        $this->deliveryOrdersCollectionFactory = $deliveryOrdersCollectionFactory;
        $this->productRepository = $productRepository;
        $this->productModel = $productModel;
        //$this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
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
        if (isset($veepeeOrderId) && strlen($veepeeOrderId) > 0 && $this->config->isEnabled()) {
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
        $address  = $this->getAddress($veepeeDeliveryOrder);
        // create quote (cart)
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCurrency();
        // add veepee customer as quest
        $quote = $this->assignCustomer($quote, $veepeeDeliveryOrder);
        // set billing and shipping Address
        $quote->getBillingAddress()->addData($address);
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

        // set shipping method todo create backend setting for this
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
        // set payment method todo create backend setting for this
        $quote->setPaymentMethod('checkmo');
        $quote->setInventoryProcessed(false); // this reserves the items
        $quote->save(); // this is needed to avoid the error: Call to a member function getStoreId() on null in vendor/magento/module-quote/Model/Quote/Payment.php
        $quote->getPayment()->importData(['method' => 'checkmo']);
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
        }
        if($saved) {
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
                $message = 'Error: could not create order';
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
        // we only have zipcode, city and country so we will create email and name
        $email = $email;
        $quote->setCustomerId(0);
        $quote->setCustomerEmail($email);
        $quote->setCustomerFirstname($firstname);
        $quote->setCustomerLastname($lastname);
        $quote->setCustomerIsGuest(1);
        $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);

        return $quote;
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
