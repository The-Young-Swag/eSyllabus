<!-- 
     MODAL STYLES — all inline-JS CSS moved here
 -->
<style>

/*  Shared modal chrome  */
.lib-modal .modal-content {
    border: 0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.13), 0 4px 16px rgba(0,0,0,.08);
}
.lib-modal .modal-header {
    border: 0;
    padding: 24px 24px 0;
}
.lib-modal .modal-body {
    padding: 16px 24px;
}
.lib-modal .modal-footer {
    border: 0;
    padding: 0 24px 24px;
}
.lib-modal .close {
    position: absolute;
    top: 14px;
    right: 16px;
    background: #f1f5f9;
    border: 0;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    font-size: 1rem;
    line-height: 1;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background .15s, color .15s;
    z-index: 10;
}
.lib-modal .close:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.lib-modal .modal-title {
    font-size: .95rem;
    font-weight: 700;
    color: #064e3b;
    letter-spacing: -.2px;
}

/*  Dynamic / universal modal  */
#dynamicModal .modal-header {
    background: linear-gradient(150deg, #e8faf3, #f0fdf9);
    border-bottom: 1px solid #d1fae5;
    padding-bottom: 16px;
}

/*  View-all modal  */
#viewAllModal .modal-header {
    background: linear-gradient(150deg, #e8faf3, #f0fdf9);
    border-bottom: 1px solid #d1fae5;
    padding-bottom: 14px;
}
#viewAllModal .modal-title { font-size: 1rem; }
#viewAllModal #viewAllModalSubtitle { font-size: .73rem; color: #6ee7b7; font-weight: 600; letter-spacing: .03em; }

/*  Export modal  */
#exportModal .modal-content  { border-radius: 20px; }
#exportModal .modal-header   { background: linear-gradient(150deg, #e8faf3, #f0fdf9); border-bottom: 1px solid #d1fae5; padding-bottom: 16px; }

/* Export icon badge */
.export-icon-badge {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    box-shadow: 0 2px 8px rgba(6,78,59,.12);
}
.export-icon-badge i { font-size: 1.1rem; color: #047857; }

/* Export section label */
.export-section-label {
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #6ee7b7;
    margin-bottom: 8px;
    display: block;
}

/* Export section rows */
.export-check-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: background .12s, border-color .12s;
    margin-bottom: 4px;
    background: #fff;
}
.export-check-row:hover { background: #f0fdf4; border-color: #6ee7b7; }
.export-check-row input[type=checkbox] { width: 16px; height: 16px; flex-shrink: 0; accent-color: #10b981; }
.export-check-row .export-row-icon {
    width: 24px; height: 24px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem;
    flex-shrink: 0;
}
.export-check-row .export-row-label { font-size: .83rem; font-weight: 500; color: #0f172a; }

/* Export format tiles */
.export-format-option {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 14px 0;
    border-radius: 12px;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    min-height: 76px;
    transition: all .15s;
    background: #fff;
}
.export-format-option:hover:not(.active-format) { background: #f0fdf4; border-color: #6ee7b7; }
.export-format-option.active-format { border-color: #10b981 !important; background: linear-gradient(135deg,#ecfdf5,#d1fae5); }
.export-format-option .export-fmt-label { font-size: .78rem; font-weight: 700; color: #064e3b; }

/* Export footer buttons */
#exportModal .btn-cancel {
    border-radius: 10px;
    border: 1.5px solid #d1d5db;
    background: #fff;
    color: #374151;
    font-size: .83rem;
    font-weight: 600;
    padding: 10px 0;
    flex: 1;
    transition: background .15s;
}
#exportModal .btn-cancel:hover { background: #f3f4f6; }
#exportModal .btn-export {
    border-radius: 10px;
    border: 0;
    background: #064e3b;
    color: #fff;
    font-size: .83rem;
    font-weight: 600;
    padding: 10px 0;
    flex: 1;
    transition: background .15s, box-shadow .15s;
}
#exportModal .btn-export:hover { background: #047857; box-shadow: 0 4px 14px rgba(6,78,59,.22); }

/*  Attendance reminder cards (replaces all inline JS CSS)  */
.lib-reminder {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 16px;
    padding: 14px 16px;
    border-radius: 14px;
    border: 1px solid transparent;
}
.lib-reminder__icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    border: 1.5px solid transparent;
}
.lib-reminder__tag {
    font-size: .67rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.lib-reminder__text {
    font-size: .82rem;
    font-weight: 500;
    line-height: 1.45;
}

/* Check-in (amber) */
.lib-reminder--in {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-color: #fde68a;
    box-shadow: 0 2px 12px rgba(245,158,11,.12);
}
.lib-reminder--in .lib-reminder__icon { background: #fef9c3; border-color: #fde68a; }
.lib-reminder--in .lib-reminder__tag  { color: #d97706; }
.lib-reminder--in .lib-reminder__text { color: #92400e; }

/* Check-out (green) */
.lib-reminder--out {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-color: #6ee7b7;
    box-shadow: 0 2px 12px rgba(16,185,129,.12);
}
.lib-reminder--out .lib-reminder__icon { background: #dcfce7; border-color: #6ee7b7; }
.lib-reminder--out .lib-reminder__tag  { color: #059669; }
.lib-reminder--out .lib-reminder__text { color: #065f46; }

</style>


<!-- 
     DYNAMIC / UNIVERSAL MODAL
 -->
<div class="modal fade lib-modal" id="dynamicModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content position-relative">

            <div class="modal-header position-relative">
                <div class="w-100 text-center pe-3">
                    <h5 class="modal-title" id="dynamicModalTitle"></h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="dynamicModalBody"></div>

            <div class="modal-footer justify-content-center gap-2" id="dynamicModalFooter"></div>

        </div>
    </div>
</div>


<!-- 
     VIEW-ALL MODAL
 -->
<div class="modal fade lib-modal" id="viewAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content position-relative">

            <div class="modal-header position-relative">
                <div class="w-100 text-center pe-3">
                    <h5 class="modal-title mb-0" id="viewAllModalTitle">Records</h5>
                    <small id="viewAllModalSubtitle"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body px-0 pt-2" id="viewAllModalBody"></div>

            <div class="modal-footer flex-column align-items-center gap-1" id="viewAllModalFooter"></div>

        </div>
    </div>
</div>


<!-- 
     EXPORT MODAL
 -->
 <div class="modal fade lib-modal" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content position-relative">

            <!-- Header -->
            <div class="modal-header position-relative text-center">
                <div class="w-100 pe-3">
                    <div class="d-flex justify-content-center">
                        <div class="export-icon-badge">
                            <i class="fas fa-file-export"></i>
                        </div>
                    </div>
                    <h5 class="modal-title">Export Report</h5>
                    <p class="mb-0" style="font-size:.75rem;color:#6ee7b7;font-weight:600;">Choose sections and format</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <span class="export-section-label">Sections to include</span>

                <!-- All tabs toggle -->
                <label class="export-check-row" for="exportCheckAll">
                    <input type="checkbox" id="exportCheckAll" checked>
                    <div class="export-row-icon" style="background:#f1f5f9;">
                        <i class="fas fa-layer-group" style="color:#64748b;font-size:.7rem;"></i>
                    </div>
                    <span class="export-row-label fw-bold">All Tabs</span>
                </label>

                <!-- Individual sections -->
                <div class="ps-2 mb-4" id="exportSectionIndividual">
                    <label class="export-check-row" for="exportChkLogs">
                        <input type="checkbox" class="export-section-check" id="exportChkLogs" value="logs" checked>
                        <div class="export-row-icon" style="background:#f1f5f9;">
                            <i class="fas fa-clipboard-list" style="color:#64748b;font-size:.7rem;"></i>
                        </div>
                        <span class="export-row-label">Logs</span>
                    </label>
                    <label class="export-check-row" for="exportChkUsers">
                        <input type="checkbox" class="export-section-check" id="exportChkUsers" value="users" checked>
                        <div class="export-row-icon" style="background:#eff6ff;">
                            <i class="fas fa-users" style="color:#3b82f6;font-size:.7rem;"></i>
                        </div>
                        <span class="export-row-label">Users</span>
                    </label>
                    <label class="export-check-row" for="exportChkColleges">
                        <input type="checkbox" class="export-section-check" id="exportChkColleges" value="colleges" checked>
                        <div class="export-row-icon" style="background:#f0fdf4;">
                            <i class="fas fa-university" style="color:#16a34a;font-size:.7rem;"></i>
                        </div>
                        <span class="export-row-label">Colleges</span>
                    </label>
                    <label class="export-check-row" for="exportChkCourses">
                        <input type="checkbox" class="export-section-check" id="exportChkCourses" value="courses" checked>
                        <div class="export-row-icon" style="background:#fffbeb;">
                            <i class="fas fa-book-open" style="color:#d97706;font-size:.7rem;"></i>
                        </div>
                        <span class="export-row-label">Courses</span>
                    </label>
                    <label class="export-check-row" for="exportChkDemo">
                        <input type="checkbox" class="export-section-check" id="exportChkDemo" value="demographics" checked>
                        <div class="export-row-icon" style="background:#fff1f2;">
                            <i class="fas fa-chart-bar" style="color:#e11d48;font-size:.7rem;"></i>
                        </div>
                        <span class="export-row-label">Demographics</span>
                    </label>
                </div>

                <span class="export-section-label">Export format</span>

                <div class="d-flex gap-2">
                    <label class="export-format-option" data-format="pdf">
                        <input type="radio" name="exportFormat" value="pdf" class="d-none">
                        <i class="fas fa-file-pdf text-danger" style="font-size:1.4rem;"></i>
                        <span class="export-fmt-label">PDF</span>
                    </label>
                    <label class="export-format-option active-format" data-format="xlsx">
                        <input type="radio" name="exportFormat" value="xlsx" class="d-none" checked>
                        <i class="fas fa-file-excel text-success" style="font-size:1.4rem;"></i>
                        <span class="export-fmt-label">Excel</span>
                    </label>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer gap-2">
                <button type="button" class="btn-cancel" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-export" id="exportConfirmBtn">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>

        </div>
    </div>
</div>