<?php
date_default_timezone_set('Asia/Manila');
require_once "../../db/dbconnection.php";

/*
 * HELPER — sanitise and validate a YYYY-MM-DD date string.
 * Returns null if invalid so callers can fall back to defaults.
 */
function validateDate(?string $d): ?string
{
    return ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : null;
}

/*
 * HELPER — build a WHERE fragment for the date range.
 * Uses CAST(checkin_time AS DATE) BETWEEN … AND …
 * Falls back to CAST(GETDATE() AS DATE) when no valid dates are given.
 */
function dateRangeFilter(string $col = 'checkin_time'): string
{
    $start = validateDate($_POST['startDate'] ?? null);
    $end   = validateDate($_POST['endDate']   ?? null);

    if ($start && $end) {
        return "AND CAST($col AS DATE) BETWEEN '$start' AND '$end'";
    }
    // Default: today only
    return "AND CAST($col AS DATE) = CAST(GETDATE() AS DATE)";
}

/*
 * Returns TRUE when the selected range is exactly today.
 * Used to decide the KPI label (active vs total).
 */
function isTodayRange(): bool
{
    $start = validateDate($_POST['startDate'] ?? null);
    $end   = validateDate($_POST['endDate']   ?? null);
    $today = date('Y-m-d');
    return ($start === $today && $end === $today) || (!$start && !$end);
}


// ============================================================
//  SECTIONS — list of active sections (for KPI card shells)
// ============================================================

function librarySections(): void
{
    $rows = execsqlSRS("
        SELECT SectionID, SectionCode, SectionName
        FROM   LibrarySection
        WHERE  IsActive = 1
        ORDER  BY SectionName
    ", "Search", []);

    echo json_encode($rows);
}


// ============================================================
//  KPI — Visit counts per section for the selected date range.
//
//  When range = today  → only count records with checkout_time IS NULL
//                         (i.e. currently inside the library).
//  When range ≠ today  → count all check-ins regardless of checkout.
// ============================================================

function loadKPI(): void
{
    $drFilter  = dateRangeFilter('l.checkin_time');
    $isToday   = isTodayRange();
    $activeFilter = $isToday ? "AND l.checkout_time IS NULL" : "";

    $rows = execsqlSRS("
        SELECT
            s.SectionID,
            s.SectionCode,
            s.SectionName,
            COUNT(l.id) AS total
        FROM LibrarySection s
        LEFT JOIN Library_logs l
            ON  l.library = s.SectionID
            $drFilter
            $activeFilter
        WHERE s.IsActive = 1
        GROUP BY s.SectionID, s.SectionCode, s.SectionName
    ", "Search", []);

    foreach ($rows as &$row) {
        $row['SectionCode'] = trim($row['SectionCode']);
    }

    echo json_encode($rows);
}


// ============================================================
//  DAILY LOGS — paginated, filtered by date range + section
// ============================================================

function loadDailyLogs(): void
{
    $page      = max(1, (int)($_POST['page'] ?? 1));
    $limit     = 5;
    $sectionID = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                 ? (int)$_POST['sectionID'] : null;

    $sectionFilter = $sectionID ? "AND l.library = $sectionID" : "";
    $drFilter      = dateRangeFilter('l.checkin_time');

    // Total count
    $countResult = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM   Library_logs l
        WHERE  1=1
               $drFilter
               $sectionFilter
    ", "Search", []);

    $totalRows  = (int)$countResult[0]['total'];
    $totalPages = max(1, (int)ceil($totalRows / $limit));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $limit;

    $logs = execsqlSRS("
        SELECT
            l.id_number,
            l.college,
            l.course,
            s.SectionName,
            l.checkin_time,
            l.checkout_time
        FROM Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE 1=1
              $drFilter
              $sectionFilter
        ORDER BY l.checkin_time DESC
        OFFSET $offset ROWS
        FETCH NEXT $limit ROWS ONLY
    ", "Search", []);

    ob_start();
    if (empty($logs)) {
        echo "<tr><td colspan='7' class='text-center text-muted py-4'>No records for selected range.</td></tr>";
    } else {
        foreach ($logs as $index => $log) {
            $rowClass    = $index % 2 === 0 ? 'table-success' : '';
            $statusBadge = empty($log['checkout_time'])
                ? "<span class='badge bg-success-subtle text-success rounded-pill px-3'>Active</span>"
                : "<span class='badge bg-secondary-subtle text-secondary rounded-pill px-3'>Completed</span>";

            echo "<tr class='$rowClass'>";
            echo "<td class='px-4 fw-semibold'>" . htmlspecialchars($log['id_number'])        . "</td>";
            echo "<td>"                           . htmlspecialchars($log['college']  ?? '—')  . "</td>";
            echo "<td>"                           . htmlspecialchars($log['course']   ?? '—')  . "</td>";
            echo "<td><span class='badge bg-light text-dark border'>"
                 . htmlspecialchars($log['SectionName'] ?? '—') . "</span></td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($log['checkin_time'])) . "</td>";
            echo "<td>" . ($log['checkout_time']
                 ? date('M j, Y g:i A', strtotime($log['checkout_time'])) : '—') . "</td>";
            echo "<td class='text-center'>$statusBadge</td>";
            echo "</tr>";
        }
    }
    $rowsHtml = ob_get_clean();

    echo json_encode([
        'rows'        => $rowsHtml,
        'totalRows'   => $totalRows,
        'totalPages'  => $totalPages,
        'currentPage' => $page,
        'limit'       => $limit,
    ]);
}


// ============================================================
//  MONTHLY TREND — visit counts grouped by month
//  Uses the same global date range from the UI.
// ============================================================

function loadMonthlyTrend(): void
{
    $sectionID     = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                     ? (int)$_POST['sectionID'] : null;
    $sectionFilter = $sectionID ? "AND library = $sectionID" : "";

    $start = validateDate($_POST['startDate'] ?? null);
    $end   = validateDate($_POST['endDate']   ?? null);

    if ($start && $end) {
        $dateFilter = "AND CAST(checkin_time AS DATE) BETWEEN '$start' AND '$end'";
    } else {
        // Default: last 6 calendar months
        $dateFilter = "AND checkin_time >= DATEADD(MONTH,-5,DATEFROMPARTS(YEAR(GETDATE()),MONTH(GETDATE()),1))";
    }

    $rows = execsqlSRS("
        SELECT
            FORMAT(checkin_time,'MMM yyyy')  AS month,
            YEAR(checkin_time)               AS yr,
            MONTH(checkin_time)              AS mo,
            COUNT(*)                         AS total
        FROM Library_logs
        WHERE 1=1
              $dateFilter
              $sectionFilter
        GROUP BY FORMAT(checkin_time,'MMM yyyy'), YEAR(checkin_time), MONTH(checkin_time)
        ORDER BY yr, mo
    ", "Search", []);

    echo json_encode($rows);
}


// ============================================================
//  COLLEGE & COURSE ACTIVITY — filtered by date range + section
// ============================================================

function loadCollegeCourseActivity(): void
{
    $sectionID     = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                     ? (int)$_POST['sectionID'] : null;
    $sectionFilter = $sectionID ? "AND l.library = $sectionID" : "";
    $drFilter      = dateRangeFilter('l.checkin_time');

    $rows = execsqlSRS("
        SELECT
            l.college,
            l.course,
            s.SectionName   AS section_name,
            COUNT(*)        AS total
        FROM Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE l.college IS NOT NULL AND l.college <> ''
          AND l.course  IS NOT NULL AND l.course  <> ''
          $drFilter
          $sectionFilter
        GROUP BY l.college, l.course, s.SectionName
        ORDER BY l.college, l.course, total DESC
    ", "Search", []);

    // Build nested: college → courses[]
    $grouped = [];
    foreach ($rows as $row) {
        $college = $row['college'];
        $course  = $row['course'];

        if (!isset($grouped[$college])) {
            $grouped[$college] = ['college'=>$college,'total'=>0,'courses'=>[]];
        }
        if (!isset($grouped[$college]['courses'][$course])) {
            $grouped[$college]['courses'][$course] = ['course'=>$course,'total'=>0,'sections'=>[]];
        }
        $t = (int)$row['total'];
        $grouped[$college]['courses'][$course]['total']      += $t;
        $grouped[$college]['courses'][$course]['sections'][]  = [
            'section_name' => $row['section_name'] ?? '—',
            'total'        => $t,
        ];
        $grouped[$college]['total'] += $t;
    }

    $result = [];
    foreach ($grouped as $col) {
        $courses = array_values($col['courses']);
        usort($courses, fn($a,$b) => $b['total'] - $a['total']);
        $col['courses'] = $courses;
        $result[] = $col;
    }
    usort($result, fn($a,$b) => $b['total'] - $a['total']);

    echo json_encode(array_values($result));
}


// ============================================================
//  COUNT PENDING CHECKOUT
//  Returns how many records have no checkout_time from days
//  STRICTLY BEFORE today (today is always excluded).
// ============================================================

function countPendingCheckout(): void
{
    $result = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM   Library_logs
        WHERE  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) < CAST(GETDATE() AS DATE)
    ", "Search", []);

    echo json_encode(['count' => (int)($result[0]['total'] ?? 0)]);
}


// ============================================================
//  FORCE CHECKOUT
//  Sets checkout_time = 7:00 PM of the checkin_date for all
//  records that have no checkout and checked in on a PREVIOUS
//  day (never touches today's records).
//
//  7 PM = DATEADD(HOUR, 19, CAST(CAST(checkin_time AS DATE) AS DATETIME))
// ============================================================

function forceCheckout(): void
{
    // Extra safety guard: double-check we never touch today
    execsqlSRS("
        UPDATE Library_logs
        SET    checkout_time = DATEADD(
                   HOUR, 19,
                   CAST(CAST(checkin_time AS DATE) AS DATETIME)
               )
        WHERE  checkout_time IS NULL
          AND  CAST(checkin_time AS DATE) < CAST(GETDATE() AS DATE)
    ", "Update", []);

    // Return how many rows were updated
    $affected = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM   Library_logs
        WHERE  CAST(checkout_time AS TIME) = '19:00:00'
          AND  CAST(checkin_time  AS DATE) < CAST(GETDATE() AS DATE)
    ", "Search", []);

    echo json_encode(['affected' => (int)($affected[0]['total'] ?? 0)]);
}

/*
 * NOTE: If your execsqlSRS wrapper exposes an affected-rows count,
 * use that directly instead of the COUNT re-query above.
 * Replace the forceCheckout body with:
 *
 *   $n = execsqlSRS("UPDATE ...", "Update", []);
 *   echo json_encode(['affected' => $n]);    // if wrapper returns row count
 */


// ============================================================
//  DISPATCH
// ============================================================

$request = $_POST['request'] ?? '';

switch ($request) {
    case 'sections':              librarySections();              break;
    case 'kpiData':               loadKPI();                      break;
    case 'dailyLogs':             loadDailyLogs();                break;
    case 'monthlyTrend':          loadMonthlyTrend();             break;
    case 'collegeCourseActivity': loadCollegeCourseActivity();    break;
    case 'countPendingCheckout':  countPendingCheckout();         break;
    case 'forceCheckout':         forceCheckout();                break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request.']);
}
?>