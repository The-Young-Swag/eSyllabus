<!-- UNIVERSAL VALIDATION MODAL -->
<div class="modal fade" id="dynamicModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0 position-relative">
                <div class="w-100 text-center pe-4">
                    <h5 class="modal-title fw-bold" id="dynamicModalTitle"></h5>
                    <small class="text-muted d-block">Please verify the information before proceeding</small>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pt-3" id="dynamicModalBody"></div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2" id="dynamicModalFooter"></div>
        </div>
    </div>
</div>

<!-- VIEW ALL MODAL -->
<div class="modal fade" id="viewAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-1">
                <div class="w-100 text-center pe-4">
                    <h5 class="modal-title fw-bold mb-0" id="viewAllModalTitle">Records</h5>
                    <small class="text-muted" id="viewAllModalSubtitle"></small>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pt-2 px-0" id="viewAllModalBody"></div>
            <div class="modal-footer border-0 flex-column align-items-center gap-1 pt-0" id="viewAllModalFooter"></div>
        </div>
    </div>
</div>

<!-- EXPORT MODAL -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="w-100 text-center pe-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle mb-2"
                         style="width:44px;height:44px;">
                        <i class="fas fa-file-export text-primary" style="font-size:1.1rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-0 fs-6">Export Report</h6>
                    <small class="text-muted">Choose sections and format</small>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>

            <!-- Body -->
            <div class="modal-body px-4 pt-3 pb-2">

                <!-- Section label -->
                <p class="text-uppercase fw-semibold mb-2"
                   style="font-size:.65rem;letter-spacing:.1em;color:#9ca3af;">Sections to include</p>

                <!-- All Tabs toggle — checked because all individual boxes start checked -->
                <label class="d-flex align-items-center gap-3 px-3 py-2 rounded-2 border mb-1"
                       style="cursor:pointer;background:#f9fafb;">
                    <input type="checkbox" class="form-check-input m-0 flex-shrink-0"
                           id="exportCheckAll" checked style="width:16px;height:16px;">
                    <span class="small fw-semibold text-dark">All Tabs</span>
                </label>

                <!-- Individual section checks -->
                <div class="d-flex flex-column gap-1 ps-2 mb-4" id="exportSectionIndividual">

                    <label class="d-flex align-items-center gap-3 px-3 py-2 rounded-2 border"
                           style="cursor:pointer;" for="exportChkLogs">
                        <input type="checkbox" class="form-check-input m-0 flex-shrink-0 export-section-check"
                               id="exportChkLogs" value="logs" checked style="width:16px;height:16px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-secondary-subtle"
                              style="width:22px;height:22px;flex-shrink:0;">
                            <i class="bi bi-journal-text text-secondary" style="font-size:.65rem;"></i>
                        </span>
                        <span class="small text-dark">Logs</span>
                    </label>

                    <label class="d-flex align-items-center gap-3 px-3 py-2 rounded-2 border"
                           style="cursor:pointer;" for="exportChkUsers">
                        <input type="checkbox" class="form-check-input m-0 flex-shrink-0 export-section-check"
                               id="exportChkUsers" value="users" checked style="width:16px;height:16px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle"
                              style="width:22px;height:22px;flex-shrink:0;">
                            <i class="bi bi-people-fill text-primary" style="font-size:.65rem;"></i>
                        </span>
                        <span class="small text-dark">Users</span>
                    </label>

                    <label class="d-flex align-items-center gap-3 px-3 py-2 rounded-2 border"
                           style="cursor:pointer;" for="exportChkColleges">
                        <input type="checkbox" class="form-check-input m-0 flex-shrink-0 export-section-check"
                               id="exportChkColleges" value="colleges" checked style="width:16px;height:16px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-success-subtle"
                              style="width:22px;height:22px;flex-shrink:0;">
                            <i class="bi bi-building-fill text-success" style="font-size:.65rem;"></i>
                        </span>
                        <span class="small text-dark">Colleges</span>
                    </label>

                    <label class="d-flex align-items-center gap-3 px-3 py-2 rounded-2 border"
                           style="cursor:pointer;" for="exportChkCourses">
                        <input type="checkbox" class="form-check-input m-0 flex-shrink-0 export-section-check"
                               id="exportChkCourses" value="courses" checked style="width:16px;height:16px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-warning-subtle"
                              style="width:22px;height:22px;flex-shrink:0;">
                            <i class="bi bi-journal-bookmark-fill text-warning" style="font-size:.65rem;"></i>
                        </span>
                        <span class="small text-dark">Courses</span>
                    </label>

                    <label class="d-flex align-items-center gap-3 px-3 py-2 rounded-2 border"
                           style="cursor:pointer;" for="exportChkDemo">
                        <input type="checkbox" class="form-check-input m-0 flex-shrink-0 export-section-check"
                               id="exportChkDemo" value="demographics" checked style="width:16px;height:16px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-danger-subtle"
                              style="width:22px;height:22px;flex-shrink:0;">
                            <i class="bi bi-bar-chart-fill text-danger" style="font-size:.65rem;"></i>
                        </span>
                        <span class="small text-dark">Demographics</span>
                    </label>

                </div>

                <!-- Format label -->
                <p class="text-uppercase fw-semibold mb-2"
                   style="font-size:.65rem;letter-spacing:.1em;color:#9ca3af;">Export format</p>

                <!-- Format tiles -->
                <div class="d-flex gap-2">
                    <label class="flex-fill d-flex flex-column align-items-center justify-content-center
                                  gap-1 py-3 rounded-3 border export-format-option"
                           data-format="pdf" style="cursor:pointer;min-height:72px;transition:all .15s;">
                        <input type="radio" name="exportFormat" value="pdf" class="d-none">
                        <i class="fas fa-file-pdf text-danger" style="font-size:1.4rem;line-height:1;"></i>
                        <span class="fw-semibold text-dark" style="font-size:.78rem;">PDF</span>
                    </label>
                    <label class="flex-fill d-flex flex-column align-items-center justify-content-center
                                  gap-1 py-3 rounded-3 border export-format-option active-format"
                           data-format="xlsx" style="cursor:pointer;min-height:72px;transition:all .15s;">
                        <input type="radio" name="exportFormat" value="xlsx" class="d-none" checked>
                        <i class="fas fa-file-excel text-success" style="font-size:1.4rem;line-height:1;"></i>
                        <span class="fw-semibold text-dark" style="font-size:.78rem;">Excel</span>
                    </label>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button class="btn btn-sm btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-sm btn-primary fw-semibold flex-fill" id="exportConfirmBtn">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>

        </div>
    </div>
</div>

<style>
.export-format-option.active-format {
    border-color: #3b82f6 !important;
    background: #eff6ff;
}
.export-format-option:hover:not(.active-format) {
    background: #f9fafb;
}
#exportSectionIndividual label:hover,
label[for="exportCheckAll"]:hover {
    background: #f0f4ff !important;
}
</style>