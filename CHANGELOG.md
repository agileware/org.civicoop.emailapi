# CHANGELOG

## Version 2.20

* Activity Scheduled date trigger: Don't trigger for deleted activities !90
* Rename Case status condition class so it works !79
* Resolved #90 by passing through a list of named parameters
* Add 'is NOT one of' operator to 'Membership Type' condition and fix form validation on rule save (!66)
* Refactor 'contact in group' condition to not reference the `id` column in the group contact cache table !93
* Optimize trigger condition evaluation !94
* Refactored the menu code, making it resistant against order extension menu code that can make this menu disappear.
* Add pre/post eventID tracking to map original/current values for 'Old value/New value' comparisons - requires CiviCRM 5.34: [CiviCRM#19209](https://github.com/civicrm/civicrm-core/pull/19209).
* Added action to update a date value (!100)
* Added condition to compare a date value (!100)
* Add human readable summary of Update Date Value action (!101)
* Renamed condition "Contact does (not) have tag" to "Entity Has\Does (Not) Have Tag(s)". And made this work with entities that support tags in core: Contact, Activity, Case, File (!103)
* Added condition Contact has not an active membership of type.

## Version 2.19

* Add trigger for relationship start date !86
* Add trigger for relationship end date !88
* Added action to remove subtypes of a contact !88

## Version 2.18

* Add ability to send activity email for Add/Edit Activity Actions (!85)
* Fix calls to wrong API function in "UpdateNumericValue.php" (!84)
* Fix activityScheduledDate trigger so it does not re-trigger every day (!83)
* Ability to modify the activity from a rule triggered by an Activity trigger (!82)
* Add condition case custom field changed is one of

## Version 2.17

* Add Scheduled Reminder log trigger and conditions (!61)
* Added action to set a sepcific custom field. (!64)
* Added action to assign activity and send an e-mail (!72)
* Added action to update/set a date value (!73)
* Fixed condition Last Contribution (#86)
* Add Activity Scheduled Date Cron trigger (!77)
* Allow to trigger on case activities or non-case activities and filter by record type (!80)

## Version 2.16

* Show createdby/date in the list if rule has not been modified since it was created
* Improved error handling.
* Fixed #82 - Participant Role condition saves now the value instead of the id
* Fix generic status comparison to work with Campaign and other entities

## Version 2.15

* Fixed issue with participant status comparison.
* Fixed issue with campaign status comparison. (issue 79)
* Fixed issue with contact subtype in combination with other triggers than contact related (issue 80)
* Fixed issue with Field Value Comparison and comparing custom fields.

## Version 2.14.1

* Fixed regression bug in cron triggers.

## Version 2.14

* Fix Field Value Comparision
* Add trigger "Membership is Renewed" that is triggered after a membership is renewed (End date is increased by one term).
* Add 'Save and Done' button to a rule.
* Display Modified date/by instead of Created date/by in list of rules (you can still see Created when editing the rule).
* Change cancel button to close and always redirect to rules list.
* Membership End Date trigger:
  * Allows you to select multiple membership types.
  * Provide ContributionRecur entity so conditions based on the linked recurring contribution can be used.
* Make status condition generic, support matching multiple statuses and add support for the ContributionRecur entity.
* Add ContributionRecur status changed condition and make the parent class more generic to support more entities.
* 'Recurring Payment Processor is' changes:
  * Now works with Contribution entity.
  * Now works for test payment processors too.
* Fixed issue with setting original data on the edit field value comparison screen.
* Add condition "Contact has recurring contribution(s) with status" which checks if the contact has any recurring contributions with a specific status.
* Added logging of the triggered entity


## Version 2.13

* Fixed #58: added locking mechanism to cron triggers to prevent that a rule gets fired again whilst it is running.


## Version 2.12

* Added operator 'Does not contain string' to Field Value condition.
* Make sure we trigger rules after transaction has completed (see also issue #21)
* Added "No Bulk Mail" (is_opt_out) to privacy options action
* Fixed issue #61 (add condition campaign status is (not) one of)
* Added contribution soft credit trigger
* Added condition: "Soft Credit Type is (not) one of"
* Added condition: "Contact added by Contact (not) in Group(s)"
* Added action: "Update Numeric Value"
* Also check smartgroups for 'Contact in group' condition
* Fixed issue #67 ("Event Type is" condition doesn't work)
* Add condition "Recurring Contribution Payment Processor is" that checks which payment processor is linked to the recur
* Add condition "Compare old Membership Status to new Membership Status".
* Add help to rule conditions and update ContributionRecur has payment processor condition
* Fix crash on FieldValueComparison if not saved properly
* Membership Type condition changed so we can check for multiple types
* Added condition 'Membership End Date Changed'

## Version 2.11

* Added action to create relationships.
* Added action to create a membership
* Added action to set financial type of a contribution
* Added condition to check whether a contribution is a recurring contribution
* fixed issue #53 (https://lab.civicrm.org/extensions/civirules/issues/53)
* fixed issue #46 (the is empty condition on the field value comparison is broken)
* fixed issue #59 (added triggers for campaign and condition campaign type)

## Version 2.10

* Added clone butten to the edit rule screen, so you can copy and change only what needs changing (#29)
* Added configuration for the record type for Activity and Case Activity trigger.
* Fixed bug in Activity and Case Activity trigger with an empty contact id.
* Added action to set Case Role
* Added trigger for new UFMatch record (link with CMS user is added)
* Removed the Case Added trigger as it causes errors (check https://lab.civicrm.org/extensions/civirules/issues/45). To do stuff when a new case is added use the Case Activity Added trigger instead with activity type Open Case. During the upgrade all existing rules based on the Case Added trigger will be deleted! They need to be recreated manually with the Case Activity is added trigger with activity type Open Case.
* Added condition Compare old participant status to new participant status

## Version 2.9

* Adds new action: update participant status
* Refactored the way triggers, actions and conditions are inserted in the database upon installation (#24).
* Fixed the fatal error after copying a profile (#19).
* Fixed php warning in CRM_CivirulesConditions_Contact_AgeComparison
* Fixed Cancel button on Rule form returns to "random" page (now it returns to rule overview)
* Fixed uncorrect behavior of isConditionValid with empty value (now returns FALSE)
* Fixed issue 40 (https://lab.civicrm.org/extensions/civirules/issues/40) where the fresh install SQL scripts still create tables with CONSTRAINT ON DELETE RESTRICT rather than ON DELETE CASCADE. There is an upgrade action (2025) linked to this fix which will remove the current constraints on tables civirule_rule_action, civirule_rule_condition and civirule_rule_tag and replace them with CONSTRAINT ON DELETE CASCADE and ON UPDATE RESTRICT.
* Introduces the option to take child groups into consideration for the condition 'contact is (not) in group'.

## Version 2.8
* "Set Thank You Date for a Contribution" action now supports options for time as well as date.
* Added trigger for Event Date reached.
* Added option to compare with original value in Field Value Comparison condition
* Add a condition for contacts being within a specific domain. This is useful for multisite installations as it allows rules to only be executed on contacts that are within that domain's domain_group_id

## Version 2.7
* Changed the ON DELETE NO ACTION to ON DELETE CASCADE for the constraints for tables civirule_rule_action, civirule_rule_condition, civirule_rule_tag which fixes #8
* Fixed notices and warnings on isRuleOnQueue method
* Add "show disabled rules" checkbox on filter for Manage Rules

## Version 2.6
REQUIRES MENU REBUILD! (/civicrm/clearcache)

* Added a trigger for membership end date
* Replaced the Find Rules custom search with a Manage Rules form
