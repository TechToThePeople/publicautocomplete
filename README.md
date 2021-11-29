publicautocomplete
==================

This civicrm extension allows an anonymous user to have an autocomplete field instead of a free form one for the current employer, to avoid mispelled/duplicate organization names

Tested on the following forms:
  * profile edit (/civicrm/profile/create)
  * event registration (/civicrm/event/register)
  * contributions pages (/civicrm/contribute/transact)
  * View/edit Drupal account (e.g., /user/%uid/edit/name_and_address)

Patch welcome for other forms.

For the latest version, see: https://github.com/TechToThePeople/publicautocomplete.git

Install
======

Install as a regular CiviCRM extension. For more information, see:  
https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/

Configuration
=============

By default, it returns all the organizations.
Think long and hard about what you really want to expose; if you value privacy, "all organizations" is probably not what you want.

Is it normal, for instance, to provide the name of organizations that are your IT providers, banks, cleaning company, center for drug abuse, restaurants...
Search all your organizations, and be sure you and they are okay being on a list associated with your organization. I'll wait.

Including all the errors of people that registered online? Including the spams, fake or obscene organization names? You know, when "Dick" from the company "two girls and a cup" registered to your events and newsletter?

So you do want to customise the list and restrict to a subset only? Good, that's what I thought.

You need to add in your civicrm.settings.php a new config variable
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['params'] = array(
  'contact_type' => 'Organization',
  'contact_sub_type' => 'members',
  'group' => 42, // active groups
  'return' => 'display_name, city'
);
```

The return param is important, always specify only the fields you want to display, as they will be displayed in the autocomplete options. Eg. avoid email probably.

You can filter by group, tag, contact type, tag, custom field... pretty much everything you want. Look at examples from the contact api, that's the same params.

Other config options
--------------------
### match_column
The matching relies on the api contact get, and by default searches on organization_name.
If you want to search on a different field, use the `match_column` config option:
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['match_column'] = 'sort_name';
// Note that 'sort_name' will search email, organization_name, and display_name.
```

### require_match
If you want to force the user to submit only a value from the list (or leave to leave it blank), thereby preventing the user from creating new organization records, set the `require_match` config option to TRUE (it defaults to FALSE):
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['require_match'] = TRUE;
```

### integer_matches
If you want to allow users to find an organization by entity ID, you can. For example, entering "38" could match the organization with Contact ID 38, or one with Membership ID 38. To enable this type of matching, use the `integer_matches` config option. This is probably only useful if you're making your users aware of their Contact ID or Member ID.
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['integer_matches'] = array(
  'member', // Integers in the autocomplete field will match on an exact Membership ID.
  'contact', // Integers in the autocomplete field will match on an exact Contact ID.
);
```

### accept\_existing\_value
By default, the user may submit the field with its existing value, even if require_match is TRUE and the existing value doesn't fit the configured limiting criteria. To disable this allowance and cause require_match to require a value that actually matches the limiting criteria, set the 'accept_existing_value' config option to FALSE:
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['accept_existing_value'] = FALSE;
```

### additional_forms
If you want to add form class names for which to enable the autocomplete feature (beyond the built-in list), you can specify these as a string (single form name) or array (list of form names). Particularly useful if you have third-party extensions that create their own forms. For example:
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['additional_forms'] = 'CRM_Entity_Form_Type';
```
Or:
```php
global $civicrm_setting;
$civicrm_setting['eu.tttp.publicautocomplete']['additional_forms'] = array(
  'CRM_Entity1_Form_Type1',
  'CRM_Entity2_Form_Type2',
);
```

### Anything else
If you need something else or want to debug, you can modify the api/v3/Contact/Publicget.php and do whatever you want.


Test, and Access rights
===================

To test, connect as a user having access to civicrm, create an event with a profile that contains a current employer field.

If everything is properly installed, you should have an autocomplete instead of a free form.

You can now grant "access AJAX API" to anonymous users (or the users that needs to have the autocomplete) and voila.

Support and Evolutions
=====================
Ask in the extensions forum on civicrm.org.

In general, if you have an idea and the skills to implement it (or the budget to make it happen), it will be added and I might burn a candle while chanting your name as a mantra, or tatoo it on my left shoulder.
