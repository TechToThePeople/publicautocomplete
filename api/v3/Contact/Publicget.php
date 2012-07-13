<?
/* this api action offers a reduced functionality, to be sure it can be let open to anonyous visitors
*
* you can customise it, but be aware of the potential security risks of exposing more than you want to
*/

function civicrm_api3_contact_publicget ($params) {
  $custom= CRM_Core_BAO_Setting::getItem('eu.tttp.publicautocomplete', 'params');
  if (!$custom) {
    $custom = array (
      'contact_type' => 'Organization',
      'return' => 'sort_name,nick_name,country'
    );
  }

  $custom['sort_name'] = $params['sort_name'];
  $custom['sequential'] = 1;
  $custom['version'] = 3;
  return civicrm_api ('Contact','Get',$custom);
}
