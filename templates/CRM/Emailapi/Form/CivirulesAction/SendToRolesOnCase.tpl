{crmScope key='org.civicoop.emailapi'}
<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-email-send">
  <div class="help-block" id="help">
    {ts}<p>This is the form where you can set what is going to happen with the email.</p>
    <p>The first few fields are relatively straightforward: the <strong>From Name</strong> is the name the email will be sent from and the <strong>From Email</strong> is the email address the email will be sent from. Leave these blank to use the configured defaults.</p>
    <p>The <strong>Message Template</strong> is where you select which CiviCRM message template will be used to compose the mail. You can create and edit them in <strong>Administer>Communications>Message Templates</strong></p>
    <p>The next section allows you to manipulate where the email will be sent to. By default it will be sent to all people with a role on the case. But you can restrict it by certain roles.<br/>
    By <strong>default</strong> the email will be sent to the <strong>primary email address of the contact</strong> in question.<br/>
    </p>
      <p>Finally you can specify an email address for the <strong>CC to</strong> (a copy of the email will be sent to this email address and the email address will be visible to the recipient of the email too) or the <strong>BCC to</strong> (a copy of the email will be sent to this email address and the email address will NOT be visible to the recipient of the email too).</p>
      <p>The sending of the email will also lead to an activity (type <em>Email</em>) being recorded for the contact in question, whatever email address will be used.</p>
    {/ts}
  </div>
  <div class="crm-section">
    <div class="label">{$form.from_name.label}</div>
    <div class="content">{$form.from_name.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.from_email.label}</div>
    <div class="content">{$form.from_email.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.template_id.label}</div>
    <div class="content">{$form.template_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.disable_smarty.label}</div>
    <div class="content">{$form.disable_smarty.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.relationship_type.label}</div>
    <div class="content">{$form.relationship_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.location_type_id.label}</div>
    <div class="content">{$form.location_type_id.html}</div>
    <div class="content" id="location_note">{ts}Note: primary email address will be used if location type email address not found{/ts}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section cc">
    <div class="label">{$form.cc.label}</div>
    <div class="content">{$form.cc.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section bcc">
    <div class="label">{$form.bcc.label}</div>
    <div class="content">{$form.bcc.html}</div>
    <div class="clear"></div>
  </div>

  {if ($has_case)}
    <div class="crm-section">
      <div class="label">{$form.file_on_case.label}</div>
      <div class="content">{$form.file_on_case.html}</div>
      <div class="clear"></div>
    </div>
  {/if}
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
  <script type="text/javascript">
    cj(function() {
      cj('#location_type_id').change(function() {
        triggerFallBackPrimary();
      });
      triggerFallBackPrimary();
    });
  function triggerFallBackPrimary() {
    var locType = cj('#location_type_id').val();
    cj('#location_note').hide();
    if (locType) {
      cj('#location_note').show();
    }
  }
  </script>
{/literal}
{/crmScope}
