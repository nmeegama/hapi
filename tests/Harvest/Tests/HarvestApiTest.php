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
    $entry->set("hours", 3);
    $entry->set("project_id", 16096418);
    $entry->set("task_id", 4220630);
    $entry->set("spent_date", date('Y-m-d'));
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->createEntry($entry, FALSE);
    $rdata = $result->get('data');

    $_SESSION["time_entry_id"] = $rdata["id"];
    $this->assertTrue($result->isSuccess());
  }

  /**
   * @group internet
   */
  public function testUpdateTimeEntry() {
    $entry = new DayEntry();
    $entry->set("id", $_SESSION["time_entry_id"]);
    $entry->set("hours", 3.75);
    $entry->set("notes", "Test Support v2");
    $entry->set("external_reference", [
      "id" => 123456,
      "group_id" => 78910,
      "permalink" => "https://host.fake",
    ]);
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->updateEntry($entry);

    $this->assertTrue($result->isSuccess());
  }

  /**
   * @group internet
   */
  public function testDeleteTimeEntry() {
    $entry = new DayEntry();
    $entry->set("id", $_SESSION["time_entry_id"]);
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config_user.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->deleteEntry($entry);
    $this->assertTrue($result->isSuccess());
  }

  /**
   * @group internet
   */
  public function testAddTimeEntryForGivenUser() {
    $entry = new DayEntry();
    $entry->set("notes", "Test Support");
    $entry->set("hours", 5);
    $entry->set("project_id", 16096418);
    $entry->set("task_id", 4220630);
    $entry->set("user_id", 1918406);
    $entry->set("spent_date", date('Y-m-d'));
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->createEntry($entry, FALSE);
    $rdata = $result->get('data');

    $_SESSION["time_entry_id"] = $rdata["id"];
    $this->assertTrue($result->isSuccess());
  }

  /**
   * @group internet
   */
  public function testFindUserByEmail() {
    $email = 'niraj@origineight.net';
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->getUsers();
    if ($result->isSuccess()) {
      $users = $result->data;
    }
    $user_found = FALSE;
    foreach ($users['users'] as $user) {
      if ($user['email'] == $email) {
        $user_found = TRUE;
      }
    }
    $this->assertTrue($result->isSuccess() && $user_found);
  }

  /**
   * @group internet
   */
  public function testFindUserProjectByTitle() {
    $title = 'teamwo';
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);
    $result = $api->getProjects(['is_active' => TRUE]);
    if ($result->isSuccess()) {
      $projects = $result->data;
    }
    $project_found = FALSE;

    foreach ($projects['projects'] as $project) {
      if (substr_count(strtolower($project['name']), $title) > 0) {
        $_SESSION["project_id"] = $project["id"];
        $project_found = TRUE;
      }
    }
    $this->assertTrue($result->isSuccess() && $project_found);
  }


  public function testFindTaskOfProjectbyTitle() {
    $title = 'development';
    $project_id = $_SESSION["project_id"];
    $config = file_exists(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json") ? json_decode(file_get_contents(BASE_PATH . DIRECTORY_SEPARATOR . "harvest_api_config.json")) : [];
    $api = new HarvestApi();
    $api->setAccessToken($config->access_token);
    $api->setAccountId($config->account_id);

    $result = $api->getProjectTaskAssignments($project_id);
    if ($result->isSuccess()) {
      $task_assignments = $result->data;
    }
    $task_found = FALSE;
    foreach ($task_assignments['task_assignments'] as $task_assignment) {
      if (substr_count(strtolower($task_assignment['task']['name']), $title) > 0) {
        $task_found = TRUE;
      }
    }
    $this->assertTrue($result->isSuccess() && $task_found);
  }

}
