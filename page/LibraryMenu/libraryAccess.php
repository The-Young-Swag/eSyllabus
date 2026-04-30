<div class="container-fluid mt-4">

    <div class="card border-success shadow-sm rounded-4">

        <div class="card-header bg-success text-white rounded-top-4">
            <h5 class="mb-0">
                <i class="fas fa-university me-2"></i>Library Access Management
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Emp ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Library Section</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accessData"></tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script>
$(function () {

const BACKEND = "backend/bk_LibraryMenu/bk_libraryAccess.php";

function loadAccess() {
    $.post(BACKEND, { request: "getAccess" }, null, "json")
        .done(res => $("#accessData").html(res.html))
        .fail(err => console.error("Access load failed:", err));
}

$(document).on("click", ".btn-save-access", function () {
    const btn = $(this);
    const userID = btn.data("userid");
    const sectionID = $(`.select-access[data-userid="${userID}"]`).val();

    btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i>');
    $("#loadingSpinner").fadeIn(200);

    $.post(BACKEND, { request: "assignAccess", userID, sectionID }, null, "json")
        .done(res => {
            if (res.success) showToast("Library access updated!", "success");
            else showToast(res.error || "Failed to update access.", "danger");
        })
        .fail(() => showToast("Request failed.", "danger"))
        .always(() => {
            btn.prop("disabled", false).html('Save');
            $("#loadingSpinner").fadeOut(200);
        });
});

loadAccess();

});
</script>