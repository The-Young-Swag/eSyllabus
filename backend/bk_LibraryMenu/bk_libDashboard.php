<?php
date_default_timezone_set('Asia/Manila');
require_once "../../db/dbconnection.php";

// Returns a validated YYYY-MM-DD string, or null if invalid.
function validateDate(?string $d): ?string
{
    return ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : null;
}

function dateRangeClause(string $col): string
{
    $start = validateDate($_POST['startDate'] ?? null);
    $end   = validateDate($_POST['endDate']   ?? null);

    return ($start && $end)
        ? "AND CAST($col AS DATE) BETWEEN '$start' AND '$end'"
        : "AND CAST($col AS DATE) = CAST(GETDATE() AS DATE)";
}


//  SECTIONS 
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


//  KPI 
function loadKPI(): void
{
    $start = validateDate($_POST['startDate'] ?? null);
    $end = validateDate($_POST['endDate']   ?? null);
    $today = date('Y-m-d');
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


//  DAILY LOGS 
function loadDailyLogs(): void
{
    $page = max(1, (int)($_POST['page'] ?? 1));
    $limit = 5;
    $sectionID = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
                 ? (int)$_POST['sectionID'] : null;

    $sectionClause = $sectionID ? "AND l.library = $sectionID" : "";
    $drClause = dateRangeClause('l.checkin_time');

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


//  MONTHLY TREND 
function loadMonthlyTrend(): void
{
    $sectionID = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
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


//  COLLEGE & COURSE ACTIVITY 
function loadCollegeCourseActivity(): void
{
    $sectionID = isset($_POST['sectionID']) && $_POST['sectionID'] !== ''
        ? (int)$_POST['sectionID'] : null;

    $sectionClause = $sectionID ? "AND l.library = $sectionID" : "";
    $dateRangeClause = dateRangeClause('l.checkin_time');

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
          $dateRangeClause
          $sectionClause
        GROUP BY l.college, l.course, s.SectionName
        ORDER BY l.college, l.course, total DESC
    ", "Search", []);

    $groupedData = [];

    foreach ($rows as $row) {
        $collegeName = $row['college'];
        $courseName  = $row['course'];
        $totalVisits = (int)$row['total'];

        if (!isset($groupedData[$collegeName])) {
            $groupedData[$collegeName] = [
                'college' => $collegeName,
                'total'   => 0,
                'courses' => []
            ];
        }

        if (!isset($groupedData[$collegeName]['courses'][$courseName])) {
            $groupedData[$collegeName]['courses'][$courseName] = [
                'course'   => $courseName,
                'total'    => 0,
                'sections' => []
            ];
        }

        $groupedData[$collegeName]['courses'][$courseName]['total'] += $totalVisits;

        $groupedData[$collegeName]['courses'][$courseName]['sections'][] = [
            'section_name' => $row['section_name'] ?? '—',
            'total'        => $totalVisits,
        ];

        $groupedData[$collegeName]['total'] += $totalVisits;
    }

    $result = array_values($groupedData);

    foreach ($result as &$college) {
        $college['courses'] = array_values($college['courses']);

        usort($college['courses'], function ($courseA, $courseB) {
            return $courseB['total'] - $courseA['total'];
        });
    }
    unset($college); // prevent reference issues

    usort($result, function ($collegeA, $collegeB) {
        return $collegeB['total'] - $collegeA['total'];
    });

    echo json_encode($result);
}


//  COUNT PENDING CHECKOUT 
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


//  FORCE CHECKOUT 
function forceCheckout(): void
{
    $todayBoundary = date('Y-m-d') . ' 00:00:00';

    $countResult = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM Library_logs
        WHERE checkout_time IS NULL
          AND checkin_time  < '$todayBoundary'
    ", "Search", []);
    $affected = (int)($countResult[0]['total'] ?? 0);

    if ($affected > 0) {
        execsqlSRS("
            UPDATE Library_logs
            SET checkout_time = DATEADD(HOUR, 19, CAST(CAST(checkin_time AS DATE) AS DATETIME))
            WHERE  checkout_time IS NULL
              AND  checkin_time  < '$todayBoundary'
        ", "Update", []);
    }

    echo json_encode(['affected' => $affected]);
}


//  DISPATCH 
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