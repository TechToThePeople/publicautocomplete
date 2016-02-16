<?php

require_once 'publicautocomplete.civix.php';

/**
 * Get an array of CiviCRM forms supported by this extension.
 */
function _publicautocomplete_supported_forms() {
  return array('CRM_Profile_Form_Edit','CRM_Event_Form_Registration_Register');
}

/**
 * Test whether a given value is a fully matching organization name.
 */
function _publicautocomplete_validate_current_employer($current_employer) {
  $custom = _publicautocomplete_get_setting('params');
  $custom['return'] = 'organization_name';
  $custom['organization_name'] = $current_employer;
  $custom['sequential'] = 1;
  $custom['version'] = 3;
  $result = civicrm_api('Contact', 'Get', $custom);
  return ($result['count'] > 0);
}

/**
 * Test whether a given value is a fully matching organization name.
 */
function _publicautocomplete_get_setting($name) {
  // If this is the  first time, prime a $settings array with the default values,
  // overridden with any values found by CRM_Core_BAO_Setting::getItem().
  static $settings = array();
  if (empty($settings)) {
    $defaults = array(
      'params' => array(
        'contact_type' => 'organization',
        'return' => 'sort_name,nick_name',
        'contact_is_deleted' => 0,
      ),
      'match_column' => 'sort_name',
      'require_match' => FALSE,
    );

    foreach ($defaults as $key => $value) {
      $config_value = CRM_Core_BAO_Setting::getItem('eu.tttp.publicautocomplete', $key);
      $settings[$key] = $config_value;
    }
    $settings = array_replace_recursive($defaults, $settings);
  }

  // If the setting is still unset, set it from CRM_Core_BAO_Setting::getItem().
  if (!array_key_exists($name, $settings)) {
    $settings[$name] = CRM_Core_BAO_Setting::getItem('eu.tttp.publicautocomplete', $name);
  }
  return $settings[$name];
}

/**
 * Implementation of hook_civicrm_config
 */
function publicautocomplete_civicrm_config(&$config) {
  $extRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $include_path = $extRoot . PATH_SEPARATOR . get_include_path( );
  set_include_path( $include_path );
}

function publicautocomplete_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['contact']['getpublic'] = array('access AJAX API');
}

/**
 * Implements hook_civicrm_buildForm().
 */
function publicautocomplete_civicrm_buildForm($formName, &$form) {
  // Return void if this isn't one of the supported CiviCRM forms.
  $forms = _publicautocomplete_supported_forms();
  if (!in_array ($formName,$forms)) {
    return;
  }
  // Return void if we don't have permission.
  if (!CRM_Core_Permission::check('access CiviCRM') && !CRM_Core_Permission::check('access AJAX API') ) {
    return;
  }
  // Add the necessary javascript file.
  CRM_Core_Resources::singleton()->addScriptFile('eu.tttp.publicautocomplete', 'js/public.autocomplete.js');

  // Define some parameters to pass to JavaScript.
  $autocomplete_params = _publicautocomplete_get_setting('params');
  $returnProperties = explode(',', str_replace(' ', '', $autocomplete_params['return']));
  $vars = array(
    'return_properties' => $returnProperties,
    'require_match' => _publicautocomplete_get_setting('require_match'),
    'required_error' => ts('%1 must be an existing organization name.', $form->_fields['current_employer']['title']),
  );
  CRM_Core_Resources::singleton()->addVars('eu.tttp.publicautocomplete', $vars);
}

/**
 * Implements hook_civicrm_validateForm().
 */
function publicautocomplete_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $forms = _publicautocomplete_supported_forms();
  if (!in_array ($formName,$forms)) {
    return;
  }
  if (_publicautocomplete_get_setting('require_match') !== TRUE) {
    return;
  }
  $current_employer = CRM_Utils_Array::value('current_employer', $fields);
  if ($current_employer && ! _publicautocomplete_validate_current_employer($current_employer)) {
    // Only perform this validation if there's a value in the current_employer
    // field. If there is a value, and if it's not the exact name of an existing
    // valid employer, report an error.
    $errors['current_employer'] = ts('%1 must be an existing organization name.', $form->_fields['current_employer']['title']);
  }
}

/**
 * Implementation of hook_civicrm_install
 */
function publicautocomplete_civicrm_install() {
  return _publicautocomplete_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function publicautocomplete_civicrm_uninstall() {
  return _publicautocomplete_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function publicautocomplete_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _publicautocomplete_civix_civicrm_upgrade($op, $queue);
}
