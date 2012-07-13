publicautocomplete
==================

This civicrm extension allows an anonymous user to have an autocomplete field instead of a free form one for the current employer, to avoid mispelled/duplicate organisation names due to the current employer

tested on profile edit and event registration, patch welcome

Install
======

Still need to figure out the zip install version, so far, git clone https://github.com/TechToThePeople/publicautocomplete.git in your local extension repository and it should work

you need to patch js/rest.js from CRM-10524 if you are not running from trunk

Configuration
=============

By default, it returns all the organisations. 
Think long and hard about what you really want to expose, if you value privacy, that's probably not what you want.


Is it normal for instance to provide the name of organisations that are your IT provider, bank, cleaning, center for drug abuse, restaurants... 
Search all your organisations, and be sure you and they are ok being on a list associated with your org, I'll wait.

Including all the errors of people that registered online and put fake or obscene organisation names? You know, when Dick from the company "two girls and a cup" registered to your events and newsletter?

So you do want to customise the list and restrict to a subset only? Good, that's what I thought.

You need to add in your civicrm.settings.php (or settings.php)
 
 global $civicrm_setting;

$civicrm_setting['eu.tttp.publicautocomplete']['params'] = array('contact_type'=> 'Organisation',
'contact_sub_type' => 'members',
'group' => 42, // active groups
return=>'sort_name,nick_name,country');

the return param is important, always specify only the fields you want to display. Eg. avoid email probably.

You can filter by group, tag, contact type, tag, custom field... pretty much everything you want. Look at eexamples from the contact api, that's the same params. 
 



