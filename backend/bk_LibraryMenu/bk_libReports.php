<?php
/**
 * Library Analytics - Backend Handler
 */

include "../../db/dbconnection.php";
header('Content-Type: application/json');

define('USER_DISPLAY_FIELD', 'id_number');

define('COLLEGE_COLOR_MAP', [
    'CAF' => 'rgba(22,163,74,0.88)',
    'CAS' => 'rgba(234,88,12,0.88)',
    'CBM' => 'rgba(202,138,4,0.88)',
    'CET' => 'rgba(220,38,38,0.88)',
    'CED' => 'rgba(37,99,235,0.88)',
    'CVM' => 'rgba(107,114,128,0.88)',
]);
define('COLLEGE_COLOR_FALLBACK', 'rgba(139,92,246,0.88)');

// ── UTILITY FUNCTIONS ────────────────────────────────────────────────────────

//CALCULATE
// Returns the duration in minutes between check-in and check-out, or 0 if no check-out time is provided.
function calcDurationMinutes(string $checkinTime, ?string $checkoutTime): float
{
    if (!$checkoutTime) return 0;
    return (strtotime($checkoutTime) - strtotime($checkinTime)) / 60;
}

function filterByClassification(array $logs, string $classification): array
{
    return array_filter($logs, fn($log) => strtolower($log['classification']) === strtolower($classification));
}

function excludeNonStudents(array $logs): array
{
    return array_filter($logs, fn($log) => strtolower($log['classification'] ?? '') === 'student');
}

function formatDateTime(string $datetime): string
{
    return date('M j, Y g:i A', strtotime($datetime));
}

function safe(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function getUserDisplayLabel(array $log): string
{
    return $log[USER_DISPLAY_FIELD] ?? $log['id_number'];
}

function resolveCollegeColor(string $collegeName): string
{
    $upperName = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbreviation => $color) {
        if (strpos($upperName, strtoupper($abbreviation)) !== false) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

// ── FILTER & FETCH ───────────────────────────────────────────────────────────

function buildWhereClauseFromFilters(array $postData): array
{
    $where  = '';
    $params = [];

    if (!empty($postData['startDate'])) {
        $where .= " AND CAST(l.checkin_time AS DATE) >= :startDate";
        $params[':startDate'] = $postData['startDate'];
    }
    if (!empty($postData['endDate'])) {
        $where .= " AND CAST(l.checkin_time AS DATE) <= :endDate";
        $params[':endDate'] = $postData['endDate'];
    }
    if (!empty($postData['classification']) && $postData['classification'] !== 'All') {
        $where .= " AND l.classification = :classification";
        $params[':classification'] = $postData['classification'];
    }
    if (!empty($postData['library']) && $postData['library'] !== 'All') {
        $where .= " AND l.library = :libraryId";
        $params[':libraryId'] = $postData['library'];
    }

    return [$where, $params];
}

function fetchFilteredVisitLogs(string $where, array $params): array
{
    return execsqlSRS("
        SELECT l.id, l.id_number, l.name, l.college, l.course,
               l.library AS library_section_id, s.SectionName AS library_section_name,
               l.checkin_time, l.checkout_time, l.sex, l.classification
        FROM   Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE  1=1 {$where}
        ORDER  BY l.checkin_time DESC
    ", 'Select', $params);
}

// ── KPI & AGGREGATION ────────────────────────────────────────────────────────

function computeDashboardKpis(array $logs, string $endDate): array
{
    $total        = count($logs);
    $totalMinutes = array_sum(array_map(fn($log) => calcDurationMinutes($log['checkin_time'], $log['checkout_time']), $logs));
    $unique       = count(array_unique(array_column($logs, 'id_number')));
    $avg          = $total ? round($totalMinutes / $total, 1) : 0;
    $endCount     = $endDate
        ? count(array_filter($logs, fn($log) => substr($log['checkin_time'], 0, 10) === $endDate))
        : 0;

    return [
        'totalVisits'     => $total,
        'totalDuration'   => round($totalMinutes),
        'uniqueUsers'     => $unique,
        'avgDuration'     => $avg,
        'endDateCheckins' => $endCount,
    ];
}

function aggregateTopUsersByClassification(array $logs): array
{
    $topC = $topD = [];

    foreach (['Student', 'Employee', 'Guest'] as $cls) {
        $clsLogs = filterByClassification($logs, $cls);
        $counts  = [];
        $durs    = [];
        $meta    = [];

        foreach ($clsLogs as $log) {
            $userId          = $log['id_number'];
            $counts[$userId] = ($counts[$userId] ?? 0) + 1;
            $durs[$userId]   = ($durs[$userId]   ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);

            if (!isset($meta[$userId])) {
                $meta[$userId] = [
                    'display_label' => getUserDisplayLabel($log),
                    'name'          => $log['name'] ?? '',
                    'college'       => $log['college'] ?? '',
                    'course'        => $log['course'] ?? '',
                    'library'       => $log['library_section_name'],
                    'last_checkin'  => $log['checkin_time'],
                ];
            } elseif ($log['checkin_time'] > $meta[$userId]['last_checkin']) {
                $meta[$userId]['last_checkin'] = $log['checkin_time'];
            }
        }

        uksort($counts, fn($a, $b) => $counts[$b] <=> $counts[$a] ?: strcmp($a, $b));
        $topC[$cls] = [];
        $counter    = 0;
        foreach ($counts as $userId => $total) {
            if ($counter >= 3) break;
            $topC[$cls][$userId] = array_merge($meta[$userId], ['count' => $total]);
            $counter++;
        }

        uksort($durs, fn($a, $b) => $durs[$b] <=> $durs[$a] ?: strcmp($a, $b));
        $topD[$cls] = [];
        $counter    = 0;
        foreach ($durs as $userId => $minutes) {
            if ($counter >= 3) break;
            $topD[$cls][$userId] = array_merge($meta[$userId], ['minutes' => $minutes]);
            $counter++;
        }
    }

    return ['topCheckins' => $topC, 'topDuration' => $topD];
}

function aggregateClassificationDistribution(array $logs): array
{
    $out = [];
    foreach ($logs as $log) {
        $key       = $log['classification'] ?: 'Unknown';
        $out[$key] = ($out[$key] ?? 0) + 1;
    }
    return $out;
}

function aggregateTopColleges(array $logs): array
{
    $studentLogs = excludeNonStudents($logs);
    $uniq        = [];
    $cnt         = [];
    $dur         = [];
    $last        = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $studentId = $log['id_number'];

        if (!isset($uniq[$college][$studentId])) {
            $uniq[$college][$studentId] = true;
            $cnt[$college]              = ($cnt[$college] ?? 0) + 1;
        }
        $dur[$college] = ($dur[$college] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if (!isset($last[$college]) || $log['checkin_time'] > $last[$college]) {
            $last[$college] = $log['checkin_time'];
        }
    }

    uksort($cnt, fn($a, $b) => $cnt[$b] <=> $cnt[$a] ?: strcmp($a, $b));
    $topC    = [];
    $counter = 0;
    foreach ($cnt as $college => $total) {
        if ($counter >= 3) break;
        $topC[$college] = ['count' => $total, 'last_checkin' => $last[$college], 'color' => resolveCollegeColor($college)];
        $counter++;
    }

    uksort($dur, fn($a, $b) => $dur[$b] <=> $dur[$a] ?: strcmp($a, $b));
    $topD    = [];
    $counter = 0;
    foreach ($dur as $college => $minutes) {
        if ($counter >= 3) break;
        $topD[$college] = ['minutes' => $minutes, 'last_checkin' => $last[$college], 'color' => resolveCollegeColor($college)];
        $counter++;
    }

    return ['top3CollegesCheckin' => $topC, 'top3CollegesDuration' => $topD];
}

function aggregateTopCoursesByCollege(array $logs): array
{
    $studentLogs = excludeNonStudents($logs);
    $uniq        = [];
    $cnt         = [];
    $dur         = [];
    $last        = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $course    = $log['course']  ?: 'Unknown';
        $studentId = $log['id_number'];
        $key       = "{$college}|{$course}";

        if (!isset($uniq[$college][$course][$studentId])) {
            $uniq[$college][$course][$studentId] = true;
            $cnt[$college][$course]              = ($cnt[$college][$course] ?? 0) + 1;
        }
        $dur[$college][$course] = ($dur[$college][$course] ?? 0) + calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if (!isset($last[$key]) || $log['checkin_time'] > $last[$key]) {
            $last[$key] = $log['checkin_time'];
        }
    }

    $topC = [];
    foreach ($cnt as $college => $courses) {
        uksort($courses, fn($a, $b) => $courses[$b] <=> $courses[$a] ?: strcmp($a, $b));
        $topC[$college] = [];
        $counter        = 0;
        foreach ($courses as $course => $total) {
            if ($counter >= 3) break;
            $topC[$college][$course] = ['count' => $total, 'last_checkin' => $last["{$college}|{$course}"] ?? null];
            $counter++;
        }
    }

    $topD = [];
    foreach ($dur as $college => $courses) {
        uksort($courses, fn($a, $b) => $dur[$college][$b] <=> $dur[$college][$a] ?: strcmp($a, $b));
        $topD[$college] = [];
        $counter        = 0;
        foreach ($courses as $course => $minutes) {
            if ($counter >= 3) break;
            $topD[$college][$course] = ['minutes' => $minutes, 'last_checkin' => $last["{$college}|{$course}"] ?? null];
            $counter++;
        }
    }

    return ['topCoursesCheckin' => $topC, 'topCoursesDuration' => $topD];
}

function aggregateSexDistribution(array $logs): array
{
    $out = [];
    foreach ($logs as $log) {
        $key       = $log['sex'] ?: 'Unknown';
        $out[$key] = ($out[$key] ?? 0) + 1;
    }
    return $out;
}

function aggregateCollegeDistribution(array $logs): array
{
    $studentLogs = excludeNonStudents($logs);
    $uniq        = [];
    $cnt         = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $studentId = $log['id_number'];
        if (!isset($uniq[$college][$studentId])) {
            $uniq[$college][$studentId] = true;
            $cnt[$college]              = ($cnt[$college] ?? 0) + 1;
        }
    }

    uksort($cnt, fn($a, $b) => $cnt[$b] <=> $cnt[$a] ?: strcmp($a, $b));
    $out = [];
    foreach ($cnt as $college => $total) {
        $out[$college] = ['count' => $total, 'color' => resolveCollegeColor($college)];
    }
    return $out;
}

// ── RANK HELPER ──────────────────────────────────────────────────────────────

function annotateRanks(array $items, string $valueKey): array
{
    $firstRankForValue = [];
    $countPerValue     = [];

    foreach ($items as $index => $item) {
        $value = $item[$valueKey];
        if (!isset($firstRankForValue[$value])) {
            $firstRankForValue[$value] = $index + 1;
        }
        $countPerValue[$value] = ($countPerValue[$value] ?? 0) + 1;
    }

    return array_map(function ($item) use ($firstRankForValue, $countPerValue, $valueKey) {
        $value             = $item[$valueKey];
        $item['rank']      = $firstRankForValue[$value];
        $item['tied']      = $countPerValue[$value] > 1;
        $item['tiedCount'] = $countPerValue[$value];
        return $item;
    }, $items);
}

// ── KPI TOP-3 BUILDER ────────────────────────────────────────────────────────

function buildKpiTop3(array $logs): array
{
    $studentLogs = array_filter($logs, fn($log) => strtolower($log['classification'] ?? '') === 'student');

    // Top 3 Students
    $visitCount  = [];
    $studentMeta = [];

    foreach ($studentLogs as $log) {
        $studentId              = $log['id_number'];
        $visitCount[$studentId] = ($visitCount[$studentId] ?? 0) + 1;
        if (!isset($studentMeta[$studentId])) {
            $studentMeta[$studentId] = [
                'id_number' => $studentId,
                'name'      => $log['name']    ?? '',
                'college'   => $log['college'] ?? '',
                'course'    => $log['course']  ?? '',
            ];
        }
    }

    uksort($visitCount, fn($a, $b) => $visitCount[$b] <=> $visitCount[$a] ?: strcmp($a, $b));
    $top3Students = [];
    foreach ($visitCount as $studentId => $count) {
        if (count($top3Students) >= 3) break;
        $top3Students[] = array_merge($studentMeta[$studentId], ['count' => $count]);
    }
    $top3Students = annotateRanks($top3Students, 'count');

    // Top 3 Colleges
    $seenPerCollege      = [];
    $collegeVisitorCount = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $studentId = $log['id_number'];
        if (!isset($seenPerCollege[$college][$studentId])) {
            $seenPerCollege[$college][$studentId] = true;
            $collegeVisitorCount[$college]         = ($collegeVisitorCount[$college] ?? 0) + 1;
        }
    }

    uksort($collegeVisitorCount, fn($a, $b) => $collegeVisitorCount[$b] <=> $collegeVisitorCount[$a] ?: strcmp($a, $b));
    $top3Colleges = [];
    foreach ($collegeVisitorCount as $college => $count) {
        if (count($top3Colleges) >= 3) break;
        $top3Colleges[] = ['name' => $college, 'count' => $count];
    }
    $top3Colleges = annotateRanks($top3Colleges, 'count');

    // Top 3 Courses
    $seenPerCourse      = [];
    $courseVisitorCount = [];

    foreach ($studentLogs as $log) {
        $college   = $log['college'] ?: 'Unknown';
        $course    = $log['course']  ?: 'Unknown';
        $studentId = $log['id_number'];
        $key       = "{$college}|{$course}";
        if (!isset($seenPerCourse[$key][$studentId])) {
            $seenPerCourse[$key][$studentId] = true;
            $courseVisitorCount[$key]         = ($courseVisitorCount[$key] ?? 0) + 1;
        }
    }

    uksort($courseVisitorCount, fn($a, $b) => $courseVisitorCount[$b] <=> $courseVisitorCount[$a] ?: strcmp($a, $b));
    $top3Courses = [];
    foreach ($courseVisitorCount as $key => $count) {
        if (count($top3Courses) >= 3) break;
        [$college, $course] = explode('|', $key, 2);
        $top3Courses[] = ['college' => $college, 'course' => $course, 'count' => $count];
    }
    $top3Courses = annotateRanks($top3Courses, 'count');

    return compact('top3Students', 'top3Colleges', 'top3Courses');
}

// ── VIEW ALL BUILDERS ────────────────────────────────────────────────────────

function buildViewAllUsers(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach ($logs as $log) {
        $userId = $log['id_number'];
        if (!isset($agg[$userId])) {
            $agg[$userId] = [
                'display_label' => getUserDisplayLabel($log),
                'name'          => $log['name'] ?? '',
                'college'       => $log['college'] ?? '',
                'course'        => $log['course'] ?? '',
                'type'          => $log['classification'],
                'library'       => $log['library_section_name'],
                'checkins'      => 0,
                'duration'      => 0,
                'last_checkin'  => $log['checkin_time'],
            ];
        }
        $agg[$userId]['checkins']++;
        $agg[$userId]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $agg[$userId]['last_checkin']) {
            $agg[$userId]['last_checkin'] = $log['checkin_time'];
        }
    }
    uasort($agg, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_values(array_slice($agg, $offset, $limit, true)), 'total' => count($agg)];
}

function buildViewAllColleges(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach (excludeNonStudents($logs) as $log) {
        $college = $log['college'] ?: 'Unknown';
        if (!isset($agg[$college])) {
            $agg[$college] = ['college_name' => $college, 'unique_visitors' => [], 'duration' => 0, 'last_checkin' => $log['checkin_time']];
        }
        $agg[$college]['unique_visitors'][$log['id_number']] = true;
        $agg[$college]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $agg[$college]['last_checkin']) {
            $agg[$college]['last_checkin'] = $log['checkin_time'];
        }
    }
    $rows = [];
    foreach ($agg as $data) {
        $rows[] = ['name' => $data['college_name'], 'checkins' => count($data['unique_visitors']), 'duration' => $data['duration'], 'last_checkin' => $data['last_checkin']];
    }
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllCourses(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach (excludeNonStudents($logs) as $log) {
        $key = ($log['college'] ?: 'Unknown') . '|' . ($log['course'] ?: 'Unknown');
        if (!isset($agg[$key])) {
            $agg[$key] = [
                'college'         => $log['college'] ?: 'Unknown',
                'course'          => $log['course']  ?: 'Unknown',
                'unique_visitors' => [],
                'duration'        => 0,
                'last_checkin'    => $log['checkin_time'],
            ];
        }
        $agg[$key]['unique_visitors'][$log['id_number']] = true;
        $agg[$key]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $agg[$key]['last_checkin']) {
            $agg[$key]['last_checkin'] = $log['checkin_time'];
        }
    }
    $rows = [];
    foreach ($agg as $data) {
        $rows[] = ['college' => $data['college'], 'course' => $data['course'], 'checkins' => count($data['unique_visitors']), 'duration' => $data['duration'], 'last_checkin' => $data['last_checkin']];
    }
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllLogs(array $logs, int $offset, int $limit): array
{
    $rows = array_map(fn($log) => [
        'id_number'      => $log['id_number']            ?? '',
        'name'           => $log['name']                 ?? '',
        'college'        => $log['college']              ?? '',
        'course'         => $log['course']               ?? '',
        'classification' => $log['classification']       ?? '',
        'library'        => $log['library_section_name'] ?? '',
        'sex'            => $log['sex']                  ?? '',
        'checkin_time'   => $log['checkin_time']         ?? '',
        'checkout_time'  => $log['checkout_time']        ?? null,
        'duration'       => calcDurationMinutes($log['checkin_time'], $log['checkout_time'] ?? null),
    ], $logs);

    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllDemographics(array $logs, int $offset, int $limit): array
{
    $rows = array_map(fn($log) => [
        'display_label' => getUserDisplayLabel($log),
        'sex'           => $log['sex'],
        'checkin'       => $log['checkin_time'],
        'checkout'      => $log['checkout_time'],
        'duration'      => calcDurationMinutes($log['checkin_time'], $log['checkout_time']),
    ], $logs);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

// ── MODAL TABLE & PAGINATION ─────────────────────────────────────────────────

function renderModalTable(string $tab, array $rows): string
{
    $cols = [
	'logs' => [
    'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
    'rowFn'   => fn($row) =>
        '<td class="ps-3 fw-semibold">' . safe($row['id_number'])           . '</td>' .
        '<td class="text-muted small">' . safe($row['name']           ?: '—') . '</td>' .
        '<td class="text-muted small">' . safe($row['college']        ?: '—') . '</td>' .
        '<td class="text-muted small">' . safe($row['course']         ?: '—') . '</td>' .
        '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">'
            . safe($row['classification'] ?: '—') . '</span></td>' .
        '<td class="text-muted small">' . safe($row['library']        ?: '—') . '</td>' .
        '<td class="text-muted small">' . safe($row['sex']            ?: '—') . '</td>' .
        '<td class="text-muted small">' . ($row['checkin_time']  ? formatDateTime($row['checkin_time'])  : '—') . '</td>' .
        '<td class="text-muted small">' . ($row['checkout_time'] ? formatDateTime($row['checkout_time']) : '—') . '</td>' .
        '<td class="text-end pe-3">'    . (isset($row['duration']) ? (int)round($row['duration']) : '—') . '</td>',
],

        'users' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">'   . safe($row['display_label'])  . '</td>' .
                '<td class="text-muted small">'    . safe($row['name'])           . '</td>' .
                '<td class="text-muted small">'    . safe($row['college'] ?: '—') . '</td>' .
                '<td class="text-muted small">'    . safe($row['course']  ?: '—') . '</td>' .
                '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">' . safe($row['type']) . '</span></td>' .
                '<td class="text-muted small">'    . safe($row['library'] ?? '—') . '</td>' .
                '<td class="text-end fw-semibold text-primary">' . (int)$row['checkins'] . '</td>' .
                '<td class="text-end">'            . (int)round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . formatDateTime($row['last_checkin']) . '</td>',
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . safe($row['name'])    . '</td>' .
                '<td class="text-end">'         . (int)$row['checkins'] . '</td>' .
                '<td class="text-end">'         . (int)round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . formatDateTime($row['last_checkin']) . '</td>',
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 text-muted small">' . safe($row['college'])  . '</td>' .
                '<td class="fw-semibold">'            . safe($row['course'])   . '</td>' .
                '<td class="text-end">'               . (int)$row['checkins'] . '</td>' .
                '<td class="text-end">'               . (int)round($row['duration']) . '</td>' .
                '<td class="text-muted small pe-3">'  . formatDateTime($row['last_checkin']) . '</td>',
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($row) =>
                '<td class="ps-3 fw-semibold">' . safe($row['display_label']) . '</td>' .
                '<td>'                           . safe($row['sex'])           . '</td>' .
                '<td class="text-muted small">'  . formatDateTime($row['checkin'])   . '</td>' .
                '<td class="text-muted small">'  . ($row['checkout'] ? formatDateTime($row['checkout']) : '—') . '</td>' .
                '<td class="text-end pe-3">'     . (int)round($row['duration']) . '</td>',
        ],
    ];

    if (!isset($cols[$tab])) return '';
    $cfg   = $cols[$tab];
    $heads = implode('', array_map(fn($header) => "<th class=\"small fw-semibold\">{$header}</th>", $cfg['headers']));
    $body  = implode('', array_map(fn($row) => '<tr>' . ($cfg['rowFn'])($row) . '</tr>', $rows));
    return "<div class=\"table-responsive\"><table class=\"table table-sm table-striped table-hover align-middle mb-0\"><thead class=\"table-dark\"><tr>{$heads}</tr></thead><tbody class=\"small\">{$body}</tbody></table></div>";
}

function renderModalPagination(int $totalPages, int $current, int $totalRecords, int $rowsPerPage): string
{
    if ($totalPages <= 1) return '';

    $isFirst = $current === 1;
    $isLast  = $current === $totalPages;
    $prev    = max(1, $current - 1);
    $next    = min($totalPages, $current + 1);
    $window  = 5;
    $start   = max(1, min($current - intdiv($window, 2), $totalPages - $window + 1));
    $end     = min($totalPages, $start + $window - 1);

    $li = fn(string $label, int $page, string $extra = '', bool $isText = false) =>
        "<li class=\"page-item {$extra}\"><a class=\"page-link\" href=\"#\"" . ($isText ? '' : " data-page=\"{$page}\"") . ">{$label}</a></li>";

    $items  = '';
    $items .= $li('«', 1,           $isFirst ? 'disabled' : '');
    $items .= $li('‹', $prev,       $isFirst ? 'disabled' : '');
    if ($start > 1) {
        $items .= $li('1', 1, $current === 1 ? 'active' : '');
        if ($start > 2) $items .= $li('…', 0, 'disabled', true);
    }
    for ($page = $start; $page <= $end; $page++) {
        $items .= $li((string)$page, $page, $page === $current ? 'active' : '');
    }
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $items .= $li('…', 0, 'disabled', true);
        $items .= $li((string)$totalPages, $totalPages, $current === $totalPages ? 'active' : '');
    }
    $items .= $li('›', $next,       $isLast ? 'disabled' : '');
    $items .= $li('»', $totalPages, $isLast ? 'disabled' : '');

    $from = (($current - 1) * $rowsPerPage) + 1;
    $to   = min($current * $rowsPerPage, $totalRecords);
    $info = "<small class=\"text-muted\">Showing {$from}–{$to} of {$totalRecords} records</small>";
    return "{$info}<nav class=\"mt-1\"><ul class=\"pagination pagination-sm mb-0 flex-wrap justify-content-center\">{$items}</ul></nav>";
}

// ── TAB HTML RENDERERS ───────────────────────────────────────────────────────
function renderLogsTab(array $allLogsFlat): string
{
    ob_start(); ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
            <div>
                <span class="fw-semibold small">All Visit Logs</span>
                <p class="text-muted mb-0" style="font-size:.72rem;">Every check-in within selected date range</p>
            </div>
            <button class="btn btn-sm btn-outline-primary py-0 px-2 view-all-btn"
                    data-tab="logs" style="font-size:.75rem;">
                <i class="bi bi-arrow-up-right-square me-1"></i>View All
            </button>
        </div>
        <div class="card-body p-0"
             id="allLogsCard"
             data-rows="<?= htmlspecialchars(json_encode($allLogsFlat), ENT_QUOTES) ?>"
             data-per-page="10">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th class="ps-3 small">ID Number</th><th class="small">Name</th>
                        <th class="small">College</th><th class="small">Course</th>
                        <th class="small">Type</th><th class="small">Section</th>
                        <th class="small">Sex</th><th class="small">Check-in</th>
                        <th class="small">Check-out</th>
                        <th class="text-end pe-3 small">Duration (min)</th>
                    </tr></thead>
                    <tbody id="allLogsTbody" class="small"></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-3">
            <div class="d-flex flex-column align-items-center gap-1" id="allLogsPager"></div>
        </div>
    </div>
    <?php return ob_get_clean();
}
// 1. Update the function signature
function renderUsersTab(array $topByCheckins, array $topByDuration): string
{
    $flatCheckins = [];
    foreach ($topByCheckins as $cls => $users) {
        foreach ($users as $user) {
            $flatCheckins[] = [
                'display_label' => $user['display_label'],
                'college'       => $user['college'] ?: '—',
                'course'        => $user['course']  ?: '—',
                'type'          => $cls,
                'library'       => $user['library'] ?? '—',
                'count'         => $user['count'],
                'last_checkin'  => date('M j', strtotime($user['last_checkin'])),
            ];
        }
    }
    usort($flatCheckins, fn($a, $b) => $b['count'] <=> $a['count']);

    $flatDuration = [];
    foreach ($topByDuration as $cls => $users) {
        foreach ($users as $user) {
            $flatDuration[] = [
                'display_label' => $user['display_label'],
                'college'       => $user['college'] ?: '—',
                'type'          => $cls,
                'minutes'       => (int)round($user['minutes']),
            ];
        }
    }
    usort($flatDuration, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

    ob_start(); ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <p class="fw-semibold small mb-0">Top Visitors by Check-ins</p>
                                <p class="text-muted mb-0" style="font-size:.72rem;">Most frequent visitors this period</p>
                            </div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2">Top 3</span>
                        </div>
                        <div class="card-body px-3 pt-3 pb-2">
                            <div style="height:180px;position:relative;"><canvas id="chartTopUserCheckins"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <p class="fw-semibold small mb-0">Top Visitors by Duration</p>
                                <p class="text-muted mb-0" style="font-size:.72rem;">Longest cumulative time in library</p>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2">Top 3</span>
                        </div>
                        <div class="card-body px-3 pt-3 pb-2">
                            <div style="height:180px;position:relative;"><canvas id="chartTopUserDuration"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Visitor Type</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Breakdown by classification</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center px-3">
                    <div style="height:320px;width:100%;position:relative;"><canvas id="chartVisitorTypeDonut"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="fw-semibold small">Check-in Details</span>
                    <button class="btn btn-sm btn-outline-primary py-0 px-2 view-all-btn" data-tab="users" style="font-size:.75rem;">
                        <i class="bi bi-arrow-up-right-square me-1"></i>View All
                    </button>
                </div>
                <div class="card-body p-0" style="min-height:175px;"
                     id="checkinDetailsCard"
                     data-rows="<?= htmlspecialchars(json_encode($flatCheckins), ENT_QUOTES) ?>"
                     data-page="1" data-per-page="3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 small">ID Number</th>
                                    <th class="small">College</th>
                                    <th class="small">Course</th>
                                    <th class="small">Type</th>
                                    <th class="small">Section</th>
                                    <th class="text-end small">Check-ins</th>
                                    <th class="text-end pe-3 small">Last Visit</th>
                                </tr>
                            </thead>
                            <tbody id="checkinDetailsTbody" class="small"></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2 px-3">
                    <div class="d-flex flex-column align-items-center gap-1" id="checkinDetailsPager"></div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <span class="fw-semibold small">Duration Details</span>
                </div>
                <div class="card-body p-0" style="min-height:175px;"
                     id="durationDetailsCard"
                     data-rows="<?= htmlspecialchars(json_encode($flatDuration), ENT_QUOTES) ?>"
                     data-page="1" data-per-page="3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 small">ID Number</th>
                                    <th class="small">College</th>
                                    <th class="small">Type</th>
                                    <th class="text-end pe-3 small">Minutes</th>
                                </tr>
                            </thead>
                            <tbody id="durationDetailsTbody" class="small"></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2 px-3">
                    <div class="d-flex flex-column align-items-center gap-1" id="durationDetailsPager"></div>
                </div>
            </div>
        </div>
		

    </div>
    <?php return ob_get_clean();
}

function renderCollegesTab(array $topByCheckins, array $topByDuration): string
{
    ob_start(); ?>

	
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Colleges — Check-ins</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Unique visitors per college</p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="chartCollegeCheckin"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>College</th><th class="text-end">Visitors</th><th class="text-end">Last Visit</th></tr></thead>
                        <tbody>
                        <?php foreach ($topByCheckins as $collegeName => $data): ?>
                            <tr>
                                <td class="fw-semibold"><?= safe($collegeName) ?></td>
                                <td class="text-end fw-semibold text-primary"><?= $data['count'] ?></td>
                                <td class="text-end text-muted"><?= date('M j, Y', strtotime($data['last_checkin'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Colleges — Duration</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Total session time per college</p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="chartCollegeDuration"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>College</th><th class="text-end">Duration (min)</th><th class="text-end">Last Visit</th></tr></thead>
                        <tbody>
                        <?php foreach ($topByDuration as $collegeName => $data): ?>
                            <tr>
                                <td class="fw-semibold"><?= safe($collegeName) ?></td>
                                <td class="text-end fw-semibold text-success"><?= round($data['minutes']) ?></td>
                                <td class="text-end text-muted"><?= date('M j, Y', strtotime($data['last_checkin'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary view-all-btn" data-tab="colleges" style="font-size:.75rem;">View All Colleges</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}

function renderCoursesTab(array $topByCheckins, array $topByDuration): string
{
    $flatCheckins = [];
    foreach ($topByCheckins as $college => $courses) {
        foreach ($courses as $course => $data) {
            $flatCheckins[] = ['college' => $college, 'course' => $course, 'count' => $data['count'], 'last_checkin' => $data['last_checkin']];
        }
    }
    usort($flatCheckins, fn($a, $b) => $b['count'] <=> $a['count']);

    $flatDuration = [];
    foreach ($topByDuration as $college => $courses) {
        foreach ($courses as $course => $data) {
            $flatDuration[] = ['college' => $college, 'course' => $course, 'minutes' => $data['minutes'], 'last_checkin' => $data['last_checkin']];
        }
    }
    usort($flatDuration, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

    ob_start(); ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Courses — Check-ins</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Unique visitors per course</p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="chartCoursesCheckin"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>College</th><th>Course</th><th class="text-end">Visitors</th><th class="text-end">Last Visit</th></tr></thead>
                        <tbody>
                        <?php foreach ($flatCheckins as $row): ?>
                            <tr>
                                <td class="text-muted"><?= safe($row['college']) ?></td>
                                <td class="fw-semibold"><?= safe($row['course']) ?></td>
                                <td class="text-end"><?= $row['count'] ?></td>
                                <td class="text-end text-muted"><?= $row['last_checkin'] ? date('M j', strtotime($row['last_checkin'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($flatCheckins)): ?><tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Courses — Duration</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Total session time per course</p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="chartCoursesDuration"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>College</th><th>Course</th><th class="text-end">Duration (min)</th><th class="text-end">Last Visit</th></tr></thead>
                        <tbody>
                        <?php foreach ($flatDuration as $row): ?>
                            <tr>
                                <td class="text-muted"><?= safe($row['college']) ?></td>
                                <td class="fw-semibold"><?= safe($row['course']) ?></td>
                                <td class="text-end"><?= round($row['minutes']) ?></td>
                                <td class="text-end text-muted"><?= $row['last_checkin'] ? date('M j', strtotime($row['last_checkin'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($flatDuration)): ?><tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary view-all-btn" data-tab="courses" style="font-size:.75rem;">View All Courses</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}

function renderDemographicsTab(array $countBySex, int $total): string
{
    $male      = $countBySex['Male']    ?? 0;
    $female    = $countBySex['Female']  ?? 0;
    $unkn      = $countBySex['Unknown'] ?? 0;
    $malePct   = $total ? round($male   / $total * 100, 1) : 0;
    $femalePct = $total ? round($female / $total * 100, 1) : 0;

    ob_start(); ?>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Sex Distribution</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Visitor breakdown by sex</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center px-3">
                    <div style="height:300px;width:100%;position:relative;"><canvas id="chartSexDonut"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-primary-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi bi-people-fill text-primary"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Total Visitors</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-info-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi bi-gender-male text-info"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Male</p>
                                <h4 class="fw-bold mb-0"><?= number_format($male) ?></h4>
                                <small class="text-muted"><?= $malePct ?>% of total</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-danger-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi bi-gender-female text-danger"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Female</p>
                                <h4 class="fw-bold mb-0"><?= number_format($female) ?></h4>
                                <small class="text-muted"><?= $femalePct ?>% of total</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($unkn > 0): ?>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi bi-question-circle text-secondary"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Unknown</p>
                                <h4 class="fw-bold mb-0"><?= number_format($unkn) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12 text-end">
            <button class="btn btn-sm btn-outline-secondary view-all-btn" data-tab="demographics" style="font-size:.75rem;">View All Logs</button>
        </div>
    </div>
    <?php return ob_get_clean();
}

// ── REQUEST BOOTSTRAP ────────────────────────────────────────────────────────

$requestedAction  = $_POST['action'] ?? 'tab';
$requestedTab     = $_POST['tab']    ?? 'users';
$requestedPage    = max(1, (int)($_POST['page'] ?? 1));
$rowsPerPage      = 10;
$paginationOffset = ($requestedPage - 1) * $rowsPerPage;

$validTabs = ['logs', 'users', 'colleges', 'courses', 'demographics'];
if (!in_array($requestedTab, $validTabs)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid tab.']);
    exit;
}

[$where, $params] = buildWhereClauseFromFilters($_POST);
$logs = fetchFilteredVisitLogs($where, $params);

switch ($requestedAction) {

    case 'viewAll':
        $pageData = match($requestedTab) {
			'logs'         => buildViewAllLogs($logs, $paginationOffset, $rowsPerPage),
            'users'        => buildViewAllUsers($logs, $paginationOffset, $rowsPerPage),
            'colleges'     => buildViewAllColleges($logs, $paginationOffset, $rowsPerPage),
            'courses'      => buildViewAllCourses($logs, $paginationOffset, $rowsPerPage),
            'demographics' => buildViewAllDemographics($logs, $paginationOffset, $rowsPerPage),
            default        => ['rows' => [], 'total' => 0],
        };
        $totalRecords  = $pageData['total'];
        $totalPages    = $totalRecords > 0 ? (int)ceil($totalRecords / $rowsPerPage) : 1;
        $requestedPage = min($requestedPage, $totalPages);
        echo json_encode([
            'status'     => 'success',
            'tableHtml'  => renderModalTable($requestedTab, $pageData['rows']),
            'pagination' => renderModalPagination($totalPages, $requestedPage, $totalRecords, $rowsPerPage),
            'total'      => $totalRecords,
            'totalPages' => $totalPages,
            'page'       => $requestedPage,
        ]);
        break;

// ── tab case ─────────────────────────────────────────────────
case 'tab':
default:
    $kpis        = computeDashboardKpis($logs, $_POST['endDate'] ?? '');
    $uData       = aggregateTopUsersByClassification($logs);
    $clsDist     = aggregateClassificationDistribution($logs);
    $colData     = aggregateTopColleges($logs);
    $crsData     = aggregateTopCoursesByCollege($logs);
    $sexData     = aggregateSexDistribution($logs);
    $kpi3        = buildKpiTop3($logs);

    // Build flat log list (users tab only — skip the work on other tabs)
// 2. In the tab case — build $allLogsFlat BEFORE the match, then pass it in
$allLogsFlat = array_map(fn($log) => [
    'id_number'        => $log['id_number']            ?? '',
    'name'             => $log['name']                 ?? '',
    'college'          => $log['college']              ?? '',
    'course'           => $log['course']               ?? '',
    'classification'   => $log['classification']       ?? '',
    'library'          => $log['library_section_name'] ?? '',
    'sex'              => $log['sex']                  ?? '',
    'checkin_time'     => $log['checkin_time']         ?? '',
    'checkout_time'    => $log['checkout_time']        ?? '',
    'duration_minutes' => calcDurationMinutes($log['checkin_time'], $log['checkout_time'] ?? null),
], $logs);

$html = match($requestedTab) {
	'logs'         => renderLogsTab($allLogsFlat),
    'users'        => renderUsersTab($uData['topCheckins'], $uData['topDuration']),
    'colleges'     => renderCollegesTab($colData['top3CollegesCheckin'], $colData['top3CollegesDuration']),
    'courses'      => renderCoursesTab($crsData['topCoursesCheckin'], $crsData['topCoursesDuration']),
    'demographics' => renderDemographicsTab($sexData, count($logs)),
};

    echo json_encode([
        'status'                     => 'success',
        'html'                       => $html,
        'totalVisits'                => $kpis['totalVisits'],
        'totalDuration'              => $kpis['totalDuration'],
        'avgDuration'                => $kpis['avgDuration'],
        'uniqueUsers'                => $kpis['uniqueUsers'],
        'endDateCheckins'            => $kpis['endDateCheckins'],
        'top3Students'               => $kpi3['top3Students'],
        'top3Colleges'               => $kpi3['top3Colleges'],
        'top3Courses'                => $kpi3['top3Courses'],
        'topCheckins'                => $uData['topCheckins'],
        'topDuration'                => $uData['topDuration'],
        'classificationDistribution' => $clsDist,
        'top3CollegesCheckin'        => $colData['top3CollegesCheckin'],
        'top3CollegesDuration'       => $colData['top3CollegesDuration'],
        'topCoursesCheckin'          => $crsData['topCoursesCheckin'],
        'topCoursesDuration'         => $crsData['topCoursesDuration'],
        'sexDistribution'            => $sexData,
        'allLogs'                    => $allLogsFlat,   // ← now correct
    ]);
    break;
}
?>