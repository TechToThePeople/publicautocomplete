<?php
/* this api action offers a reduced functionality, to be sure it can be let open to anonyous visitors
*
* you can customise it, but be aware of the potential security risks of exposing more than you want to
*/

function civicrm_api3_contact_getpublic ($params) {
  $custom = _publicautocomplete_get_setting('params');
  if (!$custom) {
    $custom = array (
      'contact_type' => 'Organization',
      'return' => 'sort_name,nick_name'
    );
  }

  // Determine column to search in, defaulting to sort_name.
  $match_column = _publicautocomplete_get_setting('match_column');
  if (!$match_column) {
    $match_column = 'sort_name';
  }

  // Some columns are automatically searched using LIKE '%term%'. For those,
  // we just use the search term; but for any other columns, specify LIKE.
  $like_names = array('sort_name', 'email', 'note', 'display_name');
  if (in_array($match_column, $like_names)) {
    $custom[$match_column] = $params['term'];
  }
  else {
    $custom[$match_column] = array(
        'LIKE' => '%'. $params['term'] .'%',
    );
  }
  
  $custom['sequential'] = 1;
  $custom['version'] = 3;
  return civicrm_api ('Contact','Get',$custom);
}
