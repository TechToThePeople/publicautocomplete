<?php
/**
 * This api action offers a reduced functionality, to be sure it can be let
 * open to anonyous visitors. You can customise it, but be aware of the
 * potential security risks of exposing more than you want to.
 */
function civicrm_api3_contact_getpublic($params) {
  $term = $params['term'];
  $custom = _publicautocomplete_get_setting('params');

  // Determine column to search in, defaulting to sort_name.
  $match_column = _publicautocomplete_get_setting('match_column');
  if (!$match_column) {
    $match_column = 'sort_name';
  }

  // Some columns are automatically searched using LIKE '%term%'. For those,
  // we just use the search term; but for any other columns, specify LIKE.
  $like_names = array('sort_name', 'email', 'note', 'display_name');
  if (in_array($match_column, $like_names)) {
    $custom[$match_column] = $term;
  }
  else {
    $custom[$match_column] = array(
      'LIKE' => '%' . $term . '%',
    );
  }

  $ret = civicrm_api('Contact', 'Get', $custom);

  if ($ret['is_error']) {
    // If there's any error, return now.
    return $ret;
  }

  // If the term is numeric, the user might be trying to search on a system ID.
  // If we're configured for support of integer searches, perform one and add
  // the results to $ret.
  if (is_numeric($term)) {
    $integer_matches = _publicautocomplete_get_setting('integer_matches');
    if (is_array($integer_matches)) {
      foreach ($integer_matches as $entity) {
        $integer_ret = _civicrm_api3_contact_getpublic_by_entity_integer($term, $entity);
        if ($integer_ret['is_error']) {
          // If there's any error, just return this search result now.
          return $integer_ret;
        }
        $ret['count'] += $integer_ret['count'];
        $ret['values'] += $integer_ret['values'];
      }
    }
  }
  return $ret;
}

function _civicrm_api3_contact_getpublic_by_entity_integer($integer, $entity) {
  if (strtolower($entity) == 'contact') {
    // Searching by Contact ID is a little simpler, so use a dedicated function for that.
    return _civicrm_api3_contact_getpublic_by_contact_id($integer);
  }

  $custom = _publicautocomplete_get_setting('params');

  // Copy api params to api.contact.get, so we can find the contact for this entity
  // as long as the contact matches our configured requirements.
  $custom['api.contact.get'] = $custom;

  // Add and 'id' parameter so we can search for entities with exactly this ID.
  $custom['id'] = $integer;

  // Remove the 'return' param for the entity. We don't want to return anything
  // about the entity itself, just the contact.
  unset($custom['return']);

  $result = civicrm_api($entity, 'Get', $custom);
  // If the API result is an error, just return the result now.
  if ($result['is_error']) {
    return $result;
  }

  // If there are any records found, get the contact values for each returned
  // entity. Replace the api result values with the contact values.
  $contact_values = array();
  if ($result['count']) {
    foreach ($result['values'] as $id => $values) {
      // There may be no contact for this entity. For example, a membership may
      // have been found with the given ID, but the correspondig contact doesn't
      // meet the our configured requirements. In that case, 'count' will be 0
      // and we should skip this entity.
      if (!$values['api.contact.get']['count']) {
        continue;
      }
      $contact_values[$id] = $values['api.contact.get']['values'][$values['contact_id']];
    }
    // Any value in $result['id'] will be for the entity, not for its corresponding
    // contact; just unset it.
    unset($result['id']);
  }
  $result['values'] = $contact_values;
  $result['count'] = count($contact_values);

  return $result;
}

function _civicrm_api3_contact_getpublic_by_contact_id($integer) {
  $custom = _publicautocomplete_get_setting('params');
  $custom['id'] = $integer;
  $result = civicrm_api('contact', 'Get', $custom);
  return $result;
}
