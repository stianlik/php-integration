<?php

namespace Svea;

require_once SVEA_REQUEST_DIR . '/Includes.php';

class Recur {

    protected $config;
    public $countryCode;
    public $currency;
    public $amount;
    public $clientOrderNumber;
    public $subscriptionId;

    /**
     * @param \ConfigurationProvider $config
     */
    function __construct($config) {
        $this->config = $config;
    }
    
    /**
     * Required
     * @param string $countryCodeAsString
     * @return \Svea\Recur
     */
    public function setCountryCode($countryCodeAsString) {
        $this->countryCode = $countryCodeAsString;
        return $this;
    }
    
    /**
     * If the subscription type is RECURRING or RECURRINGCAPTURE the currency for the recur
     * request must be the same as the currency in the initial transaction. In this case it is good practice to
     * omit the currency parameter.
     * @param string $currency
     * @return \Svea\Recur
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }
    
    /**
     * Required
     * @param float $amount
     * @return \Svea\Recur
     */
    public function setAmount($amount) {
        $this->amount = Helper::bround($amount,2) * 100;
        return $this;
    }
    
    /**
     * Required
     * @param string $customerRefNo
     * @return \Svea\Recur
     */
    public function setClientOrderNumber($customerRefNo) {
        $this->clientOrderNumber = $customerRefNo;
        return $this;
    }
    
    /**
     * Required
     * @param string $subscriptionId
     * @return \Svea\Recur
     */
    public function setSubscriptionId($subscriptionId) {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }
    
    /**
     * @return array Prepared request
     */
    public function prepareRequest() {
        $this->validate();
        $xmlBuilder = new HostedXmlBuilder();
        $requestXML = $xmlBuilder->getRecurXML($this);
        $merchantId = $this->config->getMerchantId(\ConfigurationProvider::HOSTED_TYPE, $this->countryCode);
        $secret = $this->config->getSecret(\ConfigurationProvider::HOSTED_TYPE, $this->countryCode);
        $xmlMessageBase64 = base64_encode($requestXML);
        $request = array(
            "merchantid" => urlencode($merchantId),
            "message" => urlencode($xmlMessageBase64),
            "mac" => urlencode(hash("sha512", $xmlMessageBase64 . $secret))
        );
        return $request;
    }
    
    /**
     * Do request using cURL
     * @return \SveaResponse
     */
    public function doRequest() {
        $responseXML = $this->doCurlRequest($this->prepareRequest());
        $responseObj = new \SimpleXMLElement($responseXML);
        return new \SveaResponse($responseObj, $this->countryCode, $this->config);
    }
    
    /**
     * @todo Should avoid using CURLOPT_SSL_VERIFYPEER
     * @throws Exception On failed requests
     * @param array $preparedRequest Associative array of POST values
     * @return string Response string
     */
    protected function doCurlRequest($preparedRequest) {
        $fields = array();
        foreach ($preparedRequest as $key => $value) {
            $fields[] = "$key=$value";
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getRequestUrl());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // force curl to trust https
        $responseXML = curl_exec($ch);
        curl_close($ch);
        
        if ($responseXML === false) {
            throw new Exception('CURL Request failed with errno ' . curl_errno($ch) . ', and error message ' . curl_error($ch));
        }
        
        return $responseXML;
    }

    protected function validate() {
        if (!$this->subscriptionId) {
            $this->failMissingValue('SubscriptionId');
        }
        if (!$this->clientOrderNumber) {
            $this->failMissingValue('OrderNumber');
        }
        if (!$this->amount) {
            $this->failMissingValue('Amount');
        }
    }
    
    protected function failMissingValue($key) {
        throw new ValidationException("-missing value : Client$key is required use set$key()");
    }
    
    protected function getRequestUrl() {
        return $this->config->getEndpoint(SveaConfigurationProvider::HOSTED_ADMIN_TYPE) . 'recur';
    }
}
