# org.civicoop.emailapi

## Version 2.0 and the token processor
Version 2.0 adds support for the new CiviCRM token processor introduced via https://github.com/civicrm/civicrm-core/pull/14662.

**It may not be fully backwards compatible with older versions so you should check before upgrading**

But it adds support for processing activity tokens which is nice.

## Description

E-mail API for CiviCRM to send e-mails through the API

The entity for the E-mail API is Email and the action is Send.

Parameters for the api are specified below:
- `contact_id`: list of contacts IDs to create the PDF Letter (separated by ",")
- `template_id`: ID of the message template which will be used in the API.
- `from_name`: **optional** name of the sender (if you provide this value you have also to provide from_email)
- `from_email`: **optional** e-mail of the sender (if you provide this value you have also to provide from_name)
- `alternative_receiver_address`: **optional** alternative receiver address of the e-mail.
- `case_id`: **optional** adds the e-mail to the case identified by this ID.
- `create_activity`: **optional** (default: 1) Record a copy of the email
  sent in an activity

*It is not possible to specify your own message through the API.*


## Differences with CiviCRM core `MessageTemplate.send` API

For historical reasons
[[1](https://lab.civicrm.org/extensions/emailapi/-/issues/12)]
[[2](https://lab.civicrm.org/extensions/emailapi/-/issues/13)]
we have two similar APIs, here's a list of differences so you can choose one
that's suitable for your needs, and migrate between them should you need to.

- **Message template**: `Email.send` uses `template_id`; `MessageTemplate.send` uses `id`.

- **To**: Both APIs default to details from the Contact record. `Email.send` uses `alternative_receiver_address` to allow override of the address, in which case no friendly name will be used in the To address. `MessageTemplate.send` uses `to_email` and `to_name`.

- **From**: `Email.send` uses separate `from_name` and `from_email` parameters which use system defaults if not supplied; `MessageTemplate.send` uses the combined `"Friendly Name" <email@example.org>` form in `from`.

- **Subject**: Both system take this from the Message Template, however
  `Email.send` also provides a parameter override for this (since !28)

- **Text, HTML**: Both system take these from the Message Template.

- `Email.send` can use more tokens (since v2.0), e.g. activity, case, contribution.

- `Email.send` creates an activity (using core’s Email activity type). This behaviour can be prevented by using the `create_activity` parameter (since !22). `MessageTemplate.send` cannot do this.

- `Email.send` provides an Action to be used with the [Action Provider extension](https://lab.civicrm.org/extensions/action-provider) (separate to, but used with [Form Processor](https://lab.civicrm.org/extensions/form-processor/))

- `Email.send` does not support attachments; `MessageTemplate.send` does.

- `Email.send` does not support passing of template params; `MessageTemplate.send` does.

- `Email.send` does not call `hook_civicrm_alterMailParams`; `MessageTemplate.send` does.




