<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This class tests that the CiviRules action is able to pass through data in a
 * suitable format for the Email.send API's consumption.
 *
 * @group headless
 */
class CRM_Emailapi_CivirulesAction_SendTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use \Civi\Test\Api3TestTrait;

  /** @var array */
  protected static $sentMail = [];
  /** @var array of message tpl ids. */
  protected $messageTemplates = [];
  /** @var int */
  protected $contactID;

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
   * This documents how entity data is prepared.
   *
   * There is a big mix of camelCase and snake_case, _id, Id, ID etc.
   * in use within CiviCRM and its APIs and extensions.
   *
   * e.g. the TokenProcessor will take contactId and create tokens like
   * {contact.display_name} and it will require contribution_recurId to create
   * tokens {contribution_recur.amount}
   *
   * This extension (emailapi) generally deals with CamelCase api entity names,
   * e.g. Contact or ContributionRecur and lower-snake-case. Pre 2.8, it simply
   * removed underscores, which works for Contribution => contribution, contribution_id
   * but broke ContributionRecur wrt tokens.
   *
   * For sending email as a CiviRule acion, there are two transformations that occur:
   * 1. convert the entity keys in trigger data to snake case and extract their IDs at
   *    the top level using, e.g. contribution_id.
   *
   * 2. The CRM_Emailapi_Utils_Tokens then converts those outer x_y_id keys to x_yId,
   *    as needed by the token processor.
   *
   * This test exists because the above is very confusing and makes reasoning
   * hard without the above knowledge being recorded.
   */
  public function testEntityDataMunging() {

    // Set up the fixture data
    $originalData = [
      'id' => 123,
      'amount' => 1,
      'financial_type_id' => 1,
      'processor_id' => 'dummyID1',
      'contact_id' => 1,
    ];
    $data = new CRM_Civirules_TriggerData_Post('ContributionRecur', 1, $originalData);
    $data->setContactId(1);
    $obj = new CRM_Emailapi_CivirulesAction_Send();

    // Subject under test
    $result = $obj->alterApiParametersForTesting([], $data);

    // Test assertions
    $this->assertEquals([
      'contact_id' => 1,
      'extra_data' => [
        'contribution_recur' => $originalData,
      ],
      'contribution_recur_id' => 123,
    ], $result);
  }

  /**
   *
   * This test documents how to specify non-contact entities you want to use tokens for.
   *
   * This is not strictly testing the api, but instead it is testing the shared
   * tokenising function called by the send api.
   */
  public function testReplaceTokens() {
    $this->setUpDbFixtures();

    // Add a contribution recur.
    $contributionRecur = \Civi\Api4\ContributionRecur::create(FALSE)
      ->addValue('contact_id', $this->contactID)
      ->addValue('financial_type_id', 1)
      ->addValue('amount', 1)
      ->addValue('processor_id', 'some_processor_id')
      ->addValue('start_date', '2022-01-01')
      ->addValue('contribution_status_id:name', 'In Progress')
      ->execute()->first();

    $contribution = \Civi\Api4\Contribution::create(FALSE)
      ->addValue('contact_id', $this->contactID)
      ->addValue('contribution_recur_id', $contributionRecur['id'])
      ->addValue('financial_type_id', 1)
      ->addValue('total_amount', 1)
      ->addValue('receive_date', 'now')
      ->addValue('contribution_status_id:name', 'Completed')
      ->execute()->first();
    $contributionID = $contribution['id'];

    // Mock a CiviRule firing.
    // Set up the trigger data @todo look at tests in civirules. Oh there aren't any.
    $triggerData = new CRM_Civirules_TriggerData_Post('ContributionRecur', 1, $contributionRecur);
    $triggerData->setContactId($this->contactID);
    $triggerData->setEntityData('Contribution', $contribution, FALSE);
    $action = new CRM_Emailapi_CivirulesAction_Send();
    $action->setRuleActionData([
      'action_params' => serialize([
        'from_name' => '',
        'from_email' => '',
        'template_id' => $this->messageTemplates[0],
        'disable_smarty' => false,
        'location_type_id' => '',
        'from_email_option' => '',
        'alternative_receiver_address' => '',
        'cc' => '',
        'bcc' => '',
        'file_on_case' => false,
      ]),
      'rule_id' => 1,
      'action_id' => 1,
    ]);
    // The processAction is going to call getActionParameters which requires the ruleAction's action_params to be serialized params.
    $action->processAction($triggerData);

    // Test the results
    list($mock, $recipientEmail, $message) = array_shift(static::$sentMail);
    $this->assertInstanceOf(\Mail_mock::class, $mock);
    $this->assertCount(1, $mock->sentMessages);
    $sent = $mock->sentMessages[0];
    $this->assertEquals(['testy@example.org'], $sent['recipients']);
    $this->assertEquals('Fix1subject Testy', $sent['headers']['Subject']);
    $this->assertContains(<<<TEXT
      contact.id: $this->contactID
      contact.first_name: Testy
      contribution_recur.id: $contributionRecur[id]
      contribution_recur.amount: $1.00
      contribution_recur.processor_id: some_processor_id
      contribution.id: $contributionID
      contribution.total_amount: $1.00
      TEXT,
      $sent['body']);
  }


  // This is (at time of writing) a copy of tests/php_api/v3/Email/SendTest::setUp()
  /**
   */
  public function setUpDbFixtures() {
    // Civi::settings()->set('mailing_backend', ['outBound_option' => CRM_Mailing_Config::OUTBOUND_OPTION_REDIRECT_TO_DB]);
    Civi::settings()->set('mailing_backend', [
      'outBound_option' => CRM_Mailing_Config::OUTBOUND_OPTION_MOCK,
      'preSendCallback' => [static::class, 'captureMailSent'],
    ]);

    // Create a message template. First, dead simple.
    $tpl = civicrm_api3('MessageTemplate', 'create', [
      'msg_title' => "Fix1title",
      'msg_subject' => 'Fix1subject {contact.first_name}',
      'msg_text' => <<<TEXT
        contact.id: {contact.id}
        contact.first_name: {contact.first_name}
        contribution_recur.id: {contribution_recur.id}
        contribution_recur.amount: {contribution_recur.amount}
        contribution_recur.processor_id: {contribution_recur.processor_id}
        contribution.id: {contribution.id}
        contribution.total_amount: {contribution.total_amount}
        TEXT,
      'msg_html' => '<p>Fix1html {contact.first_name}</p>',
    ]);
    $this->messageTemplates = [$tpl['id']];

    // Create a contact.
    $this->contactID = $this->callAPISuccess('Contact', 'create', [
      'first_name' => 'Testy',
      'last_name' => 'McTestFace',
      'email' => 'testy@example.org',
      'contact_type' => 'Individual',
    ])['id'];
  }

  /**
   * Capture sent mail.
   *
   * Nb. the params are a bit of a guess...
   */
  public static function captureMailSent(\Mail_mock $mailMock, $recipientEmail, $headers) {
    static::$sentMail[] = func_get_args();
  }
}

