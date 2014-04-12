<?php

$root = realpath(dirname(__FILE__));
require_once $root . '/../../../../src/Includes.php';

/**
 * @todo Create test for a valid subscripion
 */
class RecurIntegrationTest extends \PHPUnit_Framework_TestCase {
    
    public function testFailOnNonExistingSubscriptionId() {
        $recur = WebPay::recur(\Svea\SveaConfig::getDefaultConfig())
            ->setClientOrderNumber('testordernumber')
            ->setCountryCode('NO')
            ->setCurrency('NOK')
            ->setSubscriptionId('testsubscription')
            ->setAmount(123.45);
        $response = $this->doRequest($recur);
        $this->assertEquals(0, $response->accepted);
        $this->assertEquals('322 (BAD_SUBSCRIPTION_ID)', $response->resultcode);
    }
    
    /**
     * @param \Svea\Recur $recur
     * @return \Svea\HostedAdminResponse
     */
    private function doRequest($recur) {
        return $recur->doRequest()->getResponse();
    }
    
}
