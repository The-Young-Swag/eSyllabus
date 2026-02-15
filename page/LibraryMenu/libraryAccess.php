<?php
include "../../db/dbconnection.php";

// Get all active (non-deleted) users
$users = execsqlSRS("SELECT 
                        UserID,
                        EmpID,
                        Name,
                        EmailAddress
                    FROM Sys_UserAccount
                    WHERE IsDeleted = 0
                    ORDER BY Name", "Search", []);

// Get all active library sections
$sections = execsqlSRS("SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName", "Search", []);

// Get current library access
$accessRows = execsqlSRS("SELECT UserID, SectionID FROM LibraryAccess", "Search", []);
$userAccess = [];
foreach ($accessRows as $ar) {
    $userAccess[$ar['UserID']] = $ar['SectionID'];
}
?>

<div class="container-fluid mt-3">
    <h4 class="fw-bold mb-3"><i class="fas fa-university text-primary mr-2"></i> Library Access Management</h4>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Emp ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Library Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($users)): ?>
                    <?php foreach($users as $i => $user): 
                        $currentSection = $userAccess[$user['UserID']] ?? '';
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($user['EmpID']) ?></td>
                            <td><?= htmlspecialchars($user['Name']) ?></td>
                            <td><?= htmlspecialchars($user['EmailAddress']) ?></td>
                            <td>
                                <select class="form-control select-access" data-userid="<?= $user['UserID'] ?>">
                                    <option value="">-- None --</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?= $sec['SectionID'] ?>" <?= $sec['SectionID'] == $currentSection ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sec['SectionName']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-save-access" data-userid="<?= $user['UserID'] ?>">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No active users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Save button click
    $(document).off('click', '.btn-save-access').on('click', '.btn-save-access', function() {
        const btn = $(this);
        const userID = btn.data('userid');
        const sectionID = $(`.select-access[data-userid="${userID}"]`).val(); // empty = null

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.post("backend/bk_LibraryMenu/bk_libraryAccess.php", {
            request: 'assignAccess',
            userID: userID,
            sectionID: sectionID
        }, function(resp) {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            if(resp === 'SUCCESS') {
                showToast('Library access updated!', 'success');
            } else {
                alert('Failed to update access: ' + resp);
            }
        });
    });
});
</script>
