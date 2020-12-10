<?php

require_once 'publicautocomplete.civix.php';

/**
 * Get an array of CiviCRM forms supported by this extension.
 */
function _publicautocomplete_supported_forms() {
  // FIXME: are there any forms where we /do not/ want this? Maybe change this
  // to match for all forms.
  return array(
    'CRM_Profile_Form_Edit',
    'CRM_Event_Form_Registration_Register',
    'CRM_Contribute_Form_Contribution_Main',
    'CRM_Profile_Form_Dynamic',
  );
}

/**
 * Test whether a given value is a fully matching organization name.
 */
function _publicautocomplete_validate_current_employer($organization_name) {
  $params = _publicautocomplete_get_setting('params');
  $return_properties = explode(',', str_replace(' ', '', $params['return']));
  $name_column = $return_properties[0];

  $custom = array();
  $custom['term'] = $organization_name;
  $custom['version'] = 3;
  $result = civicrm_api('Contact', 'getpublic', $custom);

  $full_matches = array_filter($result['values'], function($item) use ($organization_name, $name_column) {
    return ($item[$name_column] == $organization_name);
  });
  return (!empty($full_matches));
}

/**
 * Get the value of the given config setting.
 */
function _publicautocomplete_get_setting($name) {
  // If this is the  first time, prime a $settings array with the default values,
  // overridden with any values found by CRM_Core_BAO_Setting::getItem().
  static $settings = array();
  if (empty($settings)) {
    $defaults = array(
      'params' => array(
        'return' => 'organization_name',
        'contact_is_deleted' => 0,
      ),
      'match_column' => 'sort_name',
      'require_match' => FALSE,
      'integer_matches' => array(),
      'accept_existing_value' => TRUE,
    );

    foreach ($defaults as $key => $value) {
      $config_value = CRM_Core_BAO_Setting::getItem('eu.tttp.publicautocomplete', $key);
      if (!is_null($config_value)) {
        $settings[$key] = $config_value;
      }
    }
    $settings = array_replace_recursive($defaults, $settings);

    // If the setting is still unset, set it from CRM_Core_BAO_Setting::getItem().
    if (!array_key_exists($name, $settings)) {
      $settings[$name] = CRM_Core_BAO_Setting::getItem('eu.tttp.publicautocomplete', $name);
    }

    // Finally, force some standard API parameters.
    $settings['params']['sequential'] = 0;
    $settings['params']['version'] = 3;
    $settings['params']['contact_type'] = 'organization';
  }

  return $settings[$name];
}

/**
 * Add given key-value pairs to CRM object in Javasript.
 */
function _publicautocomplete_setupJavascript($vars) {
  $resource = CRM_Core_Resources::singleton();
  $resource->addCoreResources();

  // Add the necessary javascript file and configuration vars.
  $resource->addScriptFile('eu.tttp.publicautocomplete', 'js/public.autocomplete-4.5.js', 100, 'html-header');
  $resource->addVars('eu.tttp.publicautocomplete', $vars);
}

/**
 * Implements hook_civicrm_config().
 */
function publicautocomplete_civicrm_config(&$config) {
  $extRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
  $include_path = $extRoot . PATH_SEPARATOR . get_include_path();
  set_include_path($include_path);
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
  if (!in_array($formName, $forms)) {
    return;
  }
  // Return void if we don't have permission.
  if (!CRM_Core_Permission::check('access CiviCRM') && !CRM_Core_Permission::check('access AJAX API')) {
    return;
  }
  // Return void if there's no current_employer field.
  if (!array_key_exists('current_employer', $form->_fields)) {
    return;
  }

  // Define some parameters to pass to JavaScript.
  $autocomplete_params = _publicautocomplete_get_setting('params');
  $return_properties = explode(',', str_replace(' ', '', $autocomplete_params['return']));
  $vars = array(
    'return_properties' => $return_properties,
    'require_match' => _publicautocomplete_get_setting('require_match'),
    'required_error' => ts('%1 must be an existing organization name.', array(1 => $form->_fields['current_employer']['title'], 'domain' => ['eu.tttp.publicautocomplete', NULL])),
  );
  _publicautocomplete_setupJavascript($vars);
}

/**
 * Implements hook_civicrm_validateForm().
 */
function publicautocomplete_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $forms = _publicautocomplete_supported_forms();
  if (!in_array($formName, $forms)) {
    return;
  }
  if (_publicautocomplete_get_setting('require_match') !== TRUE) {
    return;
  }
  $current_employer = CRM_Utils_Array::value('current_employer', $fields);
  if ($current_employer && !_publicautocomplete_validate_current_employer($current_employer)) {
    // Only perform this validation if there's a value in the current_employer
    // field. If there is a value, and if it's not the exact name of an existing
    // valid employer, report an error.
    $errors['current_employer'] = ts('%1 must be an existing organization name.', array(1 => $form->_fields['current_employer']['title'], 'domain' => ['eu.tttp.publicautocomplete', NULL]));
  }
}

/**
 * Implements hook_civicrm_install().
 */
function publicautocomplete_civicrm_install() {
  return _publicautocomplete_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function publicautocomplete_civicrm_uninstall() {
  return _publicautocomplete_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_upgrade().
 */
function publicautocomplete_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _publicautocomplete_civix_civicrm_upgrade($op, $queue);
}
