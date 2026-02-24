<?php
date_default_timezone_set('Asia/Manila');
include "../../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {

    case "kpiData":
        loadKPI();
        break;

    case "dailyLogs":
        loadDailyLogs();
        break;

    case "monthlyTrend":
        loadMonthlyTrend();
        break;

    case "departmentOverview":
        loadDepartmentOverview();
        break;

    default:
        echo "Invalid Request";
}


function loadKPI() {
    // Fetch active library sections and today’s visit counts
    $query = execsqlSRS("
        SELECT 
            s.SectionID,
            s.SectionCode,
            s.SectionName,
            COUNT(l.id) AS total
        FROM LibrarySection s
        LEFT JOIN Library_logs l
            ON l.library = s.SectionID
            AND CAST(SWITCHOFFSET(CONVERT(datetimeoffset, l.checkin_time), '+08:00') AS DATE) = CAST(GETDATE() AS DATE)
        WHERE s.IsActive = 1
        GROUP BY s.SectionID, s.SectionCode, s.SectionName
    ", "Search", []);

    foreach($query as &$row){
        $row['SectionCode'] = trim($row['SectionCode']); // remove whitespace
        $row['total'] = (int)$row['total'];              // ensure number
    }

    echo json_encode($query);
}


function getSingleSectionKPI() {

    $sectionID = $_POST["sectionID"];

    $query = execsqlSRS("
        SELECT 
            s.SectionID,
            s.SectionName,
            COUNT(l.id) as total
        FROM LibrarySection s
        LEFT JOIN Library_logs l
            ON l.library = s.SectionID
            AND CAST(l.checkin_time AS DATE) = CAST(GETDATE() AS DATE)
        WHERE s.SectionID = ?
        GROUP BY s.SectionID, s.SectionName
    ", "Search", [$sectionID]);

    echo json_encode($query[0]);
}

function loadDailyLogs() {

    $page  = isset($_POST["page"]) ? (int)$_POST["page"] : 1;
    $limit = 5;

    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $limit;

    // Count TODAY rows only
    $totalQuery = execsqlSRS("
        SELECT COUNT(*) as total
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
    ", "Search", []);

    $totalRows  = $totalQuery[0]["total"];
    $totalPages = ceil($totalRows / $limit);

    // Fetch TODAY logs only
    $sql = "
        SELECT 
            l.id_number,
            l.college,
            l.course,
            s.SectionName,
            l.checkin_time,
            l.checkout_time
        FROM Library_logs l
        LEFT JOIN LibrarySection s
            ON l.library = s.SectionID
        WHERE CAST(l.checkin_time AS DATE) = CAST(GETDATE() AS DATE)
        ORDER BY l.checkin_time DESC
        OFFSET $offset ROWS
        FETCH NEXT $limit ROWS ONLY
    ";

    $logs = execsqlSRS($sql, "Search", []);

    ob_start();

    foreach ($logs as $row) {

        $status = empty($row["checkout_time"])
            ? "<span class='badge bg-success-subtle text-success rounded-pill px-3'>Active</span>"
            : "<span class='badge bg-secondary-subtle text-secondary rounded-pill px-3'>Completed</span>";

        echo "<tr>";
        echo "<td class='px-4 fw-semibold'>" . htmlspecialchars($row["id_number"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["college"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["course"]) . "</td>";
        echo "<td><span class='badge bg-light text-dark border'>" 
                . htmlspecialchars($row["SectionName"] ?? '-') . 
             "</span></td>";
        echo "<td>" . date('M j, Y g:i A', strtotime($row["checkin_time"])) . "</td>";
        echo "<td>" . ($row["checkout_time"] 
                ? date('M j, Y g:i A', strtotime($row["checkout_time"])) 
                : '-') . "</td>";
        echo "<td class='text-center'>$status</td>";
        echo "</tr>";
    }

    $rowsHTML = ob_get_clean();

    echo json_encode([
        "rows" => $rowsHTML,
        "totalPages" => $totalPages,
        "currentPage" => $page
    ]);
}

function loadMonthlyTrend() {

    $query = execsqlSRS("
        SELECT 
            FORMAT(checkin_time, 'MMM') as month,
            COUNT(*) as total
        FROM Library_logs
        WHERE checkin_time >= DATEADD(MONTH, -6, GETDATE())
        GROUP BY FORMAT(checkin_time, 'MMM'), MONTH(checkin_time)
        ORDER BY MONTH(checkin_time)
    ", "Search", []);

    echo json_encode($query);
}

function loadDepartmentOverview() {

    $query = execsqlSRS("
        SELECT college, COUNT(*) as total
        FROM Library_logs
        WHERE CAST(checkin_time AS DATE) = CAST(GETDATE() AS DATE)
        GROUP BY college
        ORDER BY total DESC
    ", "Search", []);

    echo json_encode($query);
}

