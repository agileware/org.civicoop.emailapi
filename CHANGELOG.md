# CHANGELOG

## Version 2.8

* [!44](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/44) Bug in from_email_option.
* [!32](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/32) Pass through ID of email Activity with mail params.
* [!34](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/34) Support contribution tokens on CiviRules 2.23+.
* [!42](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/42) From email improvements.
* [!40](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/40) Add action "Send to contact reference".
* [!39](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/39) Don't overwrite contact ID when trigger is contact-based.
* [!31](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/31) Link to the 'Edit MessageTemplate' in action description.
* [!41](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/41) Add composer's package name.
* [!46](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/46) Add code that accidently got removed to disable smarty via API param
* [!49](https://lab.civicrm.org/extensions/emailapi/-/merge_requests/49) Fix entityname processing such that ContributionRecur tokens work.

## Version 2.7

* Implemented a much simpler solution of the token processor (see #21 and !43)

## Version 2.6

* Fixed issue with contact tokens (#21)

## Version 2.5

* Removed token processor functionality and reverted to 'old' way of token replacement after too many and too long issues with tokens.

## Version 2.4

* Fixed issue with Case tokens.

## Version 2.3

* Fixed issue with Event and Participant Tokens.

## Version 2.2

* Fixed issue with Send to Related contact action.
* Fixed issue with Send to role on case action.

## Version 2.1

* Fixed #15: E-mail does not file on case
* Fixed compatibility issue with CiviRules version 2.23 and token replacements.
