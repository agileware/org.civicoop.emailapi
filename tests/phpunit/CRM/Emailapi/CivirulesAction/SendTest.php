<?php
use Civi\Token\TokenProcessor;

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Email.Send API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class CRM_Emailapi_CivirulesAction_SendTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  /**
   * Set up for headless tests.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('org.civicoop.civirules')
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   */
  public function testEntityDataMunging() {
    $originalData = [
      'id' => 123,
      'amount' => 1,
      'financial_type_id' => 1,
      'processor_id' => 'dummyID1',
      'contac_id' => 1,
    ];
    $data = new CRM_Civirules_TriggerData_Post('ContributionRecur', 1, $originalData);
    $data->setContactId(1);
    $obj = new CRM_Emailapi_CivirulesAction_Send();

    $actionParameters = [];
    $result = $obj->alterApiParametersForTesting($actionParameters, $data);

    $this->assertEquals([
      'contact_id' => 1,
      'extra_data' => [
        'contributionrecur' => $originalData,
      ],
      'contributionrecur_id' => 123,
    ], $result);
  }
}

