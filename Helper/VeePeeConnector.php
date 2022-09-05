<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Helper;

use SolsWebdesign\VeePee\Model\VeepeeToken;

class VeePeeConnector extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $config;
    protected $token;
    protected $veepeeTokenFactory;
    protected $veepeeOperationsRepository;
    protected $veepeeOperationsFactory;
    protected $veepeeBatchesRepository;
    protected $veepeeBatchesFactory;
    protected $veepeeDeliveryOrdersRepository;
    protected $veepeeDeliveryOrdersFactory;
    protected $veepeeDeliveryOrderItemsRepository;
    protected $veepeeDeliveryOrderItemsFactory;
    protected $productRepository;
    protected $_curl;
    protected $veepeeApiUrl;
    protected $devLog;
    protected $devLogging;
    private $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \SolsWebdesign\VeePee\Model\Config $config,
        \SolsWebdesign\VeePee\Model\VeepeeTokenFactory $veepeeTokenFactory,
        \SolsWebdesign\VeePee\Api\VeepeeOperationsRepositoryInterface $veepeeOperationsRepository,
        \SolsWebdesign\VeePee\Model\VeepeeOperationsFactory $veepeeOperationsFactory,
        \SolsWebdesign\VeePee\Api\VeepeeBatchesRepositoryInterface $veepeeBatchesRepository,
        \SolsWebdesign\VeePee\Model\VeepeeBatchesFactory $veepeeBatchesFactory,
        \SolsWebdesign\VeePee\Api\VeepeeDeliveryOrdersRepositoryInterface $veepeeDeliveryOrdersRepository,
        \SolsWebdesign\VeePee\Model\VeepeeDeliveryOrdersFactory $veepeeDeliveryOrdersFactory,
        \SolsWebdesign\VeePee\Api\VeepeeDeliveryOrderItemsRepositoryInterface $veepeeDeliveryOrderItemsRepository,
        \SolsWebdesign\VeePee\Model\VeepeeDeliveryOrderItemsFactory $veepeeDeliveryOrderItemsFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\HTTP\Client\Curl $curl
    )
    {
        $this->config = $config;
        $this->veepeeTokenFactory = $veepeeTokenFactory;
        $this->veepeeOperationsRepository = $veepeeOperationsRepository;
        $this->veepeeOperationsFactory = $veepeeOperationsFactory;
        $this->veepeeBatchesRepository = $veepeeBatchesRepository;
        $this->veepeeBatchesFactory = $veepeeBatchesFactory;
        $this->veepeeDeliveryOrdersRepository = $veepeeDeliveryOrdersRepository;
        $this->veepeeDeliveryOrdersFactory = $veepeeDeliveryOrdersFactory;
        $this->veepeeDeliveryOrderItemsRepository = $veepeeDeliveryOrderItemsRepository;
        $this->veepeeDeliveryOrderItemsFactory = $veepeeDeliveryOrderItemsFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->_curl = $curl;

        if($this->config->isLoggingEnabled()) {
            $monthNumber = date("m");
            $this->devLogging = true;
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/veepee_connector_'.$monthNumber.'.log');
            $this->devLog = new \Zend_Log();
            $this->devLog->addWriter($writer);
        } else {
            $this->devLogging = false;
        }

        if($this->config->isEnabled()) {
            $this->veepeeApiUrl = $this->config->getVeePeeApiUrl();
        }

        parent::__construct($context);
    }

    public function getTokenCli($username)
    {
        if ($this->config->isEnabled()) {
            $accessInfo = $this->config->getVeePeeApiCredentials();
            if ($this->devLogging) {
                $this->devLog->info(print_r($accessInfo, true));
            }
            if(trim($username) == trim($accessInfo['username'])) {
                $token = $this->getToken(true);
                if(strlen($token) > 10) {
                    return 'retrieved token, continue';
                } else {
                    return 'error occurred, please check logs';
                }
            } else {
                return 'username not found';
            }
        } else {
            return 'module not enabled';
        }
    }

    public function getToken($returnIt = false)
    {
        if ($this->config->isEnabled()) {
            $veepeeTokenModel = $this->veepeeTokenFactory->create()->load(1);
            $tokenStillValid = false;
            if($veepeeTokenModel->getId() > 0) {
                // still valid? or has token expired?
                $token = $veepeeTokenModel->getToken();
                $updatedAt = $veepeeTokenModel->getUpdatedAt();
                $expiresIn = $veepeeTokenModel->getExpiresIn();
                $now = strtotime("now");
                $updatedAtTime = strtotime($updatedAt);
                $lastExpires = $updatedAtTime + $expiresIn;
                if ($this->devLogging) {
                    $this->devLog->info(print_r('getAuthToken token ' . $token, true));
                    $this->devLog->info(print_r('getAuthToken updatedAt ' . $updatedAt, true));
                    $this->devLog->info(print_r('getAuthToken expiresIn ' . $expiresIn, true));
                    $this->devLog->info(print_r('getAuthToken now ' . $now, true));
                    $this->devLog->info(print_r('getAuthToken updatedAtTime ' . $updatedAtTime, true));
                    $this->devLog->info(print_r('getAuthToken lastExpires ' . $lastExpires, true));
                }
                if ($lastExpires > $now) {
                    // cool, use current token
                    $this->token = $token;
                    $tokenStillValid = true;
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('we will use the current token ' . $token, true));
                    }
                }
            }
        }
        if (!$tokenStillValid) {
            if ($this->devLogging) {
                $this->devLog->info(print_r('get a new token through API', true));
            }
            $this->token = $this->getTokenThroughApi();
        }
        if($returnIt) {
            return $this->token;
        }
    }

    public function getTokenThroughApi()
    {
        if ($this->config->isEnabled()) {
            if(isset($this->veepeeApiUrl) && strlen($this->veepeeApiUrl) > 5) {
                $loginUrl = $this->veepeeApiUrl . '/api/v3/auth/login';
                $accessInfo = $this->config->getVeePeeApiCredentials();
                if ($this->devLogging) {
                    $this->devLog->info(print_r('Veepee login URL ' . $loginUrl, true));
                    //$this->devLog->info(print_r($accessInfo, true));
                }
                $authenticationData = array(
                    'userName' => trim($accessInfo['username']),
                    'password' => trim($accessInfo['password'])
                );
                // below does not work due to password containing slashes, trailing slashes, single quotes and double quotes
                // demand a password without any slashes or quotes because this is not the way forward
                $jsonEncoded = json_encode($authenticationData);
                //$this->devLog->info(print_r($jsonEncoded, true));
                $this->_curl->addHeader("Content-Type", "application/json-patch+json"); //x-www-form-urlencoded

                $this->_curl->setOption(CURLOPT_POST, 1);
                $this->_curl->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->_curl->post($loginUrl,$jsonEncoded);
                $response = $this->_curl->getBody();
                if ($this->devLogging) {
                    $this->devLog->info(print_r('Veepee answer ', true));
                    $this->devLog->info(print_r($response, true));
                }
                // looks like {"access_token":"eyJhbGciOiJIUzI1Ni...4","expires_in":3600}
                $response = json_decode($response, true);
                if (isset($response['access_token']) && strlen($response['access_token']) && isset($response['expires_in']) && $response['expires_in'] > 0) {
                    $veepeeTokenModel = $this->veepeeTokenFactory->create()->load(1);
                    if ($veepeeTokenModel->getId() > 0) {
                        if ($this->devLogging) {
                            $length = strlen($response['access_token']);
                            if($length > 1000) {
                                $this->devLog->info(print_r('Error, will save token but it is too long (should not be longer then 1000 and length is  ' . $length.')', true));
                            } else {
                                $this->devLog->info(print_r('token length is  ' . $length, true));
                            }
                        }
                        $veepeeTokenModel->setToken($response['access_token']);
                        $veepeeTokenModel->setExpiresIn($response['expires_in']);
                    } else {
                        $veepeeTokenModel = $this->veepeeTokenFactory->create();
                        $veepeeTokenModel->setToken($response['access_token']);
                        $veepeeTokenModel->setExpiresIn($response['expires_in']);
                    }
                    try {
                        $veepeeTokenModel->save();
                    } catch (\Exception $e) {
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('Error could not save token ' . $e->getMessage(), true));
                        }
                        $this->logger->critical('ERROR ' . $e->getMessage());
                    }
                    return $response['access_token'];
                } else {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Error (getTokenThroughApi) with connection to API:', true));
                        $this->devLog->info(print_r($response, true));
                    }
                    $this->logger->critical('ERROR veepee (getTokenThroughApi) with connection to API:');
                    $this->logger->critical($response);
                    return ''; // empty token
                }
            }
            // something went wrong
            if ($this->devLogging) {
                $this->devLog->info(print_r('Error (getTokenThroughApi) with connection to API.', true));
            }
            return ''; // empty token
        }
    }

    public function getOperations()
    {
        if ($this->config->isEnabled()) {
            $operationsReceived = 0;
            //curl -X GET "https://dropshipment-sandbox.supply.veepee.tech/api/v2/operations" -H  "accept: text/plain" -H  "Authorization: Bearer eyJhbGciO
            $this->_curl->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER , false);
            $this->getToken(false);
            $tokenBearer =  "Bearer " . $this->token;
            $operationsUrl = $this->veepeeApiUrl . '/api/v3/operations';
            $this->_curl->addHeader("Authorization", $tokenBearer);
            $this->_curl->get($operationsUrl);
            $response = $this->_curl->getBody();
            if ($this->devLogging) {
                $this->devLog->info(print_r('Veepee answer (get operations) ', true));
                $this->devLog->info(print_r($response, true));
            }
            $response = json_decode($response, true);
            if(is_array($response) && count($response) > 0) {
                foreach ($response as $responseItem) {
                    if(isset($responseItem['code']) && strlen($responseItem['code']) > 0) {
                        $operationsReceived++;
                        try {
                            $operation = $this->veepeeOperationsRepository->getByCode($responseItem['code']);
                        } catch (\Exception $exception) {
                            // just catch
                        }
                        if(isset($operation) && ($operation->getId() > 0)) {
                            // update it
                            if(isset($responseItem['status']) && strlen($responseItem['status']) > 0) {
                                $operation->setStatus($responseItem['status']);
                            }
                            if(isset($responseItem['modes'])) {
                                if (is_array($responseItem['modes']) && count($responseItem['modes']) > 0) {
                                    $modes = implode(',', $responseItem['modes']);
                                    $operation->setMode($modes);
                                } elseif (is_string($responseItem['modes'])){
                                    $operation->setMode($responseItem['modes']);
                                }
                            }
                            if(isset($responseItem['beginDate']) && strlen($responseItem['beginDate']) > 0) {
                                $operation->setStartDate($responseItem['beginDate']);
                            }
                            if(isset($responseItem['endDate']) && strlen($responseItem['endDate']) > 0) {
                                $operation->setEndDate($responseItem['endDate']);
                            }
                            try {
                                $operation->save();
                            } catch (\Exception $exception) {
                                if ($this->devLogging) {
                                    $this->devLog->info(print_r('Error (getOperations) could not save operation '.$exception->getMessage(), true));
                                }
                                $this->logger->critical('Error (getOperations) could not save operation '.$exception->getMessage());
                            }
                        } else {
                            if (is_array($responseItem['modes']) && count($responseItem['modes']) > 0) {
                                $modes = implode(',', $responseItem['modes']);
                            } elseif (is_string($responseItem['modes'])){
                                $modes = $responseItem['modes'];
                            } else {
                                $modes = 'unknown';
                            }
                            // new operation:
                            $newOperation = $this->veepeeOperationsFactory->create();
                            $newOperation->setCode($responseItem['code'])
                                ->setStatus($responseItem['status'])
                                ->setMode($modes)
                                ->setStartDate($responseItem['beginDate'])
                                ->setEndDate($responseItem['endDate']);
                            try {
                                $newOperation->save();
                            } catch (\Exception $exception) {
                                if ($this->devLogging) {
                                    $this->devLog->info(print_r('Error (getOperations) could not save operation '.$exception->getMessage(), true));
                                }
                                $this->logger->critical('Error (getOperations) could not save operation '.$exception->getMessage());
                            }
                        }
                    }
                }
            }
            return 'received '.$operationsReceived.' operations';
        } else {
            return 'module not enabled';
        }
    }

    public function getBatches($code)
    {
        if ($this->config->isEnabled()) {
            if(strlen($code) > 0) {
                $operation = $this->veepeeOperationsRepository->getByCode($code);
                $batchesReceived = 0;
                //curl -X GET "https://dropshipment-sandbox.supply.veepee.tech/api/v2/operations" -H  "accept: text/plain" -H  "Authorization: Bearer eyJhbGciO
                $this->_curl->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
                $this->getToken($returnIt = false);
                $tokenBearer = "Bearer " . $this->token;
                $batchesUrl = $this->veepeeApiUrl . '/api/v3/operations/' . $code . '/batches';
                $this->_curl->addHeader("Authorization", $tokenBearer);
                $this->_curl->get($batchesUrl);
                $response = $this->_curl->getBody();
                if ($this->devLogging) {
                    $this->devLog->info(print_r('Veepee answer (get batches) ', true));
                    $this->devLog->info(print_r($response, true));
                }
                $response = json_decode($response, true);
                if (is_array($response) && count($response) > 0) {
                    foreach ($response as $responseItem) {
                        $batchId = 0;
                        $batch = null;
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('Veepee (get batches) batch response Item', true));
                            $this->devLog->info(print_r($responseItem, true));
                        }
                        if (isset($responseItem['id']) && $responseItem['id'] > 0) {
                            $batchesReceived++;
                            try {
                                $batch = $this->veepeeBatchesRepository->getByBatchId($responseItem['id']);
                                $batchId = $batch->getId();
                            } catch (\Exception $exception) {
                                // just catch
                            }
                            if (isset($batch) && ($batchId > 0)) {
                                //$this->devLog->info(print_r('this is batch id '.$batch->getId(), true));
                                // update it
                                if (isset($batch['status']) && strlen($batch['status']) > 0) {
                                    $batch->setStatus($responseItem['status']);
                                }
                                if (isset($responseItem['creationDate']) && strlen($responseItem['creationDate']) > 0) {
                                    $batch->setStartDate($responseItem['creationDate']);
                                }
                                if (isset($responseItem['endDate']) && strlen($responseItem['endDate']) > 0) {
                                    $batch->setEndDate($responseItem['endDate']);
                                }
                                try {
                                    $batch->save();
                                } catch (\Exception $exception) {
                                    if ($this->devLogging) {
                                        $this->devLog->info(print_r('Error (getBatches) could not save batch ' . $exception->getMessage(), true));
                                    }
                                    $this->logger->critical('Error (getBatches) could not save batch ' . $exception->getMessage());
                                }
                            } else {
                                // new batch:
                                //$this->devLog->info(print_r('this is a NEW batch! ', true));
                                $newBatch = $this->veepeeBatchesFactory->create();
                                $newBatch->setOperationId($operation->getId())
                                    ->setBatchId($responseItem['id'])
                                    ->setStatus($responseItem['status'])
                                    ->setStartDate($responseItem['creationDate'])
                                    ->setEndDate($responseItem['endDate']);
                                try {
                                    $newBatch->save();
                                    if ($this->devLogging) {
                                        $this->devLog->info(print_r('saved this NEW batch! ', true));
                                    }
                                } catch (\Exception $exception) {
                                    if ($this->devLogging) {
                                        $this->devLog->info(print_r('Error (getBatches) could not save batch ' . $exception->getMessage(), true));
                                    }
                                    $this->logger->critical('Error (getBatches) could not save batch ' . $exception->getMessage());
                                }
                            }
                        }
                    }
                }
                return 'Received / updated ' . $batchesReceived . ' batches';
            } else {
                return 'Code cannot be empty';
            }
        } else {
            return 'module not enabled';
        }
    }

    public function getDeliveryOrdersForBatch($batchId, $deliveryOrderStatus = null)
    {
        if ($this->config->isEnabled()) {
            if (isset($batchId) && $batchId > 0) {
                $batch = $this->veepeeBatchesRepository->getByBatchId($batchId);
                try {
                    $operation = $this->veepeeOperationsRepository->getById($batch->getOperationId());
                    $code = $operation->getCode();
                } catch (\Exception $exception) {
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Error (getDeliveryOrdersForBatch) could not load operation ' . $exception->getMessage(), true));
                    }
                    $this->logger->critical('Error (getDeliveryOrdersForBatch) could not load operation' . $exception->getMessage());
                }
                if(isset($code) && strlen($code) > 0){
                    $vpDeliveryOrderStatuses = $this->config->getXmlOrderStatuses();
                    $ordersReceived = 0;
                    //curl -X GET "https://dropshipment-sandbox.supply.veepee.tech/api/v2/operations" -H  "accept: text/plain" -H  "Authorization: Bearer eyJhbGciO
                    $this->_curl->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
                    $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
                    $this->getToken($returnIt = false);
                    $tokenBearer = "Bearer " . $this->token;
                    $batchesUrl = $this->veepeeApiUrl . '/api/v3/operations/' . $code . '/batches/'.$batchId;
                    $this->_curl->addHeader("Authorization", $tokenBearer);
                    $this->_curl->get($batchesUrl);
                    $response = $this->_curl->getBody();
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('Veepee answer (getDeliveryOrdersForBatch) ', true));
                        //$this->devLog->info(print_r($response, true));
                    }
                    $response = json_decode($response, true);
                    if (is_array($response) && count($response) > 0) {
                        if (isset($response['deliveryOrders']) && is_array($response['deliveryOrders']) && count($response['deliveryOrders']) > 0) {
                            foreach ($response['deliveryOrders'] as $responseItem) {
                                $vpOrderId = 0;
                                $vpOrder = null;
                                if ($this->devLogging && $ordersReceived < 2) {
                                    // only show first 2 items
                                    $this->devLog->info(print_r('Veepee (getDeliveryOrdersForBatch) vpOrder response Item', true));
                                    $this->devLog->info(print_r($responseItem, true));
                                }
                                if (isset($responseItem['id']) && $responseItem['id'] > 0) {
                                    try {
                                        $vpOrder = $this->veepeeDeliveryOrdersRepository->getByVeepeeId($responseItem['id']);
                                        $vpOrderId = $vpOrder->getId();
                                    } catch (\Exception $exception) {
                                        // just catch
                                    }
                                    if (isset($vpOrder) && ($vpOrderId > 0)) {
                                        // update
                                        if ($this->devLogging) {
                                            $this->devLog->info(print_r('this is vp order id ' . $vpOrderId, true));
                                        }
                                        // update it
                                        $status = array_search(trim($responseItem['status']), $vpDeliveryOrderStatuses);
                                        if($status === false) {
                                            $status = 10; // unknown
                                        }
                                        $vpOrder->setStatus($status);
                                        $vpOrder->setVeepeeOrderId($responseItem['orderId']);
                                        $vpOrder->setBatchId($batchId);
                                        if (isset($responseItem['zipCode']) && strlen($responseItem['zipCode']) > 0) {
                                            $vpOrder->setZipCode($responseItem['zipCode']);
                                        }
                                        if (isset($responseItem['city']) && strlen($responseItem['city']) > 0) {
                                            $vpOrder->setCity($responseItem['city']);
                                        }
                                        if (isset($responseItem['country']) && strlen($responseItem['country']) > 0) {
                                            $vpOrder->setCountry($responseItem['country']);
                                        }
                                        if (isset($responseItem['carrierKey']) && strlen($responseItem['carrierKey']) > 0) {
                                            $vpOrder->setCarrierKey($responseItem['carrierKey']);
                                        }
                                        // creation date is on batch level
                                        if(isset($response['creationDate']) && strlen($response['creationDate']) > 0) {
                                            $vpOrder->setCreationDate($response['creationDate']);
                                        }
                                        // get logistic commitment date  from first delivery order item:
                                        if(isset($responseItem['logisticCommitmentDate']) && strlen($responseItem['logisticCommitmentDate']) > 0) {
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Veepee (getDeliveryOrdersForBatch) logistic commitment date ' . $responseItem['logisticCommitmentDate'], true));
                                            }
                                            $vpOrder->setLogisticCommitmentDate($responseItem['logisticCommitmentDate']);
                                        } else {
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Veepee (getDeliveryOrdersForBatch) logistic commitment date NOT SET', true));
                                            }
                                        }
                                        // get shipping address from delivery order item:
                                        if(isset($responseItem['shippingAddress'])) {
                                            // shipping address stuff:
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Veepee shipping address '.count($responseItem['shippingAddress']), true));
                                                if(count($responseItem['shippingAddress']) > 3) {
                                                    $this->devLog->info(print_r('Veepee shipping address with more then 3 items!', true));
                                                    $this->devLog->info(print_r($responseItem['shippingAddress'], true));
                                                }
                                            }
                                            if(isset($responseItem['shippingAddress']['country']) && strlen($responseItem['shippingAddress']['country']) > 0) {
                                                $vpOrder->setCountry($responseItem['shippingAddress']['country']);
                                            }
                                            if(isset($responseItem['shippingAddress']['city']) && strlen($responseItem['shippingAddress']['city']) > 0) {
                                                $vpOrder->setCity($responseItem['shippingAddress']['city']);
                                            }
                                            if(isset($responseItem['shippingAddress']['zipCode']) && strlen($responseItem['shippingAddress']['zipCode']) > 0) {
                                                $vpOrder->setZipCode($responseItem['shippingAddress']['zipCode']);
                                            }
                                            if(isset($responseItem['shippingAddress']['firstName']) && strlen($responseItem['shippingAddress']['firstName']) > 0) {
                                                $vpOrder->setFirstname($responseItem['shippingAddress']['firstName']);
                                            }
                                            if(isset($responseItem['shippingAddress']['lastName']) && strlen($responseItem['shippingAddress']['lastName']) > 0) {
                                                $vpOrder->setLastname($responseItem['shippingAddress']['lastName']);
                                            }
                                            if(isset($responseItem['shippingAddress']['companyName']) && strlen($responseItem['shippingAddress']['companyName']) > 0) {
                                                $vpOrder->setCompanyName($responseItem['shippingAddress']['companyName']);
                                            }
                                            if(isset($responseItem['shippingAddress']['digicode']) && strlen($responseItem['shippingAddress']['digicode']) > 0) {
                                                $vpOrder->setDigicode($responseItem['shippingAddress']['digicode']);
                                            }
                                            if(isset($responseItem['shippingAddress']['floor'])) {
                                                $vpOrder->setFloor($responseItem['shippingAddress']['floor']);
                                            }
                                            if(isset($responseItem['shippingAddress']['pickupPoint']) && strlen($responseItem['shippingAddress']['pickupPoint']) > 0) {
                                                $vpOrder->setPickupPoint($responseItem['shippingAddress']['pickupPoint']);
                                            }
                                            if(isset($responseItem['shippingAddress']['address1']) && strlen($responseItem['shippingAddress']['address1']) > 0) {
                                                $vpOrder->setAddress1($responseItem['shippingAddress']['address1']);
                                            }
                                            if(isset($responseItem['shippingAddress']['address2']) && strlen($responseItem['shippingAddress']['address2']) > 0) {
                                                $vpOrder->setAddress2($responseItem['shippingAddress']['address2']);
                                            }
                                            if(isset($responseItem['shippingAddress']['address3']) && strlen($responseItem['shippingAddress']['address3']) > 0) {
                                                $vpOrder->setAddress3($responseItem['shippingAddress']['address3']);
                                            }
                                            if(isset($responseItem['shippingAddress']['state']) && strlen($responseItem['shippingAddress']['state']) > 0) {
                                                $vpOrder->setState($responseItem['shippingAddress']['state']);
                                            }
                                            if(isset($responseItem['shippingAddress']['phone']) && strlen($responseItem['shippingAddress']['phone']) > 0) {
                                                $vpOrder->setPhone($responseItem['shippingAddress']['phone']);
                                            }
                                            if(isset($responseItem['shippingAddress']['email']) && strlen($responseItem['shippingAddress']['email']) > 0) {
                                                $vpOrder->setEmail($responseItem['shippingAddress']['email']);
                                            }
                                        } else {
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Veepee no shipping address?', true));
                                            }
                                        }
                                        try {
                                            $vpOrder->save();
                                        } catch (\Exception $exception) {
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Error (getDeliveryOrdersForBatch) could not save order ' . $exception->getMessage(), true));
                                            }
                                            $this->logger->critical('Error (getDeliveryOrdersForBatch) could not save order ' . $exception->getMessage());
                                        }
                                    } else {
                                        if ($this->devLogging) {
                                            $this->devLog->info(print_r('this is a NEW order with status ' . $responseItem['status'], true));
                                        }
                                        $status = array_search(trim($responseItem['status']), $vpDeliveryOrderStatuses);
                                        if($status === false) {
                                            $status = 10; // unknown
                                        }
                                        $newVpOrder = $this->veepeeDeliveryOrdersFactory->create();
                                        $newVpOrder->setBatchId($batchId)
                                            ->setVeepeeId($responseItem['id'])
                                            ->setVeepeeOrderId($responseItem['orderId'])
                                            ->setMagentoOrderId(0)
                                            ->setStatus($status)
                                            ->setZipCode($responseItem['zipCode'])
                                            ->setCity($responseItem['city'])
                                            ->setCountry($responseItem['country'])
                                            ->setCarrierKey($responseItem['carrierKey']);
                                        // shipping address
                                        if(is_array($responseItem['shippingAddress']) && count($responseItem['shippingAddress']) > 0) {
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Veepee shipping address '.count($responseItem['shippingAddress']), true));
                                                if(count($responseItem['shippingAddress']) > 3) {
                                                    $this->devLog->info(print_r('Veepee shipping address with more then 3 items!', true));
                                                    $this->devLog->info(print_r($responseItem['shippingAddress'], true));
                                                }
                                            }
                                            if(isset($responseItem['shippingAddress']['country']) && strlen($responseItem['shippingAddress']['country']) > 0) {
                                                $newVpOrder->setCountry($responseItem['shippingAddress']['country']);
                                            }
                                            if(isset($responseItem['shippingAddress']['city']) && strlen($responseItem['shippingAddress']['city']) > 0) {
                                                $newVpOrder->setCity($responseItem['shippingAddress']['city']);
                                            }
                                            if(isset($responseItem['shippingAddress']['zipCode']) && strlen($responseItem['shippingAddress']['zipCode']) > 0) {
                                                $newVpOrder->setZipCode($responseItem['shippingAddress']['zipCode']);
                                            }
                                            if(isset($responseItem['shippingAddress']['firstName']) && strlen($responseItem['shippingAddress']['firstName']) > 0) {
                                                $newVpOrder->setFirstname($responseItem['shippingAddress']['firstName']);
                                            }
                                            if(isset($responseItem['shippingAddress']['lastName']) && strlen($responseItem['shippingAddress']['lastName']) > 0) {
                                                $newVpOrder->setLastname($responseItem['shippingAddress']['lastName']);
                                            }
                                            if(isset($responseItem['shippingAddress']['companyName']) && strlen($responseItem['shippingAddress']['companyName']) > 0) {
                                                $newVpOrder->setCompanyName($responseItem['shippingAddress']['companyName']);
                                            }
                                            if(isset($responseItem['shippingAddress']['digicode']) && strlen($responseItem['shippingAddress']['digicode']) > 0) {
                                                $newVpOrder->setDigicode($responseItem['shippingAddress']['digicode']);
                                            }
                                            if(isset($responseItem['shippingAddress']['floor'])) {
                                                $newVpOrder->setFloor($responseItem['shippingAddress']['floor']);
                                            }
                                            if(isset($responseItem['shippingAddress']['pickupPoint']) && strlen($responseItem['shippingAddress']['pickupPoint']) > 0) {
                                                $newVpOrder->setPickupPoint($responseItem['shippingAddress']['pickupPoint']);
                                            }
                                            if(isset($responseItem['shippingAddress']['address1']) && strlen($responseItem['shippingAddress']['address1']) > 0) {
                                                $newVpOrder->setAddress1($responseItem['shippingAddress']['address1']);
                                            }
                                            if(isset($responseItem['shippingAddress']['address2']) && strlen($responseItem['shippingAddress']['address2']) > 0) {
                                                $newVpOrder->setAddress2($responseItem['shippingAddress']['address2']);
                                            }
                                            if(isset($responseItem['shippingAddress']['address3']) && strlen($responseItem['shippingAddress']['address3']) > 0) {
                                                $newVpOrder->setAddress3($responseItem['shippingAddress']['address3']);
                                            }
                                            if(isset($responseItem['shippingAddress']['state']) && strlen($responseItem['shippingAddress']['state']) > 0) {
                                                $newVpOrder->setState($responseItem['shippingAddress']['state']);
                                            }
                                            if(isset($responseItem['shippingAddress']['phone']) && strlen($responseItem['shippingAddress']['phone']) > 0) {
                                                $newVpOrder->setPhone($responseItem['shippingAddress']['phone']);
                                            }
                                            if(isset($responseItem['shippingAddress']['email']) && strlen($responseItem['shippingAddress']['email']) > 0) {
                                                $newVpOrder->setEmail($responseItem['shippingAddress']['email']);
                                            }
                                        }
                                        // creation date is on batch level
                                        if(isset($response['creationDate']) && strlen($response['creationDate']) > 0) {
                                            $newVpOrder->setCreationDate($response['creationDate']);
                                        }
                                        if(isset($responseItem['logisticCommitmentDate']) && strlen($responseItem['logisticCommitmentDate']) > 0) {
                                            $newVpOrder->setLogisticCommitmentDate($responseItem['logisticCommitmentDate']);
                                        }
                                        try {
                                            $newVpOrder->save();
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('saved this NEW order! ', true));
                                            }
                                        } catch (\Exception $exception) {
                                            if ($this->devLogging) {
                                                $this->devLog->info(print_r('Error (getDeliveryOrdersForBatch) could not save order ' . $exception->getMessage(), true));
                                            }
                                            $this->logger->critical('Error (getDeliveryOrdersForBatch) could not save order ' . $exception->getMessage());
                                        }
                                    }
                                    if(is_array($responseItem['details']) && count($responseItem['details']) > 0) {
                                        $this->processOrderDetails($responseItem['details'], $responseItem['orderId']);
                                    }
                                }
                                $ordersReceived++;
                            }
                            return 'Reponse contained '.$ordersReceived.' Delivery Orders';
                        } else {
                            return 'Response contains no Veepee Delivery Orders';
                        }
                    } else {
                        return 'Response does not contain an array. Response length is '.strlen($response);
                    }
                }
            }
        }
        return 'error';
    }

    public function processOrderDetails($detailsArray, $veepeeOrderId)
    {
        // detail looks something like:
        //[{"supplierReference":"COQT2P300200200VP",
        //"productId":1112670009,
        //"name":"COUETTE ANTI ACARIENS TEMPEREE-Blanc-COQT2P300200200VP",
        //"quantity":1,
        //"quantityParcelled":1,
        //"quantityLabeled":1,
        //"quantityShipped":0,
        //"quantityStockout":0,
        //"weight":2250.000,
        //"componentProductId":null, // probably parent product id of configurable
        //"ean13List":["3123600318195"],
        //"additionalDataMap":null}],
        //
        //"isMonoRef":true,
        //"isSingleRef":true,
        //"hasComponentProducts":false,
        //"logisticCommitmentDate":"2021-10-20T00:00:00",
        //"cancellationRequestStatus":null,
        //"shippingAddress":
        //{"country":"FR","city":"VILLERS LES NANCY","zipCode":"54600"}},

        foreach ($detailsArray as $detail) {
            if ($this->devLogging) {
                //$this->devLog->info(print_r('The details:', true));
                //$this->devLog->info(print_r($detail, true));
            }
            $vpOrderItem = null;
            $vpOrderItemId = 0;
            $product = null;
            if(isset($detail['productId']) && $detail['productId'] > 0) {
                $productId = $detail['productId'];
                // do we have item already?
                try {
                    $vpOrderItem = $this->veepeeDeliveryOrderItemsRepository->getByVeepeeOrderIdAndProductId($veepeeOrderId, $productId);
                    $vpOrderItemId = $vpOrderItem->getId();
                } catch (\Exception $exception) {
                    // just catch
                }
                if(isset($vpOrderItemId) && $vpOrderItemId > 0) {
                    $vpOrderItem->setSupplierReference($detail['supplierReference'])
                        //->setSku('..')
                        ->setVeepeeProductName($detail['name'])
                        ->setQty($detail['quantity'])
                        ->setQtyParcelled($detail['quantityParcelled'])
                        ->setQtyLabeled($detail['quantityLabeled'])
                        ->setQtyShipped($detail['quantityShipped'])
                        ->setQtyStockout($detail['quantityStockout'])
                        ->setWeight($detail['weight']);
                    if(is_array($detail['ean13List'])) {
                        // just the first item for now
                        $vpOrderItem->setEanList($detail['ean13List'][0]);
                    }
                    try {
                        $vpOrderItem->save();
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('updated this veepee order item ', true));
                        }
                    } catch (\Exception $exception) {
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('Error (getDeliveryOrdersForBatch) could not save order item ' . $exception->getMessage(), true));
                        }
                        $this->logger->critical('Error (getDeliveryOrdersForBatch) could not save order item ' . $exception->getMessage());
                    }
                } else {
                    // new vp Order Item
                    // does product exist? if so, get sku
                    try {
                        $product = $this->productRepository->getById($productId);
                    } catch (\Exception $exception) {
                        // just catch
                    }
                    if(isset($product)) {
                        $sku = $product->getSku();
                    } else {
                        $sku = 'n.a.';
                    }
                    $newVpOrderItem = $this->veepeeDeliveryOrderItemsFactory->create();
                    $newVpOrderItem->setVeepeeOrderId($veepeeOrderId)
                        ->setProductId($productId)
                        ->setSupplierReference($detail['supplierReference'])
                        ->setSku($sku)
                        ->setVeepeeProductName($detail['name'])
                        ->setQty($detail['quantity'])
                        ->setQtyParcelled($detail['quantityParcelled'])
                        ->setQtyLabeled($detail['quantityLabeled'])
                        ->setQtyShipped($detail['quantityShipped'])
                        ->setQtyStockout($detail['quantityStockout'])
                        ->setWeight($detail['weight']);
                        if(is_array($detail['ean13List'])) {
                            // just the first item for now
                            $newVpOrderItem->setEanList($detail['ean13List'][0]);
                        }
                    try {
                        $newVpOrderItem->save();
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('saved this NEW veepee order item! ', true));
                        }
                    } catch (\Exception $exception) {
                        if ($this->devLogging) {
                            $this->devLog->info(print_r('Error (getDeliveryOrdersForBatch) could not save order item ' . $exception->getMessage(), true));
                        }
                        $this->logger->critical('Error (getDeliveryOrdersForBatch) could not save order item ' . $exception->getMessage());
                    }
                }
            }
        }
    }

    public function getDetails($veepeeOrderId)
    {

    }
}
