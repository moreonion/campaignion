<?php

namespace Drupal\campaignion_m2t_send;

use Drupal\little_helpers\Webform\Submission as _Submission;

/**
 * Extend submission objects with a scalable iterator.
 */
class Submission extends _Submission {

  /**
   * Efficently iterate over submissions.
   *
   * @param object[] $nodes
   *   The nodes whichs’ submissions should be iterated.
   * @param string $submission_sql
   *   A SQL query string that selects sids to iterate over. It must use the
   *   :nid and :last_sid parameter and sort by sid. If no query is passed,
   *   then the function iterates over all the submissions of all passed nodes.
   */
  public static function iterate(array $nodes, $submission_sql = NULL) {
    // By default loop over all submissions of each of the passed nodes.
    $submission_sql = $submission_sql ?? <<<SQL
    SELECT sid
    FROM {webform_submissions}
    WHERE nid=:nid AND sid>:last_sid
    ORDER BY sid
    LIMIT 100;
    SQL;

    foreach ($nodes as $node) {
      $args = [':last_sid' => 0, ':nid' => $node->nid];
      while ($sids = db_query($submission_sql, $args)->fetchCol()) {
        foreach (webform_get_submissions(['ws.sid' => $sids]) as $s) {
          yield new static($nodes[$s->nid], $s);
          $args[':last_sid'] = $s->sid;
        }
        gc_collect_cycles();
      }
    }
  }

}
