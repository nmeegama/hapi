<?php

namespace Harvest\Tests;

use Harvest\HarvestApi;
use Harvest\Model\DayEntry;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * HarvestApi test cases
 */
class HarvestApiTest extends TestCase {

  public function testNothing() {
    $this->assertTrue(TRUE);
  }

  public function testInstance() {
    $this->assertInstanceOf('Harvest\HarvestApi', new HarvestApi());
  }

  public function testAPIProperties() {
    $api = new HarvestApi();

    $api->setAccessToken("my access token");
    $api->setAccountId("my id");

    $this->assertEquals('my access token', Assert::readAttribute($api, '_accessToken'));
    $this->assertEquals('my id', Assert::readAttribute($api, '_accountId'));
  }

  /**
   * @group internet
   */
  public function testClientsRetrieval() {
    $api = new HarvestApi();
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json")) : [];

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

  /**
   * @group internet
   */
  public function testAddTimeEntry() {
    $entry = new DayEntry();
    $entry->set("notes", "Test Support");
    $entry->set("hours", 2);
    $entry->set("project_id", 16096418);
    $entry->set("task_id", 4220630);
    $entry->set("spent_date", "2018-02-01");
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->createEntry($entry, FALSE);
    $this->assertTrue($result->isSuccess());
  }
}
