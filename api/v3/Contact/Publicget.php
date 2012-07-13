<?
/* this api action offers a reduced functionality, to be sure it can be let open to anonyous visitors
*
* you can customise it, but be aware of the potential security risks of exposing more than you want to
*/

function civicrm_api3_contact_publicget ($params) {
  $params['contact_type'] = 'Organization';
  $params['return']= 'sort_name,nick_name,country';
  $params['sequential'] = 1;
  return civicrm_api ('Contact','Get',$params);
}
