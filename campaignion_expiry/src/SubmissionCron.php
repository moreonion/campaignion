<?php

namespace Drupal\campaignion_expiry;

/**
 * Cron job for expiring form submission data.
 */
class SubmissionCron {

  protected $timeLimit;

  /**
   * String defining the time frame.
   *
   * @var string
   */
  protected $expireUpToStr;

  /**
   * The point in time when the cron-job was last started.
   *
   * @var int
   */
  protected $lastRunAt;

  /**
   * Create a new submission cron runner.
   *
   * @param int $time_limit
   *   A soft time limit for the cron-job specified in seconds. No new batches
   *   will be started after this time has elapsed.
   * @param string $expire_up_to_str
   *   A string usable with strtotime() that defines how old a submission must
   *   be to be considered for expiry.
   * @param int $last_run_at
   *   The point in time when the cron-job was last started.
   */
  public function __construct(int $time_limit, string $expire_up_to_str, int $last_run_at = NULL) {
    $this->timeLimit = $time_limit;
    $this->expireUpToStr = $expire_up_to_str;
    $this->lastRunAt = $last_run_at ?? 0;
  }

  /**
   * Remove all data from old webform submissions.
   */
  public function run() {
    $now = time();
    $stop_after = $now + $this->timeLimit;
    $expire_from = $this->lastRunAt;
    $expire_up_to = (new \DateTime("@$now"))
      ->modify($this->expireUpToStr)
      ->getTimestamp();
    $last_sid = 0;
    while ((time() < $stop_after) && ($last_sid = $this->expireSubmissionBatch($expire_from, $expire_up_to, $last_sid))) {
      watchdog('campaignion_expiry', 'Expired webform submissions up to sid=@last_sid', ['@last_sid' => $last_sid], WATCHDOG_DEBUG);
    }
    watchdog('campaignion_expiry', 'No more submissions to anonymize for now.', [], WATCHDOG_DEBUG);
    $args = ['@expire_up_to' => strftime('%d/%m/%Y %H:%M:%S', $expire_up_to)];
    watchdog('campaignion_expiry', 'Expired all submissions up to @expire_up_to', $args, WATCHDOG_INFO);
    variable_set('campaignion_expiry_submission_last_run', $this->lastRunAt = $now);
  }

  /**
   * Expire one batch of webform submissions.
   */
  protected function expireSubmissionBatch($from_time, $up_to_time, $last_sid = 0, $batch_size = 1000) {
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
