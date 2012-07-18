<?php

require_once 'publicautocomplete.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function publicautocomplete_civicrm_config(&$config) {
  //don't need smarty templates path
  //  _publicautocomplete_civix_civicrm_config($config);
  $path =  dirname( __FILE__ );
  set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

function publicautocomplete_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['contact']['publicget'] = array('access AJAX API');
}

function publicautocomplete_civicrm_buildForm($formName, &$form) {
  $forms = array('CRM_Profile_Form_Edit','CRM_Event_Form_Registration_Register');
  if (!in_array ($formName,$forms))
    return;
  if (!CRM_Core_Permission::check('access CiviCRM') && !CRM_Core_Permission::check('access AJAX API') )
    return;
  $smarty = CRM_Core_Smarty::singleton();
  $smarty->register_postfilter('publicautocomplete_civicrm_add_js');
}

// I would have used drupal_add_js, but isn't cross CMS. Poor's man replacement
function publicautocomplete_civicrm_add_js($tpl_source, &$smarty) {
    $file =  dirname( __FILE__ ) . '/js/public.autocomplete.js';
    return '<script>'.file_get_contents($file) .'</script>' .$tpl_source;
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
