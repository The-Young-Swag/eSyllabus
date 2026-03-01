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

function calcDurationMinutes(string $checkinTime, ?string $checkoutTime): float
{
    if (!$checkoutTime) return 0;
    return (strtotime($checkoutTime) - strtotime($checkinTime)) / 60;
}

function filterByClassification(array $logs, string $classification): array
{
    return array_filter($logs, fn($l) => strtolower($l['classification']) === strtolower($classification));
}

function excludeNonStudents(array $logs): array
{
    return array_filter($logs, fn($l) => strtolower($l['classification'] ?? '') === 'student');
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
    $upper = strtoupper($collegeName);
    foreach (COLLEGE_COLOR_MAP as $abbr => $color) {
        if (strpos($upper, strtoupper($abbr)) !== false) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

// ── FILTER & FETCH ───────────────────────────────────────────────────────────

function buildWhereClauseFromFilters(array $postData): array
{
    $where = ''; $params = [];
    if (!empty($postData['startDate'])) { $where .= " AND CAST(l.checkin_time AS DATE) >= :startDate"; $params[':startDate'] = $postData['startDate']; }
    if (!empty($postData['endDate']))   { $where .= " AND CAST(l.checkin_time AS DATE) <= :endDate";   $params[':endDate']   = $postData['endDate'];   }
    if (!empty($postData['classification']) && $postData['classification'] !== 'All') { $where .= " AND l.classification = :classification"; $params[':classification'] = $postData['classification']; }
    if (!empty($postData['library'])        && $postData['library']        !== 'All') { $where .= " AND l.library = :libraryId";            $params[':libraryId']       = $postData['library'];         }
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
    $total    = count($logs);
    $totalMin = array_sum(array_map(fn($l) => calcDurationMinutes($l['checkin_time'], $l['checkout_time']), $logs));
    $unique   = count(array_unique(array_column($logs, 'id_number')));
    $avg      = $total ? round($totalMin / $total, 1) : 0;
    $endCount = $endDate ? count(array_filter($logs, fn($l) => substr($l['checkin_time'], 0, 10) === $endDate)) : 0;
    return ['totalVisits' => $total, 'totalDuration' => round($totalMin), 'uniqueUsers' => $unique, 'avgDuration' => $avg, 'endDateCheckins' => $endCount];
}

function aggregateTopUsersByClassification(array $logs): array
{
    $topC = $topD = [];
    foreach (['Student','Employee','Guest'] as $cls) {
        $clsLogs = filterByClassification($logs, $cls);
        $counts = $durs = $meta = [];
        foreach ($clsLogs as $l) {
            $id = $l['id_number'];
            $counts[$id] = ($counts[$id] ?? 0) + 1;
            $durs[$id]   = ($durs[$id]   ?? 0) + calcDurationMinutes($l['checkin_time'], $l['checkout_time']);
            if (!isset($meta[$id])) {
                $meta[$id] = [
                    'display_label' => getUserDisplayLabel($l),
                    'name'          => $l['name'] ?? '',
                    'college'       => $l['college'] ?? '',
                    'course'        => $l['course'] ?? '',
                    'library'       => $l['library_section_name'],
                    'last_checkin'  => $l['checkin_time'],
                ];
            } elseif ($l['checkin_time'] > $meta[$id]['last_checkin']) {
                $meta[$id]['last_checkin'] = $l['checkin_time'];
            }
        }
        arsort($counts); $topC[$cls] = []; $i = 0;
        foreach ($counts as $id => $c) { if ($i >= 3) break; $topC[$cls][$id] = array_merge($meta[$id], ['count' => $c]); $i++; }
        arsort($durs); $topD[$cls] = []; $i = 0;
        foreach ($durs as $id => $m) { if ($i >= 3) break; $topD[$cls][$id] = array_merge($meta[$id], ['minutes' => $m]); $i++; }
    }
    return ['topCheckins' => $topC, 'topDuration' => $topD];
}

function aggregateClassificationDistribution(array $logs): array
{
    $out = [];
    foreach ($logs as $l) { $k = $l['classification'] ?: 'Unknown'; $out[$k] = ($out[$k] ?? 0) + 1; }
    return $out;
}

function aggregateTopColleges(array $logs): array
{
    $ng = excludeNonStudents($logs);
    $uniq = $cnt = $dur = $last = [];
    foreach ($ng as $l) {
        $c = $l['college'] ?: 'Unknown'; $id = $l['id_number'];
        if (!isset($uniq[$c][$id])) { $uniq[$c][$id] = true; $cnt[$c] = ($cnt[$c] ?? 0) + 1; }
        $dur[$c]  = ($dur[$c] ?? 0) + calcDurationMinutes($l['checkin_time'], $l['checkout_time']);
        if (!isset($last[$c]) || $l['checkin_time'] > $last[$c]) $last[$c] = $l['checkin_time'];
    }
    arsort($cnt); $topC = []; $i = 0;
    foreach ($cnt as $c => $n) { if ($i >= 3) break; $topC[$c] = ['count' => $n, 'last_checkin' => $last[$c], 'color' => resolveCollegeColor($c)]; $i++; }
    arsort($dur); $topD = []; $i = 0;
    foreach ($dur as $c => $m) { if ($i >= 3) break; $topD[$c] = ['minutes' => $m, 'last_checkin' => $last[$c], 'color' => resolveCollegeColor($c)]; $i++; }
    return ['top3CollegesCheckin' => $topC, 'top3CollegesDuration' => $topD];
}

function aggregateTopCoursesByCollege(array $logs): array
{
    $ng = excludeNonStudents($logs);
    $uniq = $cnt = $dur = $last = [];
    foreach ($ng as $l) {
        $col = $l['college'] ?: 'Unknown'; $crs = $l['course'] ?: 'Unknown'; $id = $l['id_number']; $key = "{$col}|{$crs}";
        if (!isset($uniq[$col][$crs][$id])) { $uniq[$col][$crs][$id] = true; $cnt[$col][$crs] = ($cnt[$col][$crs] ?? 0) + 1; }
        $dur[$col][$crs] = ($dur[$col][$crs] ?? 0) + calcDurationMinutes($l['checkin_time'], $l['checkout_time']);
        if (!isset($last[$key]) || $l['checkin_time'] > $last[$key]) $last[$key] = $l['checkin_time'];
    }
    $topC = $topD = [];
    foreach ($cnt as $col => $courses) { arsort($courses); $topC[$col] = []; $i = 0; foreach ($courses as $crs => $n) { if ($i >= 3) break; $topC[$col][$crs] = ['count' => $n, 'last_checkin' => $last["{$col}|{$crs}"] ?? null]; $i++; } }
    foreach ($dur as $col => $courses) { arsort($courses); $topD[$col] = []; $i = 0; foreach ($courses as $crs => $m) { if ($i >= 3) break; $topD[$col][$crs] = ['minutes' => $m, 'last_checkin' => $last["{$col}|{$crs}"] ?? null]; $i++; } }
    return ['topCoursesCheckin' => $topC, 'topCoursesDuration' => $topD];
}

function aggregateSexDistribution(array $logs): array
{
    $out = [];
    foreach ($logs as $l) { $k = $l['sex'] ?: 'Unknown'; $out[$k] = ($out[$k] ?? 0) + 1; }
    return $out;
}

function aggregateCollegeDistribution(array $logs): array
{
    $ng = excludeNonStudents($logs); $uniq = $cnt = [];
    foreach ($ng as $l) { $c = $l['college'] ?: 'Unknown'; $id = $l['id_number']; if (!isset($uniq[$c][$id])) { $uniq[$c][$id] = true; $cnt[$c] = ($cnt[$c] ?? 0) + 1; } }
    arsort($cnt); $out = [];
    foreach ($cnt as $c => $n) $out[$c] = ['count' => $n, 'color' => resolveCollegeColor($c)];
    return $out;
}

// ── VIEW ALL BUILDERS ────────────────────────────────────────────────────────

function buildViewAllUsers(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach ($logs as $l) {
        $id = $l['id_number'];
        if (!isset($agg[$id])) {
            $agg[$id] = [
                'display_label' => getUserDisplayLabel($l),
                'name'          => $l['name'] ?? '',
                'college'       => $l['college'] ?? '',
                'course'        => $l['course'] ?? '',
                'type'          => $l['classification'],
                'library'       => $l['library_section_name'],
                'checkins'      => 0,
                'duration'      => 0,
                'last_checkin'  => $l['checkin_time'],
            ];
        }
        $agg[$id]['checkins']++;
        $agg[$id]['duration'] += calcDurationMinutes($l['checkin_time'], $l['checkout_time']);
        if ($l['checkin_time'] > $agg[$id]['last_checkin']) $agg[$id]['last_checkin'] = $l['checkin_time'];
    }
    uasort($agg, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_values(array_slice($agg, $offset, $limit, true)), 'total' => count($agg)];
}

function buildViewAllColleges(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach (excludeNonStudents($logs) as $l) {
        $c = $l['college'] ?: 'Unknown';
        if (!isset($agg[$c])) $agg[$c] = ['college_name' => $c, 'unique_visitors' => [], 'duration' => 0, 'last_checkin' => $l['checkin_time']];
        $agg[$c]['unique_visitors'][$l['id_number']] = true;
        $agg[$c]['duration'] += calcDurationMinutes($l['checkin_time'], $l['checkout_time']);
        if ($l['checkin_time'] > $agg[$c]['last_checkin']) $agg[$c]['last_checkin'] = $l['checkin_time'];
    }
    $rows = [];
    foreach ($agg as $d) $rows[] = ['name' => $d['college_name'], 'checkins' => count($d['unique_visitors']), 'duration' => $d['duration'], 'last_checkin' => $d['last_checkin']];
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllCourses(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach (excludeNonStudents($logs) as $l) {
        $k = ($l['college'] ?: 'Unknown') . '|' . ($l['course'] ?: 'Unknown');
        if (!isset($agg[$k])) $agg[$k] = ['college' => $l['college'] ?: 'Unknown', 'course' => $l['course'] ?: 'Unknown', 'unique_visitors' => [], 'duration' => 0, 'last_checkin' => $l['checkin_time']];
        $agg[$k]['unique_visitors'][$l['id_number']] = true;
        $agg[$k]['duration'] += calcDurationMinutes($l['checkin_time'], $l['checkout_time']);
        if ($l['checkin_time'] > $agg[$k]['last_checkin']) $agg[$k]['last_checkin'] = $l['checkin_time'];
    }
    $rows = [];
    foreach ($agg as $d) $rows[] = ['college' => $d['college'], 'course' => $d['course'], 'checkins' => count($d['unique_visitors']), 'duration' => $d['duration'], 'last_checkin' => $d['last_checkin']];
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllDemographics(array $logs, int $offset, int $limit): array
{
    $rows = array_map(fn($l) => [
        'display_label' => getUserDisplayLabel($l),
        'sex'           => $l['sex'],
        'checkin'       => $l['checkin_time'],
        'checkout'      => $l['checkout_time'],
        'duration'      => calcDurationMinutes($l['checkin_time'], $l['checkout_time']),
    ], $logs);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

// ── MODAL TABLE & PAGINATION ─────────────────────────────────────────────────

function renderModalTable(string $tab, array $rows): string
{
    $cols = [
        'users' => [
            'headers' => ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($r) =>
                '<td class="ps-3 fw-semibold">'   . safe($r['display_label'])  . '</td>' .
                '<td class="text-muted small">'    . safe($r['name'])           . '</td>' .
                '<td class="text-muted small">'    . safe($r['college'] ?: '—') . '</td>' .
                '<td class="text-muted small">'    . safe($r['course']  ?: '—') . '</td>' .
                '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">' . safe($r['type']) . '</span></td>' .
                '<td class="text-muted small">'    . safe($r['library'] ?? '—') . '</td>' .
                '<td class="text-end fw-semibold text-primary">' . (int)$r['checkins'] . '</td>' .
                '<td class="text-end">'            . (int)round($r['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . formatDateTime($r['last_checkin']) . '</td>',
        ],
        'colleges' => [
            'headers' => ['College', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($r) =>
                '<td class="ps-3 fw-semibold">' . safe($r['name']) . '</td>' .
                '<td class="text-end">'         . (int)$r['checkins'] . '</td>' .
                '<td class="text-end">'         . (int)round($r['duration']) . '</td>' .
                '<td class="text-muted small pe-3">' . formatDateTime($r['last_checkin']) . '</td>',
        ],
        'courses' => [
            'headers' => ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            'rowFn'   => fn($r) =>
                '<td class="ps-3 text-muted small">' . safe($r['college']) . '</td>' .
                '<td class="fw-semibold">'            . safe($r['course'])  . '</td>' .
                '<td class="text-end">'               . (int)$r['checkins'] . '</td>' .
                '<td class="text-end">'               . (int)round($r['duration']) . '</td>' .
                '<td class="text-muted small pe-3">'  . formatDateTime($r['last_checkin']) . '</td>',
        ],
        'demographics' => [
            'headers' => ['ID Number', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
            'rowFn'   => fn($r) =>
                '<td class="ps-3 fw-semibold">' . safe($r['display_label']) . '</td>' .
                '<td>'                           . safe($r['sex'])           . '</td>' .
                '<td class="text-muted small">'  . formatDateTime($r['checkin']) . '</td>' .
                '<td class="text-muted small">'  . ($r['checkout'] ? formatDateTime($r['checkout']) : '—') . '</td>' .
                '<td class="text-end pe-3">'     . (int)round($r['duration']) . '</td>',
        ],
    ];
    if (!isset($cols[$tab])) return '';
    $cfg   = $cols[$tab];
    $heads = implode('', array_map(fn($h) => "<th class=\"small fw-semibold\">{$h}</th>", $cfg['headers']));
    $body  = implode('', array_map(fn($r) => '<tr>' . ($cfg['rowFn'])($r) . '</tr>', $rows));
    return "<div class=\"table-responsive\"><table class=\"table table-sm table-striped table-hover align-middle mb-0\"><thead class=\"table-dark\"><tr>{$heads}</tr></thead><tbody class=\"small\">{$body}</tbody></table></div>";
}

/**
 * Renders a First / ‹ / 1 2 … N / › / Last paginator.
 * Shows at most 5 numbered page slots; collapses with ellipsis for large sets.
 */
function renderModalPagination(int $totalPages, int $current, int $totalRecords, int $rowsPerPage): string
{
    if ($totalPages <= 1) return '';

    $disabled = fn(bool $cond) => $cond ? 'disabled' : '';
    $active   = fn(int $p)     => $p === $current ? 'active' : '';

    $first = 1;
    $last  = $totalPages;
    $prev  = max(1, $current - 1);
    $next  = min($totalPages, $current + 1);

    // Sliding window of 5 pages centred on current
    $window = 5;
    $start  = max(1, min($current - intdiv($window, 2), $totalPages - $window + 1));
    $end    = min($totalPages, $start + $window - 1);

    $li = fn(string $label, int $page, string $extra = '', bool $isText = false) =>
        "<li class=\"page-item {$extra}\">".
            "<a class=\"page-link\" href=\"#\"".($isText ? '' : " data-page=\"{$page}\"").">{$label}</a>".
        "</li>";

    $items  = '';
    $items .= $li('«', $first, $disabled($current === 1));
    $items .= $li('‹', $prev,  $disabled($current === 1));

    if ($start > 1) {
        $items .= $li('1', 1, $active(1));
        if ($start > 2) $items .= $li('…', 0, 'disabled', true);
    }

    for ($p = $start; $p <= $end; $p++) {
        $items .= $li((string)$p, $p, $active($p));
    }

    if ($end < $last) {
        if ($end < $last - 1) $items .= $li('…', 0, 'disabled', true);
        $items .= $li((string)$last, $last, $active($last));
    }

    $items .= $li('›', $next, $disabled($current === $last));
    $items .= $li('»', $last, $disabled($current === $last));

    $from  = (($current - 1) * $rowsPerPage) + 1;
    $to    = min($current * $rowsPerPage, $totalRecords);
    $info  = "<small class=\"text-muted\">Showing {$from}–{$to} of {$totalRecords} records</small>";

    return "{$info}<nav class=\"mt-1\"><ul class=\"pagination pagination-sm mb-0 flex-wrap justify-content-center\">{$items}</ul></nav>";
}

// ── TAB HTML RENDERERS ───────────────────────────────────────────────────────

function renderUsersTab(array $topByCheckins, array $topByDuration): string
{
    ob_start(); ?>
    <div class="row g-4">

        <!-- LEFT: bar charts stacked -->
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
                            <div style="height:180px;position:relative;">
                                <canvas id="chartTopUserCheckins"></canvas>
                            </div>
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
                            <div style="height:180px;position:relative;">
                                <canvas id="chartTopUserDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- RIGHT: visitor-type donut -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Visitor Type</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Breakdown by classification</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center px-3">
                    <div style="height:320px;width:100%;position:relative;">
                        <canvas id="chartVisitorTypeDonut"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM: detail tables -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="fw-semibold small">Check-in Details</span>
                    <button class="btn btn-sm btn-outline-primary py-0 px-2 view-all-btn" data-tab="users" style="font-size:.75rem;">
                        <i class="bi bi-arrow-up-right-square me-1"></i>View All
                    </button>
                </div>
                <div class="card-body p-0">
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
                            <tbody class="small">
                            <?php foreach ($topByCheckins as $cls => $users): ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold"><?= safe($u['display_label']) ?></td>
                                    <td class="text-muted"><?= safe($u['college'] ?: '—') ?></td>
                                    <td class="text-muted"><?= safe($u['course'] ?: '—') ?></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;"><?= safe($cls) ?></span></td>
                                    <td class="text-muted"><?= safe($u['library'] ?? '—') ?></td>
                                    <td class="text-end fw-semibold text-primary"><?= number_format($u['count']) ?></td>
                                    <td class="text-end text-muted pe-3"><?= date('M j', strtotime($u['last_checkin'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <span class="fw-semibold small">Duration Details</span>
                </div>
                <div class="card-body p-0">
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
                            <tbody class="small">
                            <?php foreach ($topByDuration as $cls => $users): ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold"><?= safe($u['display_label']) ?></td>
                                    <td class="text-muted"><?= safe($u['college'] ?: '—') ?></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;"><?= safe($cls) ?></span></td>
                                    <td class="text-end fw-semibold text-success pe-3"><?= number_format(round($u['minutes'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                    <div style="height:260px;position:relative;">
                        <canvas id="chartCollegeCheckin"></canvas>
                    </div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>College</th><th class="text-end">Visitors</th><th class="text-end">Last Visit</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topByCheckins as $name => $d): ?>
                            <tr>
                                <td class="fw-semibold"><?= safe($name) ?></td>
                                <td class="text-end fw-semibold text-primary"><?= $d['count'] ?></td>
                                <td class="text-end text-muted"><?= date('M j, Y', strtotime($d['last_checkin'])) ?></td>
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
                    <div style="height:260px;position:relative;">
                        <canvas id="chartCollegeDuration"></canvas>
                    </div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>College</th><th class="text-end">Duration (min)</th><th class="text-end">Last Visit</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topByDuration as $name => $d): ?>
                            <tr>
                                <td class="fw-semibold"><?= safe($name) ?></td>
                                <td class="text-end fw-semibold text-success"><?= round($d['minutes']) ?></td>
                                <td class="text-end text-muted"><?= date('M j, Y', strtotime($d['last_checkin'])) ?></td>
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
    $allC = $allD = [];
    foreach ($topByCheckins as $col => $courses) foreach ($courses as $crs => $d) $allC[] = ['college' => $col, 'course' => $crs, 'count' => $d['count'], 'last_checkin' => $d['last_checkin']];
    usort($allC, fn($a, $b) => $b['count'] <=> $a['count']);
    foreach ($topByDuration as $col => $courses) foreach ($courses as $crs => $d) $allD[] = ['college' => $col, 'course' => $crs, 'minutes' => $d['minutes'], 'last_checkin' => $d['last_checkin']];
    usort($allD, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

    ob_start(); ?>
    <div class="row g-4">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Courses — Check-ins</p>
                    <p class="text-muted mb-0" style="font-size:.72rem;">Unique visitors per course</p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;">
                        <canvas id="chartCoursesCheckin"></canvas>
                    </div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>College</th><th>Course</th><th class="text-end">Visitors</th><th class="text-end">Last Visit</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allC as $r): ?>
                            <tr>
                                <td class="text-muted"><?= safe($r['college']) ?></td>
                                <td class="fw-semibold"><?= safe($r['course']) ?></td>
                                <td class="text-end"><?= $r['count'] ?></td>
                                <td class="text-end text-muted"><?= $r['last_checkin'] ? date('M j', strtotime($r['last_checkin'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($allC)): ?><tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
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
                    <div style="height:260px;position:relative;">
                        <canvas id="chartCoursesDuration"></canvas>
                    </div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>College</th><th>Course</th><th class="text-end">Duration (min)</th><th class="text-end">Last Visit</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allD as $r): ?>
                            <tr>
                                <td class="text-muted"><?= safe($r['college']) ?></td>
                                <td class="fw-semibold"><?= safe($r['course']) ?></td>
                                <td class="text-end"><?= round($r['minutes']) ?></td>
                                <td class="text-end text-muted"><?= $r['last_checkin'] ? date('M j', strtotime($r['last_checkin'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($allD)): ?><tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
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
                    <div style="height:300px;width:100%;position:relative;">
                        <canvas id="chartSexDonut"></canvas>
                    </div>
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

$validTabs = ['users', 'colleges', 'courses', 'demographics'];
if (!in_array($requestedTab, $validTabs)) { echo json_encode(['status' => 'error', 'message' => 'Invalid tab.']); exit; }

[$where, $params] = buildWhereClauseFromFilters($_POST);
$logs = fetchFilteredVisitLogs($where, $params);

switch ($requestedAction) {

    case 'viewAll':
        $pageData = match($requestedTab) {
            'users'        => buildViewAllUsers($logs, $paginationOffset, $rowsPerPage),
            'colleges'     => buildViewAllColleges($logs, $paginationOffset, $rowsPerPage),
            'courses'      => buildViewAllCourses($logs, $paginationOffset, $rowsPerPage),
            'demographics' => buildViewAllDemographics($logs, $paginationOffset, $rowsPerPage),
            default        => ['rows' => [], 'total' => 0],
        };
        $totalRecords = $pageData['total'];
        $totalPages   = $totalRecords > 0 ? (int)ceil($totalRecords / $rowsPerPage) : 1;
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

    case 'tab':
    default:
        $kpis    = computeDashboardKpis($logs, $_POST['endDate'] ?? '');
        $uData   = aggregateTopUsersByClassification($logs);
        $clsDist = aggregateClassificationDistribution($logs);
        $colData = aggregateTopColleges($logs);
        $colDist = aggregateCollegeDistribution($logs);
        $crsData = aggregateTopCoursesByCollege($logs);
        $sexData = aggregateSexDistribution($logs);

        $html = match($requestedTab) {
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
            'topCheckins'                => $uData['topCheckins'],
            'topDuration'                => $uData['topDuration'],
            'classificationDistribution' => $clsDist,
            'top3CollegesCheckin'        => $colData['top3CollegesCheckin'],
            'top3CollegesDuration'       => $colData['top3CollegesDuration'],
            'collegeDistribution'        => $colDist,
            'topCoursesCheckin'          => $crsData['topCoursesCheckin'],
            'topCoursesDuration'         => $crsData['topCoursesDuration'],
            'sexDistribution'            => $sexData,
        ]);
        break;
}
?>