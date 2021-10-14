<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Emailapi_CivirulesAction_SendToContactReference extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    if (!empty($actionParams['file_on_case'])) {
      $case = $triggerData->getEntityData('Case');
      $actionParams['case_id'] = $case['id'];
    }

    $contactReferenceField = 'custom_' . $actionParams['contact_reference'];
    $entityData = $triggerData->getEntityData($actionParams['entity']);
    // Find the contact reference contact's ID.
    $contactReferenceIds = (array) $entityData[$contactReferenceField];
    // Not all $triggerData contains custom field data, so look it up if necessary.
    if (!$contactReferenceIds) {
      $contactReferenceIds = civicrm_api3($triggerData->getOriginalEntity(), 'getvalue', [
        'return' => $contactReferenceField,
        'id' => $entityData['id'],
      ]);
    }

    $params = $actionParams;
    foreach ($contactReferenceIds as $contactReferenceId) {
      $params['contact_id'] = $contactReferenceId;
      // change e-mailaddress if other location type is used, falling back on primary if set
      $alternativeAddress = $this->checkAlternativeAddress($params, $contactReferenceId);
      if ($alternativeAddress) {
        $params['alternative_receiver_address'] = $alternativeAddress;
      }
      $extra_data = (array) $triggerData;
      $params['extra_data'] = $extra_data["\0CRM_Civirules_TriggerData_TriggerData\0entity_data"];
      //execute the action
      civicrm_api3('Email', 'send', $params);
    }
  }

  /**
   * Method to check if an alternative address is required. This is the case if:
   * - the location type is set, then the e-mailaddress of the specific location type (if found) is to be used.
   * - if alternative receiver address is set, that is to be used
   *
   * @param array $actionParameters
   * @param int $contactId
   * @return string|bool
   */
  private function checkAlternativeAddress($actionParameters, $contactId) {
    if (isset($actionParameters['location_type_id']) && !empty($actionParameters['location_type_id'])) {
      try {
        $alternateAddress = civicrm_api3('Email', 'getvalue', [
          'return' => 'email',
          'contact_id' => $contactId,
          'location_type_id' => $actionParameters['location_type_id'],
          'options' => ['limit' => 1, 'sort' => 'id DESC'],
        ]);
        return (string) $alternateAddress;
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return FALSE;
  }

  /**
   * Get a list of entities that use custom fields.
   *
   * @return array
   * @access public
   */
  public static function getContactReferenceEntities() {
    $return[] = '-- please select --';
    $result = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'data_type' => "ContactReference",
      'return' => ["custom_group_id.extends"],
      'options' => ['limit' => 0],
    ])['values'];
    foreach ($result as $field) {
      $return[$field['custom_group_id.extends']] = $field['custom_group_id.extends'];
    }
    $return = array_unique($return);
    asort($return);
    return $return;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirules/actions/emailapi_contactreference', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $template = 'unknown template';
    $params = $this->getActionParameters();

    $messageTemplates = new CRM_Core_DAO_MessageTemplate();
    $messageTemplates->id = $params['template_id'];
    $messageTemplates->is_active = TRUE;
    if ($messageTemplates->find(TRUE)) {
      $template = $messageTemplates->msg_title;
    }
    if (isset($params['location_type_id']) && !empty($params['location_type_id'])) {
      try {
        $locationText = 'location type ' . civicrm_api3('LocationType', 'getvalue', [
          'return' => 'display_name',
          'id' => $params['location_type_id'],
        ]) . ' with primary e-mailaddress as fall back';
      }
      catch (CiviCRM_API3_Exception $ex) {
        $locationText = 'location type ' . $params['location_type_id'];
      }
    }
    else {
      $locationText = "primary e-mailaddress";
    }
    $to = civicrm_api3('CustomField', 'getvalue', ['return' => "label", 'id' => $params['contact_reference']]);

    $cc = "";
    if (!empty($params['cc'])) {
      $cc = ts(' and cc to %1', [1 => $params['cc']]);
    }
    $bcc = "";
    if (!empty($params['bcc'])) {
      $bcc = ts(' and bcc to %1', [1 => $params['bcc']]);
    }
    return ts('Send e-mail from "%1 (%2 using %3)" with Template "%4" to %5 %6 %7', [
      1 => $params['from_name'],
      2 => $params['from_email'],
      3 => $locationText,
      4 => $template,
      5 => $to,
      6 => $cc,
      7 => $bcc,
    ]);
  }

}
