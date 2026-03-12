<div class="container-fluid mt-4">

    <div class="card border-success shadow-sm rounded-4">

        <!-- Header -->
        <div class="card-header bg-success text-white rounded-top-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sections</h5>
                <button class="btn btn-light btn-sm rounded-pill">
                    + Add Section
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>SectionID</th>
                            <th>SectionCode</th>
                            <th>Description</th>
                            <th>SectionName</th>
                            <th>IsActive</th>
                            <th>Date Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="sectionData">
                        <tr>
                            <td class="fw-semibold">SEC-001</td>
                            <td>Human Resources</td>
                            <td class="text-muted">Handles employee management</td>
                            <td>
                                <span class="badge bg-success rounded-pill">
                                    Active
                                </span>
                            </td>
                            <td>2026-03-09</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="fw-semibold">SEC-002</td>
                            <td>Finance</td>
                            <td class="text-muted">Manages company funds</td>
                            <td>
                                <span class="badge bg-secondary rounded-pill">
                                    Inactive
                                </span>
                            </td>
                            <td>2026-03-08</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){

const BACKEND = "backend/bk_LibraryMenu/bk_libSections.php";

$.post(BACKEND, { request:"getSection" }, null, "json")
    .done(res => $("#sectionData").html(res.html))
    .fail(err => console.error("Section load failed:", err));
});
</script>