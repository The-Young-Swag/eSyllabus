<div class="container-fluid mt-4">

    <div class="card border-success shadow-sm rounded-4">

        <div class="card-header bg-success text-white rounded-top-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-bookmarks-fill me-2"></i>Library Section Management
                </h5>
                <button class="btn btn-light btn-sm rounded-pill" id="btnAddSection">
                    <i class="bi bi-plus-lg me-1"></i>Add Section
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Name</th>
                            <th class="text-center">Active</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sectionData"></tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
$(function () {

const BACKEND = "backend/bk_LibraryMenu/bk_libSections.php";

// ── HELPERS ───────────────────────────────────────────────────────────────

function openDynamicModal(res) {
    $("#dynamicModalTitle").text(res.title);
    $("#dynamicModalBody").html(res.body);
    $("#dynamicModalFooter").html(res.footer);
    $("#dynamicModal").modal("show");
}

// ── LOAD ──────────────────────────────────────────────────────────────────

function loadSections() {
    $.post(BACKEND, { request: "getSection" }, null, "json")
        .done(res => $("#sectionData").html(res.html))
        .fail(() => showToast("Failed to load sections.", "danger"));
}

// ── ADD / EDIT ────────────────────────────────────────────────────────────

function openSectionModal(sectionID = 0) {
    $.post(BACKEND, { request: "getSectionModal", sectionID }, null, "json")
        .done(res => {
            if (!res.success) { showToast(res.error || "Failed to load form.", "danger"); return; }
            openDynamicModal(res);
        })
        .fail(() => showToast("Failed to load form.", "danger"));
}

$(document).on("click", "#confirmSaveSection", function () {
    const btn = $(this);

    const payload = {
        request:     "saveSection",
        sectionID:   $("#modalSectionID").val(),
        sectionCode: $("#modalSectionCode").val().trim(),
        sectionName: $("#modalSectionName").val().trim(),
        description: $("#modalSectionDesc").val().trim(),
    };

    btn.prop("disabled", true).html('<i class="bi bi-hourglass-split me-1"></i>Saving…');
    $("#loadingSpinner").fadeIn(200);

    $.post(BACKEND, payload, null, "json")
        .done(res => {
            if (!res.success) { showToast(res.error || "Failed to save.", "danger"); return; }
            $("#dynamicModal").modal("hide");
            showToast("Section saved.", "success");
            loadSections();
        })
        .fail(() => showToast("Request failed.", "danger"))
        .always(() => {
            btn.prop("disabled", false).html('<i class="bi bi-check-lg me-1"></i>Save');
            $("#loadingSpinner").fadeOut(200);
        });
});

// ── DELETE ────────────────────────────────────────────────────────────────


$(document).on("click", "#confirmDeleteSection", function () {
    const btn       = $(this);
    const sectionID = btn.data("id");

    btn.prop("disabled", true).html('<i class="bi bi-hourglass-split me-1"></i>Deleting…');
    $("#loadingSpinner").fadeIn(200);
});

// ── TOGGLE ────────────────────────────────────────────────────────────────

$(document).on("change", ".toggle-section-status", function () {
    const id       = $(this).data("id");
    const isActive = $(this).is(":checked") ? 1 : 0;
    const toggle   = $(this);

    $.post(BACKEND, { request: "toggleStatus", sectionID: id, isActive }, null, "json")
        .done(res => {
            if (!res.success) {
                toggle.prop("checked", !isActive);
                showToast(res.error || "Failed to update status.", "danger");
            } else {
                showToast("Status updated.", "success");
            }
        })
        .fail(() => {
            toggle.prop("checked", !isActive);
            showToast("Request failed.", "danger");
        });
});

// ── BINDINGS ──────────────────────────────────────────────────────────────

$("#btnAddSection").on("click", () => openSectionModal());
$(document).on("click", ".btn-edit-section", function () { openSectionModal($(this).data("id")); });

// ── BOOT ──────────────────────────────────────────────────────────────────

loadSections();

});
</script>