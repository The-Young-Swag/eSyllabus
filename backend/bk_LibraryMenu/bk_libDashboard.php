<?php
date_default_timezone_set('Asia/Manila');
require_once "../../db/dbconnection.php";

// ============================================================
//  KPI — Active visits per section right now (today, no checkout)
// ============================================================

function loadKPI()
{
    $rows = execsqlSRS("
        SELECT
            s.SectionID,
            s.SectionCode,
            s.SectionName,
            COUNT(l.id) AS total
        FROM LibrarySection s
        LEFT JOIN Library_logs l
            ON  l.library = s.SectionID
            AND l.checkout_time IS NULL
            AND CAST(l.checkin_time AS DATE) = CAST(GETDATE() AS DATE)
        WHERE s.IsActive = 1
        GROUP BY s.SectionID, s.SectionCode, s.SectionName
    ", "Search", []);

    foreach ($rows as &$row) {
        $row['SectionCode'] = trim($row['SectionCode']);
    }

    echo json_encode($rows);
}


// ============================================================
//  KPI — Single section count (for targeted refresh)
// ============================================================

function getSingleSectionKPI()
{
    $sectionID = $_POST["sectionID"];

    $row = execsqlSRS("
        SELECT
            s.SectionID,
            s.SectionName,
            COUNT(l.id) AS total
        FROM LibrarySection s
        LEFT JOIN Library_logs l
            ON  l.library = s.SectionID
            AND CAST(l.checkin_time AS DATE) = CAST(GETDATE() AS DATE)
        WHERE s.SectionID = ?
        GROUP BY s.SectionID, s.SectionName
    ", "Search", [$sectionID]);

    echo json_encode($row[0]);
}


// ============================================================
//  DAILY LOGS — Paginated today's check-in / check-out records
// ============================================================

function loadDailyLogs()
{
    $page  = max(1, (int)($_POST["page"] ?? 1));
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $countResult = execsqlSRS("
        SELECT COUNT(*) AS total
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
    ", "Search", []);

    $totalRows  = (int)$countResult[0]["total"];
    $totalPages = max(1, (int)ceil($totalRows / $limit));

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
        WHERE CAST(l.checkin_time AS DATE) = CAST(GETDATE() AS DATE)
        ORDER BY l.checkin_time DESC
        OFFSET $offset ROWS
        FETCH NEXT $limit ROWS ONLY
    ", "Search", []);

    ob_start();
    foreach ($logs as $log) {
        $statusBadge = empty($log["checkout_time"])
            ? "<span class='badge bg-success-subtle text-success rounded-pill px-3'>Active</span>"
            : "<span class='badge bg-secondary-subtle text-secondary rounded-pill px-3'>Completed</span>";

        echo "<tr>";
        echo "<td class='px-4 fw-semibold'>"  . htmlspecialchars($log["id_number"])        . "</td>";
        echo "<td>"                            . htmlspecialchars($log["college"] ?? "—")   . "</td>";
        echo "<td>"                            . htmlspecialchars($log["course"]  ?? "—")   . "</td>";
        echo "<td><span class='badge bg-light text-dark border'>"
             . htmlspecialchars($log["SectionName"] ?? "—")
             . "</span></td>";
        echo "<td>" . date('M j, Y g:i A', strtotime($log["checkin_time"])) . "</td>";
        echo "<td>" . ($log["checkout_time"]
             ? date('M j, Y g:i A', strtotime($log["checkout_time"]))
             : "—") . "</td>";
        echo "<td class='text-center'>$statusBadge</td>";
        echo "</tr>";
    }
    $rowsHtml = ob_get_clean();

    echo json_encode([
        "rows"        => $rowsHtml,
        "totalPages"  => $totalPages,
        "currentPage" => $page,
    ]);
}


// ============================================================
//  MONTHLY TREND — Visit counts for the last 6 months
// ============================================================

function loadMonthlyTrend()
{
    $rows = execsqlSRS("
        SELECT
            FORMAT(checkin_time, 'MMM') AS month,
            COUNT(*)                    AS total
        FROM Library_logs
        WHERE checkin_time >= DATEADD(MONTH, -6, GETDATE())
        GROUP BY FORMAT(checkin_time, 'MMM'), MONTH(checkin_time)
        ORDER BY MIN(checkin_time)
    ", "Search", []);

    echo json_encode($rows);
}


// ============================================================
//  DEPARTMENT OVERVIEW — Visit counts per college today
// ============================================================

function loadDepartmentOverview()
{
    $rows = execsqlSRS("
        SELECT
            college,
            COUNT(*) AS total
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
          AND college IS NOT NULL
          AND college <> ''
        GROUP BY college
        ORDER BY total DESC
    ", "Search", []);

    echo json_encode($rows);
}


// ============================================================
//  COLLEGE & COURSE ACTIVITY
//  Returns colleges with their nested course breakdowns for today.
//  Shape: [ { college, total, courses: [ { course, total }, ... ] } ]
// ============================================================

function loadCollegeCourseActivity()
{
    // Flat rows: one row per college+course with visit count today
    $rows = execsqlSRS("
        SELECT
            college,
            course,
            COUNT(*) AS total
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
          AND college IS NOT NULL AND college <> ''
          AND course  IS NOT NULL AND course  <> ''
        GROUP BY college, course
        ORDER BY college, total DESC
    ", "Search", []);

    // Group courses under their college and sum college totals in PHP
    $grouped = [];
    foreach ($rows as $row) {
        $college = $row['college'];
        if (!isset($grouped[$college])) {
            $grouped[$college] = ['college' => $college, 'total' => 0, 'courses' => []];
        }
        $grouped[$college]['total']     += (int)$row['total'];
        $grouped[$college]['courses'][]  = [
            'course' => $row['course'],
            'total'  => (int)$row['total'],
        ];
    }

    // Re-index, sort colleges by total descending
    $result = array_values($grouped);
    usort($result, fn($a, $b) => $b['total'] - $a['total']);

    echo json_encode($result);
}


// ============================================================
//  DISPATCH
// ============================================================

$request = $_POST["request"] ?? "";

switch ($request) {
    case "kpiData":              loadKPI();                    break;
    case "singleSectionKPI":     getSingleSectionKPI();        break;
    case "dailyLogs":            loadDailyLogs();              break;
    case "monthlyTrend":         loadMonthlyTrend();           break;
    case "departmentOverview":   loadDepartmentOverview();     break;
    case "collegeCourseActivity": loadCollegeCourseActivity(); break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Invalid request."]);
}