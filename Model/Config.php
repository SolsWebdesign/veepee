<?php
/**
 * Product : VeePee M2 Connection
 *
 * @copyright Copyright © 2022 VeePee M2 Connection. All rights reserved.
 * @author    Isolde van Oosterhout
 */
namespace SolsWebdesign\VeePee\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_ENABLED = 'veepee/general/enabled';
    const XML_API_URL = 'veepee/general/veepee_api_url';
    const XML_API_USERNAME = 'veepee/general/username';
    const XML_API_PASSWORD = 'veepee/general/password';
    const XML_AUTO_PROCESS_ORDERS = 'veepee/various/auto_process_orders';
    const XML_AUTO_INVOICE_ORDERS = 'veepee/various/auto_invoice_orders';
    const XML_AUTO_PROCESS_ORDERS_MAX = 'veepee/various/auto_process_orders_max';
    const XML_PAYMENT_METHOD_CODE = 'veepee/various/payment_method_code';
    const XML_DELIVERY_METHOD_CODE = 'veepee/various/delivery_method_code';
    const XML_LOGGING_ENABLED = 'veepee/developer/enable_logging';
    // custom billing address
    const XML_BILLING_ADDRESS_ENABLED = 'veepee/custom_billing_address/billing_address_enabled';
    const XML_BILLING_ADDRESS_FIRSTNAME = 'veepee/custom_billing_address/firstname';
    const XML_BILLING_ADDRESS_LASTNAME = 'veepee/custom_billing_address/lastname';
    const XML_BILLING_ADDRESS_COMPANY = 'veepee/custom_billing_address/company';
    const XML_BILLING_ADDRESS_TELEPHONE = 'veepee/custom_billing_address/telephone';
    const XML_BILLING_ADDRESS_STREET_NAME = 'veepee/custom_billing_address/street_name';
    const XML_BILLING_ADDRESS_HOUSE_NUMBER = 'veepee/custom_billing_address/house_number';
    const XML_BILLING_ADDRESS_HOUSE_NUMBER_ADDITION = 'veepee/custom_billing_address/house_number_addition';
    const XML_BILLING_ADDRESS_POSTCODE = 'veepee/custom_billing_address/postcode';
    const XML_BILLING_ADDRESS_CITY = 'veepee/custom_billing_address/city';
    const XML_BILLING_ADDRESS_COUNTRY = 'veepee/custom_billing_address/country';

    const XML_STATUSES = [
        0 => 'new',
        1 => 'processed',
        2 => 'no_stock',
        3 => 'complete',
        9 => 'canceled',
        10 => 'error'
    ];

    const XML_ORDER_STATUSES = [
        0 => 'Available',
        1 => 'Parcelled',
        2 => 'Labeled',
        3 => 'ReadyToShip',
        4 => 'Shipped',
        8 => 'Stockout',
        9 => 'Canceled',
        10 => 'Unknown'
    ];

    private $config;

    public function __construct(
        ScopeConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function isEnabled()
    {
        return $this->config->getValue(self::XML_PATH_ENABLED);
    }

    public function isLoggingEnabled()
    {
        return $this->config->getValue(self::XML_LOGGING_ENABLED);
    }

    public function isCustomBillingAddressEnabled()
    {
        return $this->config->getValue(self::XML_BILLING_ADDRESS_ENABLED);
    }

    public function getCustomBillingAddress()
    {
        return array(
            'firstname' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_FIRSTNAME)),
            'lastname' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_LASTNAME)),
            'company' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_COMPANY)),
            'telephone' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_TELEPHONE)),
            'street_name' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_STREET_NAME)),
            'house_number' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_HOUSE_NUMBER)),
            'house_number_addition' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_HOUSE_NUMBER_ADDITION)),
            'postcode' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_POSTCODE)),
            'city' => trim($this->config->getValue(self::XML_BILLING_ADDRESS_CITY)),
            'country' => $this->config->getValue(self::XML_BILLING_ADDRESS_COUNTRY)
        );
    }

    public function getXmlStatuses()
    {
        return self::XML_STATUSES;
    }

    public function getXmlStatus($id)
    {
        if(isset(self::XML_STATUSES[$id])) {
            return self::XML_STATUSES[$id];
        }
        return false;
    }

    public function getXmlOrderStatuses()
    {
        return self::XML_ORDER_STATUSES;
    }

    public function getXmlOrderStatus($id)
    {
        if(isset(self::XML_ORDER_STATUSES[$id])) {
            return self::XML_ORDER_STATUSES[$id];
        }
        return false;
    }

    public function getVeePeeApiUrl()
    {
        return trim($this->config->getValue(self::XML_API_URL));
    }

    public function getVeePeeApiCredentials()
    {
        return array(
            'username' => $this->config->getValue(self::XML_API_USERNAME),
            'password' => $this->config->getValue(self::XML_API_PASSWORD)
        );
    }

    public function getAutoProcessOrders()
    {
        return $this->config->getValue(self::XML_AUTO_PROCESS_ORDERS);
    }

    public function getAutoProcessOrdersMax()
    {
        return $this->config->getValue(self::XML_AUTO_PROCESS_ORDERS_MAX);
    }

    public function getAutoInvoiceOrders()
    {
        return $this->config->getValue(self::XML_AUTO_INVOICE_ORDERS);
    }

    public function getPaymentMethodCode()
    {
        return $this->config->getValue(self::XML_PAYMENT_METHOD_CODE);
    }

    public function getDeliveryMethodCode()
    {
        return $this->config->getValue(self::XML_DELIVERY_METHOD_CODE);
    }
}
