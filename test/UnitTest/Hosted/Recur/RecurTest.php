<?php

$root = realpath(dirname(__FILE__));

require_once $root . '/../../../../src/Includes.php';

class RecurTest extends PHPUnit_Framework_TestCase
{
    public function testRequestContainsRequiredPostFields() {
        $request = $this->prepareRequest();
        $this->assertEquals(1130, $request['merchantid']);
        $this->assertNotEmpty($request['message']);
        $this->assertNotEmpty($request['mac']);
    }
    
    public function testRequestContainsSubscriptionId() {
        $request = $this->prepareRequest();
        $message = base64_decode(urldecode($request['message']));
        $this->assertTag(array(
            'tag' => 'subscriptionid',
            'parent' => array('tag' => 'recur')
        ), $message);
    }
    
    /**
     * @expectedException Svea\ValidationException
     */
    public function testFailIfSubscriptionIdMissing() {
        $this->getBaseRecur()
            ->setAmount(123.00)
            ->setClientOrderNumber('someid')
            ->prepareRequest();
    }
    
    /**
     * @expectedException Svea\ValidationException
     */
    public function testFailIfAmountMissing() {
        $this->getBaseRecur()
            ->setClientOrderNumber('someid')
            ->setSubscriptionId('1234')
            ->prepareRequest();
    }
    
    /**
     * @expectedException Svea\ValidationException
     */
    public function testFailIfClientOrderNumberIsMissing() {
        $this->getBaseRecur()
            ->setClientOrderNumber('someid')
            ->setSubscriptionId('1234')
            ->prepareRequest();
    }
    
    private function prepareRequest() {
        return $this->getBaseRecur()
            ->setAmount(123.00)
            ->setClientOrderNumber('someid')
            ->setCurrency('NOK')
            ->setSubscriptionId('1234')
            ->prepareRequest();
    }
    
    public function getBaseRecur() {
        $config = \Svea\SveaConfig::getDefaultConfig();
        return WebPay::recur($config)->setCountryCode('NO');
    }
}
