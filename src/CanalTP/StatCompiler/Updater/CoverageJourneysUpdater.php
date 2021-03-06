<?php
namespace CanalTP\StatCompiler\Updater;

class CoverageJourneysUpdater extends AbstractUpdater
{
    public function getAffectedTable()
    {
        return 'coverage_journeys';
    }

    protected function getDeleteQuery()
    {
        return "DELETE FROM stat_compiled.coverage_journeys WHERE request_date >= (:start_date :: date) and request_date < (:end_date :: date) + interval '1 day'";
    }

    protected function getInsertQuery()
    {
        $insertQuery = <<<EOT
INSERT INTO stat_compiled.coverage_journeys
(
  request_date,
  region_id,
  is_internal_call,
  nb
)
SELECT req.request_date::date,
       cov.region_id,
       CASE WHEN req.user_name LIKE '%canaltp%' THEN 1 ELSE 0 END as is_internal_call,
       COUNT(DISTINCT j.id) AS nb
FROM stat.journeys j
INNER JOIN stat.requests req ON req.id = j.request_id
INNER JOIN stat.coverages cov ON cov.request_id = req.id
WHERE req.request_date >= (:start_date :: date)
AND req.request_date < (:end_date :: date) + interval '1 day'
GROUP BY req.request_date::date, cov.region_id, is_internal_call
;
EOT;

        return $insertQuery;
    }

    protected function getInitQuery()
    {
        $initQuery = <<<EOT
INSERT INTO stat_compiled.coverage_journeys
(
  request_date,
  region_id,
  is_internal_call,
  nb
)
SELECT req.request_date::date,
       cov.region_id,
       CASE WHEN req.user_name LIKE '%canaltp%' THEN 1 ELSE 0 END as is_internal_call,
       COUNT(DISTINCT j.id) AS nb
FROM stat.journeys j
INNER JOIN stat.requests req ON req.id = j.request_id
INNER JOIN stat.coverages cov ON cov.request_id = req.id
GROUP BY req.request_date::date, cov.region_id, is_internal_call
;
EOT;
        return $initQuery;
    }
}
