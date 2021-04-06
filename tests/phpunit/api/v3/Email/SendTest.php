<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Email.Send API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Email_SendTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

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
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    parent::setUp();

    // Civi::settings()->set('mailing_backend', ['outBound_option' => CRM_Mailing_Config::OUTBOUND_OPTION_REDIRECT_TO_DB]);
    Civi::settings()->set('mailing_backend', [
      'outBound_option' => CRM_Mailing_Config::OUTBOUND_OPTION_MOCK,
      'preSendCallback' => [static::class, 'captureMailSent'],
    ]);

    // Create a message template. First, dead simple.
    $tpl = civicrm_api3('MessageTemplate', 'create', [
      'msg_title' => "Fix1title",
      'msg_subject' => 'Fix1subject {contact.first_name}',
      'msg_text' => 'Fix1text {contact.first_name}',
      'msg_html' => '<p>Fix1html {contact.first_name}</p>',
    ]);
    $this->messageTemplates = [$tpl['id']];

    /*
    We could test smarty stuff, and implementation of MessageTemplate.send API's
    extra params feature, if we had it.

    We could also test other tokens.

    $tpl = civicrm_api3('MessageTemplate', 'create', [
      'msg_title' => "Fix1",
      'msg_subject' => 'Fix1subject {contact.first_name} {$myparam} Smarty {if 1>0}active{else}inactive{/if}',
      'msg_text' => 'Fix1text {contact.first_name} {$myparam} Smarty {if 1>0}active{else}inactive{/if}',
      'msg_html' => '<p>Fix1html {contact.first_name} {$myparam} Smarty {if 1>0}active{else}inactive{/if}</p>',
    ]);
    $this->messageTemplates = [$tpl['id']];
     */

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

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testSend() {
    civicrm_api3('Email', 'send', [
      'contact_id'  => $this->contactID,
      'template_id' => $this->messageTemplates[0],
    ]);

    list($mock, $recipientEmail, $message) = array_shift(static::$sentMail);
    $this->assertInstanceOf(\Mail_mock::class, $mock);
    $this->assertCount(1, $mock->sentMessages);
    $sent = $mock->sentMessages[0];
    $this->assertEquals(['testy@example.org'], $sent['recipients']);
    $this->assertEquals('Fix1subject Testy', $sent['headers']['Subject']);
    $this->assertContains('Fix1text Testy', $sent['body']);
    $this->assertContains('Fix1html Testy', $sent['body']);
  }

  /**
   * Test that an activity was recorded.
   */
  public function testActivityRecorded() {

    $this->assertEmailActivityCount(0);

    civicrm_api3('Email', 'send', [
      'contact_id'  => $this->contactID,
      'template_id' => $this->messageTemplates[0],
    ]);

    // Test default case: HTML and Text versions recorded in Details field.
    $activity = $this->assertEmailActivityCount(1);
    $this->assertEquals('Fix1subject Testy', $activity['subject']);
    $this->assertContains('Fix1html Testy', $activity['details']);
    $this->assertContains('Fix1text Testy', $activity['details']);
    $this->assertNotContains('Fix1title', $activity['details']);
    $completedStatusID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_status_id', 'Completed');
    $this->assertEquals($completedStatusID, $activity['status_id']);

    // Test just tplName
    civicrm_api3('Email', 'send', [
      'contact_id'  => $this->contactID,
      'template_id' => $this->messageTemplates[0],
      'activity_details' => 'tplName',
    ]);
    $activity = $this->assertEmailActivityCount(2);
    $this->assertEquals('Fix1subject Testy', $activity['subject']);
    // Should not contain the html or text.
    $this->assertNotContains('Fix1html Testy', $activity['details']);
    $this->assertNotContains('Fix1text Testy', $activity['details']);
    // Should contain template name
    $this->assertContains('Fix1title', $activity['details']);
    $this->assertEquals($completedStatusID, $activity['status_id']);

    // Test html only.
    civicrm_api3('Email', 'send', [
      'contact_id'  => $this->contactID,
      'template_id' => $this->messageTemplates[0],
      'activity_details' => 'html',
    ]);
    $activity = $this->assertEmailActivityCount(3);
    $this->assertEquals('Fix1subject Testy', $activity['subject']);
    $this->assertContains('Fix1html Testy', $activity['details']);
    $this->assertNotContains('Fix1text Testy', $activity['details']);
    $this->assertNotContains('Fix1title', $activity['details']);
    $this->assertEquals($completedStatusID, $activity['status_id']);

    // Test text only.
    civicrm_api3('Email', 'send', [
      'contact_id'  => $this->contactID,
      'template_id' => $this->messageTemplates[0],
      'activity_details' => 'text',
    ]);
    $activity = $this->assertEmailActivityCount(4);
    $this->assertEquals('Fix1subject Testy', $activity['subject']);
    $this->assertNotContains('Fix1html Testy', $activity['details']);
    $this->assertContains('Fix1text Testy', $activity['details']);
    $this->assertNotContains('Fix1title', $activity['details']);
    $this->assertEquals($completedStatusID, $activity['status_id']);
  }
  /**
   * Test that recording an activity can be prevented using create_activity parameter.
   */
  public function testActivityNotRecorded() {
    $this->assertEmailActivityCount(0);

    civicrm_api3('Email', 'send', [
      'contact_id'      => $this->contactID,
      'template_id'     => $this->messageTemplates[0],
      'create_activity' => FALSE,
    ]);

    $this->assertEmailActivityCount(0);
  }

  /**
   * Check count of activities. Returns last one (by ID).
   *
   * @return array|null
   */
  protected function assertEmailActivityCount(int $expected, $message=NULL) {
    $activityTypeID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Email');
    $c = civicrm_api3('Activity', 'get', [
      'target_id'        => $this->contactID,
      'activity_type_id' => $activityTypeID,
      'sequential'       => 1,
      'options'          => ['sort' => 'id']
    ]);
    $this->assertEquals($expected, $c['count'], $message);

    if ($c['count']) {
      return array_pop($c['values']);
    }
  }
}
