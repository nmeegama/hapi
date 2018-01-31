<?php

namespace Harvest\Tests;

use Harvest\HarvestApi;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * HarvestApi test cases
 */
class HarvestApiTest extends TestCase
{
    public function testNothing()
    {
        $this->assertTrue(true);
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Harvest\HarvestApi', new HarvestApi());
    }

    public function testAPIProperties()
    {
        $api = new HarvestApi();

      $api->setAccessToken("1437420.pt.0MchAui8WDvLyNKfybvxi9D0ikJLo8PKK9ECRD1PXCjSS4Pvj0th0QoD7tKpvqsgvhOhqE2PWBn1t7EiUea5Fg");
      $api->setAccountId("255158");

        $this->assertEquals('1437420.pt.0MchAui8WDvLyNKfybvxi9D0ikJLo8PKK9ECRD1PXCjSS4Pvj0th0QoD7tKpvqsgvhOhqE2PWBn1t7EiUea5Fg', Assert::readAttribute($api, '_accessToken'));
        $this->assertEquals('255158', Assert::readAttribute($api, '_accountId'));
    }

    /**
     * @group internet
     */
    public function testClientsRetrieval()
    {
        $api = new HarvestApi();
        $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR ."harvest_api_config.json")) : array();

        if (!$config) {
            $this->markTestSkipped('No API config file present');
        }

      $api->setAccessToken($config->access_token);
      $api->setAccountId($config->account_id);

        /** @var \Harvest\Model\Result $result */
        $result = $api->getClients();

        $this->assertInstanceOf('\Harvest\Model\Result', $result);

        $this->assertTrue($result->isSuccess());
        $this->assertNotEmpty($result->get('headers'));
        $this->assertNotEmpty($result->get('data'));
    }
}
