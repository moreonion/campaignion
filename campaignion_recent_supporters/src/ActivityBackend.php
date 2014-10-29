<?php

namespace Drupal\campaignion_recent_supporters;

/**
 * Loader for recent supporters.
 *
 * ATTENTION: This class will be used from inside a minimal bootstrap.
 * @see poll.php
 */
class ActivityBackend {
  public static function label() {
    return t('Campaignion activity & Redhen CRM');
  }
  /**
   * Print an empty supporters list.
   */
  public function emptyJson() {
    $this->send(array('supporters' => array()));
  }

  public function buildParams($options, $node = NULL, $types = NULL) {
    $config = array(
      'backend' => get_called_class(),
      'limit' => $options['query_limit'],
      'name_display' => $options['name_display'],
      'addressfield' => variable_get('campaignion_recent_supporters_address_field', 'field_address'),
    );
    if ($node) {
      return new RequestParams($config + array('nid' => $node->nid));
    }
    else {
      return new RequestParams(array(
        'types' => (array) $types,
        'lang' => $GLOBALS['language']->language,
      ) + $config);
    }
  }

  public function recentOnOneAction(RequestParams $params) {
    $config = $params->getParams();
    $sql = <<<SQL
SELECT rc.first_name, rc.last_name, ca.created as timestamp,
  COALESCE(fdfa.{$config['addressfield']}_country, wt.country) AS country,
  n.tnid, n.nid
FROM {node} n
  INNER JOIN {campaignion_activity_webform} caw ON caw.nid = n.nid
  INNER JOIN {campaignion_activity} ca ON ca.activity_id=caw.activity_id AND ca.type='webform_submission'
  INNER JOIN {redhen_contact} rc USING (contact_id)
  INNER JOIN {webform_tracking} wt ON wt.nid=caw.nid AND wt.sid=caw.sid
  LEFT OUTER JOIN {field_data_{$config['addressfield']}} fdfa ON fdfa.entity_id = rc.contact_id AND fdfa.delta = 0
    WHERE n.status = 1
    AND (n.nid = :nid OR n.nid IN (SELECT tn.nid FROM {node} tn INNER JOIN {node} n USING(tnid) WHERE n.nid = :nid AND n.tnid != 0))
ORDER BY ca.created DESC
SQL;
    $result = db_query_range($sql, 0, $config['limit'], array(':nid' => $config['nid']));

    return $this->buildSupporterList($result, $config['name_display']);
  }

  public function recentOnAllActions(RequestParams $params) {
    $config = $params->getParams();
    // get translations: na - activity node, nt - any available node translated into $lang, no - the "original" node (ie. the translation source)
    $sql = <<<SQL
SELECT rc.first_name, rc.last_name, ca.created as timestamp,
  COALESCE(fdfa.{$config['addressfield']}_country, wt.country) AS country,
  na.nid, na.tnid, na.type AS action_type, na.status,
  COALESCE(nt.title, no.title, na.title) AS action_title,
  COALESCE(nt.tnid, no.tnid, na.nid) AS action_nid,
  COALESCE(nt.language, no.language, na.language) AS action_lang
FROM {campaignion_activity_webform} caw
  INNER JOIN {node} na ON caw.nid = na.nid
  LEFT OUTER JOIN {node} nt ON na.tnid != 0 AND nt.tnid = na.tnid AND nt.language = :lang AND nt.status>0
  LEFT OUTER JOIN {node} no ON na.tnid = no.nid AND no.status>0
  INNER JOIN {campaignion_activity} ca ON ca.activity_id = caw.activity_id AND ca.type = 'webform_submission'
  INNER JOIN {redhen_contact} rc USING (contact_id)
  INNER JOIN {webform_tracking} wt ON wt.nid = caw.nid AND wt.sid = caw.sid
  LEFT OUTER JOIN {field_data_{$config['addressfield']}} fdfa ON fdfa.entity_id = rc.contact_id AND fdfa.delta = 0
WHERE na.status = 1 AND na.type IN (:types)
  ORDER BY ca.created DESC
SQL;
    $result = db_query_range($sql, 0, $config['limit'], array(':types' => $config['types'], ':lang' => $config['lang']));
    $rows = array();
    return $this->buildSupporterList($result, $config['name_display']);
  }

  function buildSupporterList($result, $name_display) {
    $supporters = array();

    // resolve "default" name display
    if ((int)$name_display === CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT) {
      $name_display_default = variable_get('campaignion_recent_supporters_name_display_default', CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_INITIAL);
      // if $name_display_default is still CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT
      // (e.g. it was explicitly set in the variable) we override it manually, as
      // 'default' would make no sense here any more
      if ($name_display_default === CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT) {
        $name_display_default = CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_INITIAL;
      }

      $name_display = $name_display_default;
    }

    foreach ($result as $item) {
      $supporter = (array) $item;

      // no CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT any more in $name_display
      switch ($name_display) {
        case CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_FIRST_ONLY:
          // set last_name to empty string
          $supporter['last_name'] = "";
          break;
        case CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_INITIAL:
          // substitute last_name with initial
          // convert every component of last_name to it's first letter
          // also last names with van, von, de, ... and those consisting
          // of two words
          // @TODO with hyphens Last-Name
          if (!empty($supporter['last_name'])) {
            $ln_array = preg_split("/ +/", $supporter['last_name']);
            $supporter['last_name'] = implode(' ', array_map('_campaignion_recent_supporters_strip_callback', $ln_array));
          }
          break;
        case CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_FULL:
        default:
          // nothing to do (full name loaded already
          break;
      }

      $supporter['rfc8601']    = date('c', $item->timestamp);
      if (isset($supporter['action_nid'])) {
        $supporter['action_url']   = $GLOBALS['base_url'] . '/node/' . $supporter['action_nid'];
      }
      $supporters[] = $supporter;
    }

    return $supporters;
  }


  public function recentOnOneActionJson(RequestParams $params) {
    $supporters = $this->recentOnOneAction($params);
    $this->send(array('supporters' => $supporters));
  }

  public function recentOnAllActionsJson(RequestParams $params) {
    $supporters = $this->recentOnAllActions($params);
    $this->send(array('supporters' => $supporters));
  }

  protected function send($data) {
    drupal_add_http_header("Access-Control-Allow-Origin", "*");
    drupal_add_http_header("Access-Control-Allow-Headers", "Content-Type");
    drupal_json_output($data);
  }
}
