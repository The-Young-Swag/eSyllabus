<!-- UNIVERSAL VALIDATION MODAL -->
<div class="modal fade" id="dynamicModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">

            <!-- Dynamic Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold" id="dynamicModalTitle"></h5>
                    <small class="text-muted d-block">
                        Please verify the information before proceeding
                    </small>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Dynamic Body -->
            <div class="modal-body pt-3" id="dynamicModalBody">
            </div>

            <!-- Dynamic Footer -->
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2"
                 id="dynamicModalFooter">
            </div>

        </div>
    </div>
</div>



<!-- ============================================================
     LIBRARY ANALYTICS — SHARED MODALS
     Include this file once per page that uses analytics.
     ============================================================ -->

<!-- VIEW ALL MODAL -->
<div class="modal fade" id="viewAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold" id="viewAllModalTitle">Records</h5>
                    <small class="text-muted d-block" id="viewAllModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3 px-0" id="viewAllModalBody">
                <!-- Table injected here -->
            </div>
            <div class="modal-footer border-0 flex-column align-items-center gap-1 pt-0" id="viewAllModalFooter">
                <!-- Pagination injected here -->
            </div>
        </div>
    </div>
</div>


<!-- EXPORT MODAL -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold">Export Report</h5>
                    <small class="text-muted d-block">Choose sections and format to export</small>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 px-4">

                <!-- Section selector -->
                <p class="small fw-semibold text-uppercase text-muted mb-2" style="letter-spacing:.05em;">Sections to include</p>
                <div class="d-flex flex-column gap-2 mb-4">
                    <label class="d-flex align-items-center gap-2 p-2 rounded-2 border bg-light-subtle" style="cursor:pointer;">
                        <input type="checkbox" class="form-check-input m-0" id="exportCheckAll">
                        <span class="small fw-semibold">All Sections</span>
                    </label>
                    <div class="ps-3 d-flex flex-column gap-2" id="exportSectionIndividual">
                        <label class="d-flex align-items-center gap-2 p-2 rounded-2 border" style="cursor:pointer;">
                            <input type="checkbox" class="form-check-input m-0 export-section-check" value="users" checked>
                            <i class="bi bi-people-fill text-primary small"></i>
                            <span class="small">Users</span>
                        </label>
                        <label class="d-flex align-items-center gap-2 p-2 rounded-2 border" style="cursor:pointer;">
                            <input type="checkbox" class="form-check-input m-0 export-section-check" value="colleges" checked>
                            <i class="bi bi-building-fill text-success small"></i>
                            <span class="small">Colleges</span>
                        </label>
                        <label class="d-flex align-items-center gap-2 p-2 rounded-2 border" style="cursor:pointer;">
                            <input type="checkbox" class="form-check-input m-0 export-section-check" value="courses" checked>
                            <i class="bi bi-journal-bookmark-fill text-warning small"></i>
                            <span class="small">Courses</span>
                        </label>
                        <label class="d-flex align-items-center gap-2 p-2 rounded-2 border" style="cursor:pointer;">
                            <input type="checkbox" class="form-check-input m-0 export-section-check" value="demographics" checked>
                            <i class="bi bi-bar-chart-fill text-danger small"></i>
                            <span class="small">Demographics</span>
                        </label>
                    </div>
                </div>

                <!-- Format selector -->
                <p class="small fw-semibold text-uppercase text-muted mb-2" style="letter-spacing:.05em;">Format</p>
                <div class="d-flex gap-2">
                    <label class="flex-fill d-flex align-items-center justify-content-center gap-2 p-3 rounded-2 border export-format-option" data-format="pdf" style="cursor:pointer;transition:all .15s;">
                        <input type="radio" name="exportFormat" value="pdf" class="d-none">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <span class="small fw-semibold">PDF</span>
                    </label>
                    <label class="flex-fill d-flex align-items-center justify-content-center gap-2 p-3 rounded-2 border export-format-option active-format" data-format="xlsx" style="cursor:pointer;transition:all .15s;">
                        <input type="radio" name="exportFormat" value="xlsx" class="d-none" checked>
                        <i class="fas fa-file-excel text-success"></i>
                        <span class="small fw-semibold">Excel</span>
                    </label>
                </div>

            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-sm btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary fw-semibold px-4" id="exportConfirmBtn">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.export-format-option.active-format { border-color: #3b82f6 !important; background: #eff6ff; }
</style>