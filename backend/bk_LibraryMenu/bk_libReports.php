<?php
/**
 * Library Analytics — Backend Handler
 */

include '../../db/dbconnection.php';
header('Content-Type: application/json');

// ── CONSTANTS ────────────────────────────────────────────────────────────────

const USER_DISPLAY_FIELD   = 'id_number';
const COLLEGE_COLOR_FALLBACK = 'rgba(139,92,246,0.88)';
const COLLEGE_COLOR_MAP    = [
    'CAF' => 'rgba(22,163,74,0.88)',
    'CAS' => 'rgba(234,88,12,0.88)',
    'CBM' => 'rgba(202,138,4,0.88)',
    'CET' => 'rgba(220,38,38,0.88)',
    'CED' => 'rgba(37,99,235,0.88)',
    'CVM' => 'rgba(107,114,128,0.88)',
];

// ── UTILITIES ────────────────────────────────────────────────────────────────

function calcDurationMinutes(string $checkin, ?string $checkout): float
{
    return $checkout ? (strtotime($checkout) - strtotime($checkin)) / 60 : 0;
}

function filterByClassification(array $logs, string $cls): array
{
    return array_filter($logs, fn($l) => strcasecmp($l['classification'], $cls) === 0);
}

function excludeNonStudents(array $logs): array
{
    return array_filter($logs, fn($l) => strcasecmp($l['classification'] ?? '', 'student') === 0);
}

function formatDateTime(string $dt): string
{
    return date('M j, Y g:i A', strtotime($dt));
}

function safe(mixed $v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function getUserDisplayLabel(array $log): string
{
    $id = $log['id_number'] ?? '';
    if ($id === '0' || $id === '') {
        return $log['name'] ?? 'Guest';
    }
    return $id;
}

function resolveCollegeColor(string $name): string
{
    $upper = strtoupper($name);
    foreach (COLLEGE_COLOR_MAP as $abbr => $color) {
        if (str_contains($upper, $abbr)) return $color;
    }
    return COLLEGE_COLOR_FALLBACK;
}

// ── FILTER & FETCH ────────────────────────────────────────────────────────────

function buildWhereClause(array $post): array {
    $where = '';
    $params = [];

    !empty($post['startDate']) && ($where .= ' AND CAST(l.checkin_time AS DATE) >= :startDate') && $params[':startDate'] = $post['startDate'];
    !empty($post['endDate'])   && ($where .= ' AND CAST(l.checkin_time AS DATE) <= :endDate') && $params[':endDate'] = $post['endDate'];
    !empty($post['classification']) && $post['classification'] !== 'All' && ($where .= ' AND l.classification = :classification') && $params[':classification'] = $post['classification'];
    !empty($post['library']) && $post['library'] !== 'All' && ($where .= ' AND l.library = :libraryId') && $params[':libraryId'] = $post['library'];

    return [$where, $params];
}

function fetchVisitLogs(string $where, array $params): array
{
    return execsqlSRS("
        SELECT l.id,
               l.id_number,
               l.name,
               l.college,
               l.course,
               l.library               AS library_section_id,
               s.SectionName           AS library_section_name,
               l.checkin_time,
               l.checkout_time,
               l.sex,
               l.classification,
               l.agency_organization
        FROM   Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE  1=1 {$where}
        ORDER  BY l.checkin_time DESC
    ", 'Select', $params);
}

// ── KPIs ─────────────────────────────────────────────────────────────────────

function computeKpis(array $logs, string $endDate): array
{
    $total   = count($logs);
    $minutes = array_sum(array_map(
        fn($l) => calcDurationMinutes($l['checkin_time'], $l['checkout_time']),
        $logs
    ));

    return [
        'totalVisits'     => $total,
        'totalDuration'   => round($minutes),
        'uniqueUsers'     => count(array_unique(array_column($logs, 'id_number'))),
        'avgDuration'     => $total ? round($minutes / $total, 1) : 0,
        'endDateCheckins' => $endDate
            ? count(array_filter($logs, fn($l) => substr($l['checkin_time'], 0, 10) === $endDate))
            : 0,
    ];
}

// ── AGGREGATIONS ─────────────────────────────────────────────────────────────

function aggregateTopUsers(array $logs): array{
    $topCheckins=$topDuration=[];
    foreach(['Student','Employee','Guest'] as $cls){
        $clsLogs=filterByClassification($logs,$cls);
        $counts=$durs=$meta=[];
        foreach($clsLogs as $log){
            $uid=$log['id_number'];
            $counts[$uid]=($counts[$uid]??0)+1;
            $durs[$uid]=($durs[$uid]??0)+calcDurationMinutes($log['checkin_time'],$log['checkout_time']);
            if(!isset($meta[$uid])){
                $meta[$uid]=[
                    'display_label'=>getUserDisplayLabel($log),
                    'name'=>$log['name']??'',
                    'college'=>$log['college']??'',
                    'course'=>$log['course']??'',
                    'library'=>$log['library_section_name']??'—',
                    'agency_organization'=>$log['agency_organization']??'—',
                    'last_checkin'=>$log['checkin_time'],
                ];
            }elseif($log['checkin_time']>$meta[$uid]['last_checkin']){
                $meta[$uid]['last_checkin']=$log['checkin_time'];
            }
        }
        $topCheckins[$cls]=topN($counts,$meta,'count',3);
        $topDuration[$cls]=topN($durs,$meta,'minutes',3);
    }
    return ['topCheckins'=>$topCheckins,'topDuration'=>$topDuration];
}

function topN(array $values,array $meta,string $valueKey,int $n): array{
    uksort($values,fn($a,$b)=>$values[$b]<=>$values[$a]?:strcmp($a,$b));
    $out=[];
    foreach($values as $uid=>$val){
        if(count($out)>=$n) break;
        $out[$uid]=array_merge($meta[$uid],[$valueKey=>$val]);
    }
    return $out;
}

function aggregateClassification(array $logs): array{
    $out=[];
    foreach($logs as $l){
        $key=$l['classification']?:'Unknown';
        $out[$key]=($out[$key]??0)+1;
    }
    return $out;
}

function aggregateTopColleges(array $logs): array{
    $uniq=$cnt=$dur=$last=[];
    foreach(excludeNonStudents($logs) as $log){
        $college=$log['college']?:'Unknown';
        $sid=$log['id_number'];
        if(!isset($uniq[$college][$sid])){
            $uniq[$college][$sid]=true;
            $cnt[$college]=($cnt[$college]??0)+1;
        }
        $dur[$college]=($dur[$college]??0)+calcDurationMinutes($log['checkin_time'],$log['checkout_time']);
        if(!isset($last[$college])||$log['checkin_time']>$last[$college]) $last[$college]=$log['checkin_time'];
    }
    $mapFn=fn($val,$key)=>['count'=>$val,'last_checkin'=>$last[$key],'color'=>resolveCollegeColor($key)];
    $durFn=fn($val,$key)=>['minutes'=>$val,'last_checkin'=>$last[$key],'color'=>resolveCollegeColor($key)];
    return ['top3CollegesCheckin'=>buildTop3Map($cnt,$mapFn),'top3CollegesDuration'=>buildTop3Map($dur,$durFn)];
}

function aggregateTopCourses(array $logs): array{
    $uniq=$cnt=$dur=$last=[];
    foreach(excludeNonStudents($logs) as $log){
        $college=$log['college']?:'Unknown';
        $course=$log['course']?:'Unknown';
        $sid=$log['id_number'];
        $key="{$college}|{$course}";
        if(!isset($uniq[$college][$course][$sid])){
            $uniq[$college][$course][$sid]=true;
            $cnt[$college][$course]=($cnt[$college][$course]??0)+1;
        }
        $dur[$college][$course]=($dur[$college][$course]??0)+calcDurationMinutes($log['checkin_time'],$log['checkout_time']);
        if(!isset($last[$key])||$log['checkin_time']>$last[$key]) $last[$key]=$log['checkin_time'];
    }
    $topCheckins=$topDuration=[];
    foreach($cnt as $college=>$courses){
        uksort($courses,fn($a,$b)=>$courses[$b]<=>$courses[$a]?:strcmp($a,$b));
        $topCheckins[$college]=[];
        foreach(array_slice($courses,0,3,true) as $course=>$total) $topCheckins[$college][$course]=['count'=>$total,'last_checkin'=>$last["{$college}|{$course}"]??null];
    }
    foreach($dur as $college=>$courses){
        uksort($courses,fn($a,$b)=>$dur[$college][$b]<=>$dur[$college][$a]?:strcmp($a,$b));
        $topDuration[$college]=[];
        foreach(array_slice($courses,0,3,true) as $course=>$minutes) $topDuration[$college][$course]=['minutes'=>$minutes,'last_checkin'=>$last["{$college}|{$course}"]??null];
    }
    return ['topCoursesCheckin'=>$topCheckins,'topCoursesDuration'=>$topDuration];
}

function aggregateSex(array $logs): array{
    $out=[];
    foreach($logs as $l){
        $key=$l['sex']?:'Unknown';
        $out[$key]=($out[$key]??0)+1;
    }
    return $out;
}

function aggregateCollegeDistribution(array $logs): array{
    $uniq=$cnt=[];
    foreach(excludeNonStudents($logs) as $log){
        $college=$log['college']?:'Unknown';
        $sid=$log['id_number'];
        if(!isset($uniq[$college][$sid])){
            $uniq[$college][$sid]=true;
            $cnt[$college]=($cnt[$college]??0)+1;
        }
    }
    uksort($cnt,fn($a,$b)=>$cnt[$b]<=>$cnt[$a]?:strcmp($a,$b));
    $out=[];
    foreach($cnt as $college=>$total) $out[$college]=['count'=>$total,'color'=>resolveCollegeColor($college)];
    return $out;
}

function buildTop3Map(array $values,callable $mapFn): array{
    uksort($values,fn($a,$b)=>$values[$b]<=>$values[$a]?:strcmp($a,$b));
    $out=[];
    foreach($values as $key=>$val){if(count($out)>=3) break;$out[$key]=$mapFn($val,$key);}
    return $out;
}
// ── RANK ANNOTATION ──────────────────────────────────────────────────────────

function annotateRanks(array $items, string $valueKey): array
{
    $firstRank = $tieCount = [];
    foreach ($items as $i => $item) {
        $v = $item[$valueKey];
        $firstRank[$v]  ??= $i + 1;
        $tieCount[$v]     = ($tieCount[$v] ?? 0) + 1;
    }
    return array_map(function ($item) use ($firstRank, $tieCount, $valueKey) {
        $v = $item[$valueKey];
        return $item + ['rank' => $firstRank[$v], 'tied' => $tieCount[$v] > 1, 'tiedCount' => $tieCount[$v]];
    }, $items);
}
// ── KPI TOP-3 BUILDER ─────────────────────────────────────────────────────────
function buildKpiTop3(array $logs): array {
    $studentLogs=array_filter($logs, fn($l)=>strcasecmp($l['classification']??'','student')===0);

    // Top 3 Students
    $visitCount=[];$studentMeta=[];
    foreach($studentLogs as $l){
        $sid=$l['id_number'];
        $visitCount[$sid]=($visitCount[$sid]??0)+1;
        $studentMeta[$sid]??=['id_number'=>$sid,'name'=>$l['name']??'','college'=>$l['college']??'','course'=>$l['course']??''];
    }
    uksort($visitCount, fn($a,$b)=>$visitCount[$b]<=>$visitCount[$a] ?: strcmp($a,$b));
    $top3Students=[];
    foreach($visitCount as $sid=>$c){if(count($top3Students)>=3) break;$top3Students[]=$studentMeta[$sid]+['count'=>$c];}

    // Top 3 Colleges
    $seenC=[];$collegeCount=[];
    foreach($studentLogs as $l){
        $c=$l['college']?:'Unknown';$sid=$l['id_number'];
        if(!isset($seenC[$c][$sid])){$seenC[$c][$sid]=true;$collegeCount[$c]=($collegeCount[$c]??0)+1;}
    }
    uksort($collegeCount, fn($a,$b)=>$collegeCount[$b]<=>$collegeCount[$a] ?: strcmp($a,$b));
    $top3Colleges=[];
    foreach($collegeCount as $c=>$cnt){if(count($top3Colleges)>=3) break;$top3Colleges[]=['name'=>$c,'count'=>$cnt];}

    // Top 3 Courses
    $seenCrs=[];$courseCount=[];
    foreach($studentLogs as $l){
        $c=$l['college']?:'Unknown';$crs=$l['course']?:'Unknown';$sid=$l['id_number'];$key="{$c}|{$crs}";
        if(!isset($seenCrs[$key][$sid])){$seenCrs[$key][$sid]=true;$courseCount[$key]=($courseCount[$key]??0)+1;}
    }
    uksort($courseCount, fn($a,$b)=>$courseCount[$b]<=>$courseCount[$a] ?: strcmp($a,$b));
    $top3Courses=[];
    foreach($courseCount as $k=>$cnt){if(count($top3Courses)>=3) break;[$c,$crs]=explode('|',$k,2);$top3Courses[]=['college'=>$c,'course'=>$crs,'count'=>$cnt];}

    return [
        'top3Students'=>annotateRanks($top3Students,'count'),
        'top3Colleges'=>annotateRanks($top3Colleges,'count'),
        'top3Courses'=>annotateRanks($top3Courses,'count'),
    ];
}

// ── VIEW-ALL BUILDERS ─────────────────────────────────────────────────────────
function buildViewAllUsers(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach ($logs as $log) {
        $uid = $log['id_number'];
        if (!isset($agg[$uid])) {
            $agg[$uid] = [
                'display_label' => getUserDisplayLabel($log),
                'name'          => $log['name']    ?? '',
                'college'       => $log['college'] ?? '',
                'course'        => $log['course']  ?? '',
                'type'          => $log['classification'],
                'library'       => $log['library_section_name'],
                'checkins'      => 0,
                'duration'      => 0,
                'last_checkin'  => $log['checkin_time'],
            ];
        }
        $agg[$uid]['checkins']++;
        $agg[$uid]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $agg[$uid]['last_checkin']) {
            $agg[$uid]['last_checkin'] = $log['checkin_time'];
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
        $agg[$college] ??= ['college_name' => $college, 'unique_visitors' => [], 'duration' => 0, 'last_checkin' => $log['checkin_time']];
        $agg[$college]['unique_visitors'][$log['id_number']] = true;
        $agg[$college]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $agg[$college]['last_checkin']) {
            $agg[$college]['last_checkin'] = $log['checkin_time'];
        }
    }
    $rows = array_map(fn($d) => [
        'name'         => $d['college_name'],
        'checkins'     => count($d['unique_visitors']),
        'duration'     => $d['duration'],
        'last_checkin' => $d['last_checkin'],
    ], array_values($agg));
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllCourses(array $logs, int $offset, int $limit): array
{
    $agg = [];
    foreach (excludeNonStudents($logs) as $log) {
        $key = ($log['college'] ?: 'Unknown') . '|' . ($log['course'] ?: 'Unknown');
        $agg[$key] ??= [
            'college'         => $log['college'] ?: 'Unknown',
            'course'          => $log['course']  ?: 'Unknown',
            'unique_visitors' => [],
            'duration'        => 0,
            'last_checkin'    => $log['checkin_time'],
        ];
        $agg[$key]['unique_visitors'][$log['id_number']] = true;
        $agg[$key]['duration'] += calcDurationMinutes($log['checkin_time'], $log['checkout_time']);
        if ($log['checkin_time'] > $agg[$key]['last_checkin']) {
            $agg[$key]['last_checkin'] = $log['checkin_time'];
        }
    }
    $rows = array_map(fn($d) => [
        'college'      => $d['college'],
        'course'       => $d['course'],
        'checkins'     => count($d['unique_visitors']),
        'duration'     => $d['duration'],
        'last_checkin' => $d['last_checkin'],
    ], array_values($agg));
    usort($rows, fn($a, $b) => $b['checkins'] <=> $a['checkins']);
    return ['rows' => array_slice($rows, $offset, $limit), 'total' => count($rows)];
}

function buildViewAllLogs(array $logs, int $offset, int $limit): array
{
    $rows = array_map(fn($log) => [
        'id_number'           => $log['id_number']            ?? '',
        'name'                => $log['name']                 ?? '',
        'college'             => $log['college']              ?? '',
        'course'              => $log['course']               ?? '',
        'classification'      => $log['classification']       ?? '',
        'library'             => $log['library_section_name'] ?? '',
        'sex'                 => $log['sex']                  ?? '',
        'checkin_time'        => $log['checkin_time']         ?? '',
        'checkout_time'       => $log['checkout_time']        ?? null,
        'agency_organization' => $log['agency_organization']  ?? '',
        'duration'            => calcDurationMinutes($log['checkin_time'], $log['checkout_time'] ?? null),
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

// ── MODAL TABLE ───────────────────────────────────────────────────────────────

function renderModalTable(string $tab,array $rows):string{
    $cols=[
        'logs'=>[
            'headers'=>['ID Number','Name','College','Course','Type','Section','Sex','Check-in','Check-out','Agency / Organization','Duration (min)'],
            'rowFn'=>fn($r)=>
                '<td class="ps-3 fw-semibold">'.safe($r['id_number']).'</td>'.
                '<td class="text-muted small">'.safe($r['name']?:'—').'</td>'.
                '<td class="text-muted small">'.safe($r['college']?:'—').'</td>'.
                '<td class="text-muted small">'.safe($r['course']?:'—').'</td>'.
                '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">'.safe($r['classification']?:'—').'</span></td>'.
                '<td class="text-muted small">'.safe($r['library']?:'—').'</td>'.
                '<td class="text-muted small">'.safe($r['sex']?:'—').'</td>'.
                '<td class="text-muted small">'.($r['checkin_time']?formatDateTime($r['checkin_time']):'—').'</td>'.
                '<td class="text-muted small">'.($r['checkout_time']?formatDateTime($r['checkout_time']):'—').'</td>'.
                '<td class="text-muted small">'.safe($r['agency_organization']?:'—').'</td>'.
                '<td class="text-end pe-3">'.(isset($r['duration'])?(int)round($r['duration']):'—').'</td>',
        ],
        'users'=>[
            'headers'=>['ID Number','Name','College','Course','Type','Library Section','Check-ins','Duration (min)','Last Check-in'],
            'rowFn'=>fn($r)=>
                '<td class="ps-3 fw-semibold">'.safe($r['display_label']).'</td>'.
                '<td class="text-muted small">'.safe($r['name']).'</td>'.
                '<td class="text-muted small">'.safe($r['college']?:'—').'</td>'.
                '<td class="text-muted small">'.safe($r['course']?:'—').'</td>'.
                '<td><span class="badge bg-secondary-subtle text-secondary rounded-pill small">'.safe($r['type']).'</span></td>'.
                '<td class="text-muted small">'.safe($r['library']??'—').'</td>'.
                '<td class="text-end fw-semibold text-primary">'.(int)$r['checkins'].'</td>'.
                '<td class="text-end">'.(int)round($r['duration']).'</td>'.
                '<td class="text-muted small pe-3">'.formatDateTime($r['last_checkin']).'</td>',
        ],
        'colleges'=>[
            'headers'=>['College','Unique Visitors','Duration (min)','Last Check-in'],
            'rowFn'=>fn($r)=>
                '<td class="ps-3 fw-semibold">'.safe($r['name']).'</td>'.
                '<td class="text-end">'.(int)$r['checkins'].'</td>'.
                '<td class="text-end">'.(int)round($r['duration']).'</td>'.
                '<td class="text-muted small pe-3">'.formatDateTime($r['last_checkin']).'</td>',
        ],
        'courses'=>[
            'headers'=>['College','Course','Unique Visitors','Duration (min)','Last Check-in'],
            'rowFn'=>fn($r)=>
                '<td class="ps-3 text-muted small">'.safe($r['college']).'</td>'.
                '<td class="fw-semibold">'.safe($r['course']).'</td>'.
                '<td class="text-end">'.(int)$r['checkins'].'</td>'.
                '<td class="text-end">'.(int)round($r['duration']).'</td>'.
                '<td class="text-muted small pe-3">'.formatDateTime($r['last_checkin']).'</td>',
        ],
        'demographics'=>[
            'headers'=>['ID Number','Sex','Check-in','Check-out','Duration (min)'],
            'rowFn'=>fn($r)=>
                '<td class="ps-3 fw-semibold">'.safe($r['display_label']).'</td>'.
                '<td>'.safe($r['sex']).'</td>'.
                '<td class="text-muted small">'.formatDateTime($r['checkin']).'</td>'.
                '<td class="text-muted small">'.($r['checkout']?formatDateTime($r['checkout']):'—').'</td>'.
                '<td class="text-end pe-3">'.(int)round($r['duration']).'</td>',
        ],
    ];

    if(!isset($cols[$tab])) return '';

    $cfg=$cols[$tab];
    $thead=implode('',array_map(fn($h)=>"<th class=\"small fw-semibold\">{$h}</th>",$cfg['headers']));
    $tbody=implode('',array_map(fn($r)=>'<tr>'.$cfg['rowFn']($r).'</tr>',$rows));

    return "<div class=\"table-responsive\"><table class=\"table table-sm table-striped table-hover align-middle mb-0\">"
         ."<thead class=\"table-dark\"><tr>{$thead}</tr></thead>"
         ."<tbody class=\"small\">{$tbody}</tbody>"
         ."</table></div>";
}

// ── MODAL PAGINATION ──────────────────────────────────────────────────────────

function renderModalPagination(int $totalPages, int $current, int $totalRecords, int $perPage): string
{
    if ($totalPages <= 1) return '';

    $isFirst = $current === 1;
    $isLast  = $current === $totalPages;
    $window  = 5;
    $start   = max(1, min($current - intdiv($window, 2), $totalPages - $window + 1));
    $end     = min($totalPages, $start + $window - 1);

    $li = fn(string $label, int $page, string $extra = '', bool $isText = false) =>
        "<li class=\"page-item {$extra}\"><a class=\"page-link\" href=\"#\""
        . ($isText ? '' : " data-page=\"{$page}\"")
        . ">{$label}</a></li>";

    $items  = $li('«', 1,              $isFirst ? 'disabled' : '');
    $items .= $li('‹', $current - 1,   $isFirst ? 'disabled' : '');

    if ($start > 1) {
        $items .= $li('1', 1, $current === 1 ? 'active' : '');
        if ($start > 2) $items .= $li('…', 0, 'disabled', true);
    }
    for ($p = $start; $p <= $end; $p++) {
        $items .= $li((string)$p, $p, $p === $current ? 'active' : '');
    }
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $items .= $li('…', 0, 'disabled', true);
        $items .= $li((string)$totalPages, $totalPages, $current === $totalPages ? 'active' : '');
    }
    $items .= $li('›', $current + 1,   $isLast ? 'disabled' : '');
    $items .= $li('»', $totalPages,     $isLast ? 'disabled' : '');

    $from = ($current - 1) * $perPage + 1;
    $to   = min($current * $perPage, $totalRecords);

    return "<small class=\"text-muted\">Showing {$from}–{$to} of {$totalRecords} records</small>"
         . "<nav class=\"mt-1\"><ul class=\"pagination pagination-sm mb-0 flex-wrap justify-content-center\">{$items}</ul></nav>";
}

// ── TAB HTML RENDERERS ────────────────────────────────────────────────────────

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
                        <th class="ps-3 small">ID Number</th>
                        <th class="small">Name</th>
                        <th class="small">College</th>
                        <th class="small">Course</th>
                        <th class="small">Type</th>
                        <th class="small">Section</th>
                        <th class="small">Sex</th>
                        <th class="small">Check-in</th>
                        <th class="small">Check-out</th>
                        <th class="small">Agency / Organization</th>
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

function renderUsersTab(array $topByCheckins, array $topByDuration): string
{
$flatCheckins = [];
foreach ($topByCheckins as $cls => $users) {
    foreach ($users as $user) {
        $flatCheckins[] = [
            'display_label'       => $user['display_label'],
            'college'             => $user['college']              ?: '—',
            'course'              => $user['course']               ?: '—',
            'type'                => $cls,
            'library'             => $user['library']              ?? '—',
            'count'               => $user['count'],
            'agency_organization' => $user['agency_organization']  ?? '—', // ← ADD
            'last_checkin'        => date('M j', strtotime($user['last_checkin'])),
        ];
    }
}

$flatDuration = [];
foreach ($topByDuration as $cls => $users) {
    foreach ($users as $user) {
        $flatDuration[] = [
            'display_label'       => $user['display_label'],
            'college'             => $user['college']             ?: '—',
            'course'              => $user['course']              ?: '—',   // ← ADD
            'type'                => $cls,
            'minutes'             => (int)round($user['minutes']),
            'agency_organization' => $user['agency_organization'] ?? '—', // ← ADD
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
                    <button class="btn btn-sm btn-outline-primary py-0 px-2 view-all-btn"
                            data-tab="users" style="font-size:.75rem;">
                        <i class="bi bi-arrow-up-right-square me-1"></i>View All
                    </button>
                </div>
                <div class="card-body p-0" style="min-height:175px;"
                     id="checkinDetailsCard"
                     data-rows="<?= htmlspecialchars(json_encode($flatCheckins), ENT_QUOTES) ?>"
                     data-page="1" data-per-page="3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr>
                                <th class="ps-3 small">ID Number</th>
                                <th class="small">College</th>
                                <th class="small">Course</th>
                                <th class="small">Type</th>
                                <th class="small">Section</th>
                                <th class="text-end small">Check-ins</th>
								<th class="small">Agency Organization</th>
                                <th class="text-end pe-3 small">Last Visit</th>
                            </tr></thead>
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
                            <thead class="table-light"><tr>
                                <th class="ps-3 small">ID Number</th>
                                <th class="small">College</th>
								<th class="small">Course</th>
                                <th class="small">Type</th>
                                <th class="text-end pe-3 small">Minutes</th>
								<th class="small">Agency Organization</th>
                            </tr></thead>
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

function renderCollegesTab(array $topByCheckins,array $topByDuration):string{
    ob_start(); ?>
    <div class="row g-4">
        <?php 
        $tabs=[
            ['title'=>'Top Colleges — Check-ins','subtitle'=>'Unique visitors per college','canvas'=>'chartCollegeCheckin','data'=>$topByCheckins,'valueKey'=>'count','class'=>'text-primary','isCount'=>true],
            ['title'=>'Top Colleges — Duration','subtitle'=>'Total session time per college','canvas'=>'chartCollegeDuration','data'=>$topByDuration,'valueKey'=>'minutes','class'=>'text-success','isCount'=>false],
        ];
        foreach($tabs as $tab): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0"><?= $tab['title'] ?></p>
                    <p class="text-muted mb-0" style="font-size:.72rem;"><?= $tab['subtitle'] ?></p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="<?= $tab['canvas'] ?>"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr>
                            <th>College</th>
                            <th class="text-end"><?= $tab['isCount']?'Visitors':'Duration (min)' ?></th>
                            <th class="text-end">Last Visit</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach($tab['data'] as $name=>$data): ?>
                        <tr>
                            <td class="fw-semibold"><?= safe($name) ?></td>
                            <td class="text-end fw-semibold <?= $tab['class'] ?>"><?= $tab['isCount']?$data[$tab['valueKey']]:round($data[$tab['valueKey']]) ?></td>
                            <td class="text-end text-muted"><?= date('M j, Y',strtotime($data['last_checkin'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($tab['data'])): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if(!$tab['isCount']): ?>
                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary view-all-btn" data-tab="colleges" style="font-size:.75rem;">View All Colleges</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
}

function renderCoursesTab(array $topByCheckins, array $topByDuration): string {
    $flatten = fn($data, $key) => array_map(fn($college, $courses) => array_map(fn($course, $info) => array_merge(['college'=>$college,'course'=>$course], $info), array_keys($courses), array_values($courses)), array_keys($data), array_values($data));
    
    $flatCheckins = array_merge(...$flatten($topByCheckins, 'count'));
    usort($flatCheckins, fn($a,$b)=>$b['count']<=>$a['count']);

    $flatDuration  = array_merge(...$flatten($topByDuration, 'minutes'));
    usort($flatDuration, fn($a,$b)=>$b['minutes']<=>$a['minutes']);

    ob_start(); ?>
    <div class="row g-4">
        <?php foreach([['Check-ins','chartCoursesCheckin','count',$flatCheckins,'Unique visitors per course'],['Duration','chartCoursesDuration','minutes',$flatDuration,'Total session time per course']] as $tab): 
            [$title,$canvasId,$valueKey,$rows,$subtitle]=$tab; ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <p class="fw-semibold small mb-0">Top Courses — <?= $title ?></p>
                    <p class="text-muted mb-0" style="font-size:.72rem;"><?= $subtitle ?></p>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="height:260px;position:relative;"><canvas id="<?= $canvasId ?>"></canvas></div>
                    <hr class="my-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr>
                            <th>College</th><th>Course</th>
                            <th class="text-end"><?= $title==='Check-ins'?'Visitors':'Duration (min)' ?></th>
                            <th class="text-end">Last Visit</th>
                        </tr></thead>
                        <tbody>
                        <?php if($rows): foreach($rows as $row): ?>
                            <tr>
                                <td class="text-muted"><?= safe($row['college']) ?></td>
                                <td class="fw-semibold"><?= safe($row['course']) ?></td>
                                <td class="text-end"><?= round($row[$valueKey]??0) ?></td>
                                <td class="text-end text-muted"><?= !empty($row['last_checkin'])?date('M j',strtotime($row['last_checkin'])):'—' ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if($title==='Duration'): ?>
                        <div class="text-end mt-2">
                            <button class="btn btn-sm btn-outline-secondary view-all-btn" data-tab="courses" style="font-size:.75rem;">View All Courses</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
}

function renderDemographicsTab(array $countBySex,int $total):string{
    $sexData=[
        'Male'=>['icon'=>'bi-gender-male','bg'=>'info','count'=>$countBySex['Male']??0],
        'Female'=>['icon'=>'bi-gender-female','bg'=>'danger','count'=>$countBySex['Female']??0],
        'Unknown'=>['icon'=>'bi-question-circle','bg'=>'secondary','count'=>$countBySex['Unknown']??0],
    ];
    foreach($sexData as $k=>&$v)$v['pct']=$total?round($v['count']/$total*100,1):0;

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
                <?php foreach($sexData as $label=>$d): if($d['count']>0||$label!=='Unknown'): ?>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-<?= $d['bg'] ?>-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px;flex-shrink:0;">
                                <i class="bi <?= $d['icon'] ?> text-<?= $d['bg'] ?>"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0"><?= $label ?></p>
                                <h4 class="fw-bold mb-0"><?= number_format($d['count']) ?></h4>
                                <?php if($label!=='Unknown'): ?><small class="text-muted"><?= $d['pct'] ?>% of total</small><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <div class="col-12 text-end">
            <button class="btn btn-sm btn-outline-secondary view-all-btn" data-tab="demographics" style="font-size:.75rem;">View All Logs</button>
        </div>
    </div>
    <?php return ob_get_clean();
}

// ── REQUEST BOOTSTRAP ─────────────────────────────────────────────────────────

$action    = $_POST['action'] ?? 'tab';
$tab       = $_POST['tab']    ?? 'users';
$page      = max(1, (int)($_POST['page'] ?? 1));
$perPage   = 10;
$offset    = ($page - 1) * $perPage;
$validTabs = ['logs', 'users', 'colleges', 'courses', 'demographics'];

if (!in_array($tab, $validTabs)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid tab.']);
    exit;
}

[$where, $params] = buildWhereClause($_POST);
$logs             = fetchVisitLogs($where, $params);

// ── DISPATCH ──────────────────────────────────────────────────────────────────

switch ($action) {

    case 'viewAll':
        $pageData = match($tab) {
            'logs'         => buildViewAllLogs($logs, $offset, $perPage),
            'users'        => buildViewAllUsers($logs, $offset, $perPage),
            'colleges'     => buildViewAllColleges($logs, $offset, $perPage),
            'courses'      => buildViewAllCourses($logs, $offset, $perPage),
            'demographics' => buildViewAllDemographics($logs, $offset, $perPage),
            default        => ['rows' => [], 'total' => 0],
        };

        $totalRecords = $pageData['total'];
        $totalPages   = $totalRecords > 0 ? (int)ceil($totalRecords / $perPage) : 1;
        $page         = min($page, $totalPages);

        echo json_encode([
            'status'     => 'success',
            'tableHtml'  => renderModalTable($tab, $pageData['rows']),
            'pagination' => renderModalPagination($totalPages, $page, $totalRecords, $perPage),
            'total'      => $totalRecords,
            'totalPages' => $totalPages,
            'page'       => $page,
        ]);
        break;

    case 'tab':
    default:
        $kpis    = computeKpis($logs, $_POST['endDate'] ?? '');
        $uData   = aggregateTopUsers($logs);
        $clsDist = aggregateClassification($logs);
        $colData = aggregateTopColleges($logs);
        $crsData = aggregateTopCourses($logs);
        $sexData = aggregateSex($logs);
        $kpi3    = buildKpiTop3($logs);

        $allLogsFlat = array_map(fn($log) => [
            'id_number'           => $log['id_number']            ?? '',
            'name'                => $log['name']                 ?? '',
            'college'             => $log['college']              ?? '',
            'course'              => $log['course']               ?? '',
            'classification'      => $log['classification']       ?? '',
            'library'             => $log['library_section_name'] ?? '',
            'sex'                 => $log['sex']                  ?? '',
            'checkin_time'        => $log['checkin_time']         ?? '',
            'checkout_time'       => $log['checkout_time']        ?? '',
            'agency_organization' => $log['agency_organization']  ?? '',
            'duration_minutes'    => calcDurationMinutes($log['checkin_time'], $log['checkout_time'] ?? null),
        ], $logs);

        $html = match($tab) {
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
            'allLogs'                    => $allLogsFlat,
        ]);
        break;
}