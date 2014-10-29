<?php

namespace Drupal\campaignion_recent_supporters;

/**
 * Loader for recent supporters.
 *
 * ATTENTION: This class will be used from inside a minimal bootstrap.
 * @see poll.php
 */
class ActivityBackend {
  /**
   * Print an empty supporters list.
   */
  public function emptyJson() {
    $this->send(array('supporters' => array()));
  }

  public function recentOnOneAction(RequestParams $params) {
    $config = $params->getParams();
    $addressfield = variable_get('campaignion_recent_supporters_address_field', 'field_address');
    $sql = <<<SQL
SELECT rc.first_name, rc.last_name, ca.created as timestamp, wt.country, fdfa.{$addressfield}_country AS supporter_country, n.tnid, n.nid
FROM {node} n
  INNER JOIN {campaignion_activity_webform} caw ON caw.nid = n.nid
  INNER JOIN {campaignion_activity} ca ON ca.activity_id=caw.activity_id AND ca.type='webform_submission'
  INNER JOIN {redhen_contact} rc USING (contact_id)
  INNER JOIN {webform_tracking} wt ON wt.nid=caw.nid AND wt.sid=caw.sid
  LEFT OUTER JOIN {field_data_$addressfield} fdfa ON fdfa.entity_id = rc.contact_id AND fdfa.delta = 0
    WHERE n.status = 1
    AND (n.nid = :nid OR n.nid IN (SELECT tn.nid FROM {node} tn INNER JOIN {node} n USING(tnid) WHERE n.nid = :nid AND n.tnid != 0))
ORDER BY ca.created DESC
SQL;
    $result = db_query_range($sql, 0, $config['limit'], array(':nid' => $config['nid']));

    return $this->buildSupporterList($result, $config['name_display'], FALSE);
  }

  public function recentOnAllActions(RequestParams $params) {
    $config = $params->getParams();
    // variable_get does read empty $conf when called from JSON (as vars are not loaded by poll.php)
    // --> change default value here if needed
    $addressfield = variable_get('campaignion_recent_supporters_address_field', 'field_address');
    // get translations: na - activity node, nt - any available node translated into $lang, no - the "original" node (ie. the translation source)
    $sql = <<<SQL
SELECT rc.first_name, rc.last_name, ca.created as timestamp, wt.country, fdfa.{$addressfield}_country AS supporter_country, na.tnid, na.type, na.title, na.nid, na.language, na.status, nt.language as tlanguage, nt.title as ttitle, nt.nid as nt_nid, nt.status as tstatus, no.language as olanguage, no.title as otitle, no.language as olanguage, no.nid as onid, no.status as ostatus
  FROM {campaignion_activity_webform} caw
  INNER JOIN {node} na ON caw.nid = na.nid
  LEFT OUTER JOIN {node} nt ON na.tnid != 0 AND nt.tnid = na.tnid AND nt.language = :lang
  LEFT OUTER JOIN {node} no ON na.tnid = no.nid
  INNER JOIN {campaignion_activity} ca ON ca.activity_id = caw.activity_id AND ca.type = 'webform_submission'
  INNER JOIN {redhen_contact} rc USING (contact_id)
  INNER JOIN {webform_tracking} wt ON wt.nid = caw.nid AND wt.sid = caw.sid
  LEFT OUTER JOIN {field_data_$addressfield} fdfa ON fdfa.entity_id = rc.contact_id AND fdfa.delta = 0
    WHERE na.status = 1 AND na.type IN (:types)
  ORDER BY ca.created DESC
SQL;
    $result = db_query_range($sql, 0, $config['limit'], array(':types' => $config['types'], ':lang' => $config['lang']));
    return $this->buildSupporterList($result, $config['name_display'], TRUE);
  }

  function buildSupporterList($result, $name_display, $has_text = FALSE) {
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

    global $base_url;

    foreach ($result as $item) {
      $supporter = array();
      $supporter['first_name'] = $item->first_name;
      $supporter['last_name']  = $item->last_name;

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

      $supporter['timestamp']  = $item->timestamp;
      $supporter['rfc8601']    = date('c', $item->timestamp);
      $supporter['country']    = empty($item->supporter_country) ? $item->country : $item->supporter_country;
      if (isset($item->nid)) {
        $supporter['nid'] = $item->nid;
      }
      if ($has_text) {
        $supporter['nid']          = $item->nid;
        $supporter['action_type']  = $item->type;

        $supporter['action_title'] = $item->title;
        $supporter['action_nid']   = $item->nid;
        $supporter['action_lang']  = $item->language;
        // overwrite action details with any translated informations
        if (isset($item->ttitle) && $item->tstatus) {
          $supporter['action_title'] = $item->ttitle;
          $supporter['action_nid']   = $item->nt_nid;
          $supporter['action_lang']  = $item->tlanguage;
        } elseif (isset($item->otitle) && $item->ostatus) {
          $supporter['action_title'] = $item->otitle;
          $supporter['action_nid']   = $item->onid;
          $supporter['action_lang']  = $item->olanguage;
        }

        $supporter['action_url']   = $base_url.'/node/'.$supporter['action_nid'];
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
