<!-- DYNAMIC / UNIVERSAL MODAL -->
<div class="modal fade lib-modal" id="dynamicModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content position-relative" style="border:0; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.13), 0 4px 16px rgba(0,0,0,0.08);">
            <div class="modal-header position-relative" style="border:0; padding:24px 24px 0; background:linear-gradient(150deg, #e8faf3, #f0fdf9); border-bottom:1px solid #d1fae5; padding-bottom:16px;">
                <div class="w-100 text-center pe-3">
                    <h5 class="modal-title" id="dynamicModalTitle" style="font-size:0.95rem; font-weight:700; color:#064e3b; letter-spacing:-0.2px;"></h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" style="position:absolute; top:14px; right:16px; background:#f1f5f9; border:0; border-radius:50%; width:30px; height:30px; font-size:1rem; line-height:1; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background 0.15s, color 0.15s; z-index:10;">&times;</button>
            </div>
            <div class="modal-body" id="dynamicModalBody" style="padding:16px 24px;"></div>
            <div class="modal-footer justify-content-center gap-2" id="dynamicModalFooter" style="border:0; padding:0 24px 24px;"></div>
        </div>
    </div>
</div>

<!-- VIEW-ALL MODAL -->
<div class="modal fade lib-modal" id="viewAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content position-relative" style="border:0; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.13), 0 4px 16px rgba(0,0,0,0.08);">
            <div class="modal-header position-relative" style="border:0; padding:24px 24px 0; background:linear-gradient(150deg, #e8faf3, #f0fdf9); border-bottom:1px solid #d1fae5; padding-bottom:14px;">
                <div class="w-100 text-center pe-3">
                    <h5 class="modal-title mb-0" id="viewAllModalTitle" style="font-size:1rem; font-weight:700; color:#064e3b; letter-spacing:-0.2px;">Records</h5>
                    <small id="viewAllModalSubtitle" style="font-size:0.73rem; color:#6ee7b7; font-weight:600; letter-spacing:0.03em;"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" style="position:absolute; top:14px; right:16px; background:#f1f5f9; border:0; border-radius:50%; width:30px; height:30px; font-size:1rem; line-height:1; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background 0.15s, color 0.15s; z-index:10;">&times;</button>
            </div>
            <div class="modal-body px-0 pt-2" id="viewAllModalBody" style="padding:16px 0;"></div>
            <div class="modal-footer flex-column align-items-center gap-1" id="viewAllModalFooter" style="border:0; padding:0 24px 24px;"></div>
        </div>
    </div>
</div>

<!-- EXPORT MODAL (original structure, all styles inline except dynamic active-format) -->
<style>
    /* minimal – only for the JS‑toggled active state */
    .export-format-option.active-format {
        border-color: #10b981 !important;
        background: linear-gradient(135deg,#ecfdf5,#d1fae5) !important;
    }
</style>

<div class="modal fade lib-modal" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content position-relative" style="border:0; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.13), 0 4px 16px rgba(0,0,0,0.08);">

            <!-- Header -->
            <div class="modal-header position-relative text-center" style="border:0; padding:24px 24px 0; background:linear-gradient(150deg, #e8faf3, #f0fdf9); border-bottom:1px solid #d1fae5; padding-bottom:16px;">
                <div class="w-100 pe-3">
                    <div class="d-flex justify-content-center">
                        <div class="export-icon-badge" style="width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg, #d1fae5, #a7f3d0); display:inline-flex; align-items:center; justify-content:center; margin-bottom:8px; box-shadow:0 2px 8px rgba(6,78,59,0.12);">
                            <i class="fas fa-file-export" style="font-size:1.1rem; color:#047857;"></i>
                        </div>
                    </div>
                    <h5 class="modal-title" style="font-size:0.95rem; font-weight:700; color:#064e3b; letter-spacing:-0.2px;">Export Report</h5>
                    <p class="mb-0" style="font-size:0.75rem; color:#6ee7b7; font-weight:600;">Choose sections and format</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="position:absolute; top:14px; right:16px; background:#f1f5f9; border:0; border-radius:50%; width:30px; height:30px; font-size:1rem; line-height:1; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background 0.15s, color 0.15s; z-index:10;">&times;</button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding:16px 24px;">
                <span class="export-section-label" style="font-size:0.62rem; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#6ee7b7; margin-bottom:8px; display:block;">Sections to include</span>

                <!-- All tabs toggle -->
                <label class="export-check-row" for="exportCheckAll" style="display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; border:1px solid #e5e7eb; cursor:pointer; background:#fff; margin-bottom:4px;">
                    <input type="checkbox" id="exportCheckAll" checked style="width:16px; height:16px; flex-shrink:0; accent-color:#10b981;">
                    <div class="export-row-icon" style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; flex-shrink:0; background:#f1f5f9;">
                        <i class="fas fa-layer-group" style="color:#64748b; font-size:0.7rem;"></i>
                    </div>
                    <span class="export-row-label fw-bold" style="font-size:0.83rem; font-weight:500; color:#0f172a; font-weight:bold;">All Tabs</span>
                </label>

                <!-- Individual sections -->
                <div class="ps-2 mb-4" id="exportSectionIndividual">
                    <label class="export-check-row" for="exportChkLogs" style="display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; border:1px solid #e5e7eb; cursor:pointer; background:#fff; margin-bottom:4px;">
                        <input type="checkbox" class="export-section-check" id="exportChkLogs" value="logs" checked style="width:16px; height:16px; flex-shrink:0; accent-color:#10b981;">
                        <div class="export-row-icon" style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; flex-shrink:0; background:#f1f5f9;">
                            <i class="fas fa-clipboard-list" style="color:#64748b; font-size:0.7rem;"></i>
                        </div>
                        <span class="export-row-label" style="font-size:0.83rem; font-weight:500; color:#0f172a;">Logs</span>
                    </label>
                    <label class="export-check-row" for="exportChkUsers" style="display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; border:1px solid #e5e7eb; cursor:pointer; background:#fff; margin-bottom:4px;">
                        <input type="checkbox" class="export-section-check" id="exportChkUsers" value="users" checked style="width:16px; height:16px; flex-shrink:0; accent-color:#10b981;">
                        <div class="export-row-icon" style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; flex-shrink:0; background:#eff6ff;">
                            <i class="fas fa-users" style="color:#3b82f6; font-size:0.7rem;"></i>
                        </div>
                        <span class="export-row-label" style="font-size:0.83rem; font-weight:500; color:#0f172a;">Users</span>
                    </label>
                    <label class="export-check-row" for="exportChkColleges" style="display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; border:1px solid #e5e7eb; cursor:pointer; background:#fff; margin-bottom:4px;">
                        <input type="checkbox" class="export-section-check" id="exportChkColleges" value="colleges" checked style="width:16px; height:16px; flex-shrink:0; accent-color:#10b981;">
                        <div class="export-row-icon" style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; flex-shrink:0; background:#f0fdf4;">
                            <i class="fas fa-university" style="color:#16a34a; font-size:0.7rem;"></i>
                        </div>
                        <span class="export-row-label" style="font-size:0.83rem; font-weight:500; color:#0f172a;">Colleges</span>
                    </label>
                    <label class="export-check-row" for="exportChkCourses" style="display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; border:1px solid #e5e7eb; cursor:pointer; background:#fff; margin-bottom:4px;">
                        <input type="checkbox" class="export-section-check" id="exportChkCourses" value="courses" checked style="width:16px; height:16px; flex-shrink:0; accent-color:#10b981;">
                        <div class="export-row-icon" style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; flex-shrink:0; background:#fffbeb;">
                            <i class="fas fa-book-open" style="color:#d97706; font-size:0.7rem;"></i>
                        </div>
                        <span class="export-row-label" style="font-size:0.83rem; font-weight:500; color:#0f172a;">Courses</span>
                    </label>
                    <label class="export-check-row" for="exportChkDemo" style="display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; border:1px solid #e5e7eb; cursor:pointer; background:#fff; margin-bottom:4px;">
                        <input type="checkbox" class="export-section-check" id="exportChkDemo" value="demographics" checked style="width:16px; height:16px; flex-shrink:0; accent-color:#10b981;">
                        <div class="export-row-icon" style="width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; flex-shrink:0; background:#fff1f2;">
                            <i class="fas fa-chart-bar" style="color:#e11d48; font-size:0.7rem;"></i>
                        </div>
                        <span class="export-row-label" style="font-size:0.83rem; font-weight:500; color:#0f172a;">Demographics</span>
                    </label>
                </div>

                <span class="export-section-label" style="font-size:0.62rem; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#6ee7b7; margin-bottom:8px; display:block;">Export format</span>

                <div class="d-flex gap-2">
                    <label class="export-format-option" data-format="pdf" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; padding:14px 0; border-radius:12px; border:1.5px solid #e5e7eb; cursor:pointer; min-height:76px; background:#fff;">
                        <input type="radio" name="exportFormat" value="pdf" class="d-none">
                        <i class="fas fa-file-pdf text-danger" style="font-size:1.4rem;"></i>
                        <span class="export-fmt-label" style="font-size:0.78rem; font-weight:700; color:#064e3b;">PDF</span>
                    </label>
                    <label class="export-format-option active-format" data-format="xlsx" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; padding:14px 0; border-radius:12px; border:1.5px solid #e5e7eb; cursor:pointer; min-height:76px; background:#fff;">
                        <input type="radio" name="exportFormat" value="xlsx" class="d-none" checked>
                        <i class="fas fa-file-excel text-success" style="font-size:1.4rem;"></i>
                        <span class="export-fmt-label" style="font-size:0.78rem; font-weight:700; color:#064e3b;">Excel</span>
                    </label>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer gap-2" style="border:0; padding:0 24px 24px;">
                <button type="button" class="btn-cancel" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius:10px; border:1.5px solid #d1d5db; background:#fff; color:#374151; font-size:0.83rem; font-weight:600; padding:10px 0; flex:1; transition:background 0.15s;">Cancel</button>
                <button type="button" class="btn-export" id="exportConfirmBtn" style="border-radius:10px; border:0; background:#064e3b; color:#fff; font-size:0.83rem; font-weight:600; padding:10px 0; flex:1; transition:background 0.15s, box-shadow 0.15s;">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>

        </div>
    </div>
</div>