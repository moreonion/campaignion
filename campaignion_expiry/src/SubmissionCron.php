<?php

namespace Drupal\campaignion_expiry;

/**
 * Cron jobs for expiring supporter data.
 */
abstract class SubmissionCron {

  /**
   * Remove all data from old webform submissions.
   */
  public static function expireSubmissions() {
    $now = REQUEST_TIME;
    $time_limit = time() + variable_get_value('campaignion_expiry_cron_time_limit');
    $expire_from = variable_get('campaignion_expiry_last_run', 0);
    $expire_up_to = (new \DateTime("@$now"))
      ->modify(variable_get_value('campaignion_expiry_submission_time_frame'))
      ->getTimestamp();
    $last_sid = 0;
    while ((time() < $time_limit) && ($last_sid = static::expireSubmissionBatch($expire_from, $expire_up_to, $last_sid))) {
      watchdog('campaignion_expiry', 'Expired webform submissions up to sid=@last_sid', ['@last_sid' => $last_sid], WATCHDOG_DEBUG);
    }
    watchdog('campaignion_expiry', 'No more submissions to anonymize for now.', [], WATCHDOG_DEBUG);
    $args = ['@expire_up_to' => strftime('%d/%m/%Y %H:%M:%S', $expire_up_to)];
    watchdog('campaignion_expiry', 'Expired all submissions up to @expire_up_to', $args, WATCHDOG_INFO);
    variable_set('campaignion_expiry_last_run', $now);
  }

  /**
   * Expire one batch of webform submissions.
   */
  protected static function expireSubmissionBatch($from_time, $up_to_time, $last_sid = 0, $batch_size = 1000) {
    $sql_pseudo_addresses = <<<SQL
CREATE TEMPORARY TABLE {tmp_pseudo_addresses} (primary key (nid, sid))
SELECT ws.nid, ws.sid, COALESCE(CONCAT(ca.contact_id, '@deleted'), CONCAT(ws.sid, '@form-submission')) as email
FROM {webform_submissions} ws
  LEFT OUTER JOIN (
    {campaignion_activity} ca
    INNER JOIN {campaignion_activity_webform} caw USING(activity_id)
  ) ON caw.nid=ws.nid AND caw.sid=ws.sid AND ca.type='webform_submission'
WHERE ws.sid>:last_sid AND ws.submitted<:up_to_time AND ws.submitted>=:from_time
ORDER BY ws.sid
LIMIT $batch_size;
SQL;
    db_query($sql_pseudo_addresses, [
      ':from_time' => $from_time,
      ':up_to_time' => $up_to_time,
      ':last_sid' => $last_sid,
    ]);

    $sql = <<<SQL
SELECT sid FROM {tmp_pseudo_addresses} ORDER BY sid DESC LIMIT 1;
SQL;
    $new_last_sid = db_query($sql)->fetchField();
    if (!$new_last_sid) {
      return FALSE;
    }

    $sql_delete_submitted_data = <<<SQL
DELETE wsd
FROM {webform_submitted_data} wsd
  INNER JOIN {webform_component} wc USING(nid, cid)
  INNER JOIN {tmp_pseudo_addresses} USING(nid, sid)
WHERE wc.form_key<>'email'
SQL;
    db_query($sql_delete_submitted_data);

    $sql_anonymize_email = <<<SQL
UPDATE {webform_submitted_data} wsd
  INNER JOIN {tmp_pseudo_addresses} t USING(nid, sid)
SET wsd.data=t.email
WHERE wsd.data NOT LIKE '%@deleted' AND wsd.data NOT LIKE '%@form-submission';
SQL;
    db_query($sql_anonymize_email);

    db_query("DROP TEMPORARY TABLE {tmp_pseudo_addresses}");
    return $new_last_sid;
  }

}
