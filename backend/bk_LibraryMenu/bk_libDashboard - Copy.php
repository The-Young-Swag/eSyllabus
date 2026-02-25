<?php

switch ($request) {

case "kpiData":
    <!-- KPI Cards Row -->
                    <h3 id="lib1Count"class="fw-bold text-success">153</h3>
					<h3 id="lib2Count"class="fw-bold text-success">153</h3>
					<h3 id="lib3Count"class="fw-bold text-success">153</h3>
					<h3 id="lib4Count"class="fw-bold text-success">153</h3>

case "logTable":
$logs =   execsqlSRS("SELECT TOP (1000) [id]
      ,[id_number]
      ,[name]
      ,[college]
      ,[course]
      ,[library]
      ,[checkin_time]
      ,[checkout_time]
      ,[sex]
      ,[classification]
  FROM [eSyllabus].[dbo].[Library_logs]");

    <!-- Table -->
                <tbody id="dailyLogs">
              <?php foreach ($topDuration as $class => $users): ?>
                            <?php foreach ($users as $id => $data): ?>
                                <tr>
									<td class="fw-semibold"><?= htmlspecialchars($data['id_number']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($data['name']) ?></td>
									<td class="fw-semibold"><?= htmlspecialchars($colllege) ?></td>
									<td><?= htmlspecialchars($data['library']) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= $class ?></span></td>
                                    <td><?= htmlspecialchars($data['library']) ?></td>
                                    <td class="text-end"><?= round($data['minutes']) ?></td>
                                    <td class="text-end"><?= date('M j, Y g:i A', strtotime($data['last_checkin'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                </tbody>
        <!-- Pagination -->
        <nav class="mt-2">
            <ul class="pagination pagination-sm justify-content-center mb-0"></ul>
        </nav>


case "departmentLogs":
                <div id="userChart" class="card-body">



case "departmentLogs":
                <div id="departmentChart" class="card-body">


}