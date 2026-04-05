<?php
date_default_timezone_set('Asia/Manila');
require_once "../../db/dbconnection.php";

// Returns a validated YYYY-MM-DD string, or null if invalid.
function validateDate(?string $d): ?string
{
    return ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : null;
}

// Builds a WHERE fragment that filters $col to the posted date range.
// Falls back to today when no valid dates are posted.
function dateRangeClause(string $col): string
{
    $start = validateDate($_POST['startDate'] ?? null);
    $end   = validateDate($_POST['endDate']   ?? null);

    return ($start && $end)
        ? "AND CAST($col AS DATE) BETWEEN '$start' AND '$end'"
        : "AND CAST($col AS DATE) = CAST(GETDATE() AS DATE)";
}


// ── SECTIONS ──────────────────────────────────────────────────────────────────

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


// ── KPI ───────────────────────────────────────────────────────────────────────
// Today range  → count only records where checkout_time IS NULL (currently inside).
// Any other range → count all check-ins regardless of checkout status.

function loadKPI(): void
{
    $start   = validateDate($_POST['startDate'] ?? null);
    $end     = validateDate($_POST['endDate']   ?? null);
    $today   = date('Y-m-d');
    $isToday = (!$start && !$end) || ($start === $today && $end === $today);

    $drClause     = dateRangeClause('l.checkin_time');
    $activeClause = $isToday ? "AND l.checkout_time IS NULL" : "";

    $rows = execsqlSRS("
        SELECT
            s.SectionID,
            s.SectionCode,
            s.SectionName,
            COUNT(l.id) AS total
        FROM LibrarySection s
        LEFT JOIN Library_logs l
            ON  l.library = s.SectionID
            $drClause
            $activeClause
        WHERE s.IsActive = 1
        GROUP BY s.SectionID, s.SectionCode, s.SectionName
    ", "Search", []);

    foreach ($rows as &$row) {
        $row['SectionCode'] = trim($row['SectionCode']);
    }

    echo json_encode($rows);
}


// ── DAILY LOGS ────────────────────────────────────────────────────────────────

function loadDailyLogs(): void
{
    $page = max(1, (int)($_POST['page'] ?? 1));
    $limit = 5;
    $sectionID = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                 ? (int)$_POST['sectionID'] : null;

    $sectionClause = $sectionID ? "AND l.library = $sectionID" : "";
    $drClause      = dateRangeClause('l.checkin_time');

    $countResult = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM   Library_logs l
        WHERE  1=1 $drClause $sectionClause
    ", "Search", []);

    $totalRows = (int)$countResult[0]['total'];
    $totalPages = max(1, (int)ceil($totalRows / $limit));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;

    $logs = execsqlSRS("
        SELECT
            l.id_number,
            l.name,
            l.college,
            l.course,
            s.SectionName,
            l.checkin_time,
            l.checkout_time
        FROM Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE 1=1 $drClause $sectionClause
        ORDER BY l.checkin_time DESC
        OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY
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

            $idNumber  = $log['id_number'] ?? '';
            $displayId = (empty($idNumber) || $idNumber === '0')
                ? htmlspecialchars($log['name'] ?: 'Guest', ENT_QUOTES, 'UTF-8')
                : htmlspecialchars($idNumber, ENT_QUOTES, 'UTF-8');

            echo "<tr class='$rowClass'>";
            echo "<td class='px-4 fw-semibold'>$displayId</td>";
            echo "<td>" . htmlspecialchars($log['college'] ?? '—') . "</td>";
            echo "<td>" . htmlspecialchars($log['course']  ?? '—') . "</td>";
            echo "<td><span class='badge bg-light text-dark border'>"
                 . htmlspecialchars($log['SectionName'] ?? '—') . "</span></td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($log['checkin_time'])) . "</td>";
            echo "<td>" . ($log['checkout_time']
                 ? date('M j, Y g:i A', strtotime($log['checkout_time'])) : '—') . "</td>";
            echo "<td class='text-center'>$statusBadge</td>";
            echo "</tr>";
        }
    }

    echo json_encode([
        'rows' => ob_get_clean(),
        'totalRows' => $totalRows,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'limit' => $limit,
    ]);
}


// ── MONTHLY TREND ─────────────────────────────────────────────────────────────

function loadMonthlyTrend(): void
{
    $sectionID     = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                     ? (int)$_POST['sectionID'] : null;
    $sectionClause = $sectionID ? "AND library = $sectionID" : "";

    $start = validateDate($_POST['startDate'] ?? null);
    $end   = validateDate($_POST['endDate']   ?? null);

    $dateClause = ($start && $end)
        ? "AND CAST(checkin_time AS DATE) BETWEEN '$start' AND '$end'"
        : "AND checkin_time >= DATEADD(MONTH,-5,DATEFROMPARTS(YEAR(GETDATE()),MONTH(GETDATE()),1))";

    $rows = execsqlSRS("
        SELECT
            FORMAT(checkin_time,'MMM yyyy') AS month,
            YEAR(checkin_time) AS yr,
            MONTH(checkin_time) AS mo,
            COUNT(*) AS total
        FROM Library_logs
        WHERE 1=1 $dateClause $sectionClause
        GROUP BY FORMAT(checkin_time,'MMM yyyy'), YEAR(checkin_time), MONTH(checkin_time)
        ORDER BY yr, mo
    ", "Search", []);

    echo json_encode($rows);
}


// ── COLLEGE & COURSE ACTIVITY ─────────────────────────────────────────────────

function loadCollegeCourseActivity(): void
{
    $sectionID = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                     ? (int)$_POST['sectionID'] : null;
    $sectionClause = $sectionID ? "AND l.library = $sectionID" : "";
    $drClause = dateRangeClause('l.checkin_time');

    $rows = execsqlSRS("
        SELECT
            l.college,
            l.course,
            s.SectionName AS section_name,
            COUNT(*) AS total
        FROM Library_logs l
        LEFT JOIN LibrarySection s ON l.library = s.SectionID
        WHERE l.college IS NOT NULL AND l.college <> ''
          AND l.course  IS NOT NULL AND l.course  <> ''
          $drClause
          $sectionClause
        GROUP BY l.college, l.course, s.SectionName
        ORDER BY l.college, l.course, total DESC
    ", "Search", []);

    // Build nested structure: college → courses[]
    $grouped = [];
    foreach ($rows as $row) {
        $college = $row['college'];
        $course = $row['course'];
        $total = (int)$row['total'];

        if (!isset($grouped[$college])) {
            $grouped[$college] = ['college' => $college, 'total' => 0, 'courses' => []];
        }
        if (!isset($grouped[$college]['courses'][$course])) {
            $grouped[$college]['courses'][$course] = ['course' => $course, 'total' => 0, 'sections' => []];
        }

        $grouped[$college]['courses'][$course]['total']      += $total;
        $grouped[$college]['courses'][$course]['sections'][]  = [
            'section_name' => $row['section_name'] ?? '—',
            'total' => $total,
        ];
        $grouped[$college]['total'] += $total;
    }

    // Sort colleges and their courses by visit count descending.
    $result = array_values($grouped);
    foreach ($result as &$col) {
        $col['courses'] = array_values($col['courses']);
        usort($col['courses'], fn($a, $b) => $b['total'] - $a['total']);
    }
    usort($result, fn($a, $b) => $b['total'] - $a['total']);

    echo json_encode($result);
}


// ── COUNT PENDING CHECKOUT ────────────────────────────────────────────────────
// Counts records with no checkout_time that checked in on a day BEFORE today.
// The pre-cast boundary keeps the predicate sargable (index-friendly).

function countPendingCheckout(): void
{
    $todayBoundary = date('Y-m-d') . ' 00:00:00';

    $result = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM   Library_logs
        WHERE  checkout_time IS NULL
          AND  checkin_time  < '$todayBoundary'
    ", "Search", []);

    echo json_encode(['count' => (int)($result[0]['total'] ?? 0)]);
}


// ── FORCE CHECKOUT ────────────────────────────────────────────────────────────
// Sets checkout_time = 7:00 PM of each record's own check-in date.
// Today's records are never touched.
// The count is taken before the UPDATE so the affected number is accurate.
//
// Recommended index (run once):
//   CREATE INDEX IX_Library_logs_checkout_checkin
//   ON Library_logs (checkout_time, checkin_time) INCLUDE (id);

function forceCheckout(): void
{
    $todayBoundary = date('Y-m-d') . ' 00:00:00';

    $countResult = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM   Library_logs
        WHERE  checkout_time IS NULL
          AND  checkin_time  < '$todayBoundary'
    ", "Search", []);
    $affected = (int)($countResult[0]['total'] ?? 0);

    if ($affected > 0) {
        execsqlSRS("
            UPDATE Library_logs
            SET    checkout_time = DATEADD(HOUR, 19, CAST(CAST(checkin_time AS DATE) AS DATETIME))
            WHERE  checkout_time IS NULL
              AND  checkin_time  < '$todayBoundary'
        ", "Update", []);
    }

    echo json_encode(['affected' => $affected]);
}


// ── DISPATCH ──────────────────────────────────────────────────────────────────

$request = $_POST['request'] ?? '';

switch ($request) {
    case 'sections':
        librarySections();
        break;
    case 'kpiData':
        loadKPI();
        break;
    case 'dailyLogs':
        loadDailyLogs();
        break;
    case 'monthlyTrend':
        loadMonthlyTrend();
        break;
    case 'collegeCourseActivity':
         loadCollegeCourseActivity();
          break;
    case 'countPendingCheckout':
        countPendingCheckout();
        break;
    case 'forceCheckout':
        forceCheckout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request.']);
}