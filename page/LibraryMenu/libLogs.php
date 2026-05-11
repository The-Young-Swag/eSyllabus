<div class="container-fluid py-4" style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; max-width: 1100px; margin: 0 auto;">
    
    <style>
        @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        @keyframes pulseDot{0%,100%{opacity:.9}50%{opacity:.35}}
        @keyframes breatheBtn{0%,100%{box-shadow:0 0 0 0 rgba(6,78,59,.3)}50%{box-shadow:0 0 0 10px rgba(6,78,59,0)}}
        @keyframes floatNudge{0%,100%{transform:translateY(-50%) translateX(0)}50%{transform:translateY(-50%) translateX(-4px)}}
    </style>

    <!-- HEADER -->
    <div style="display:flex;align-items:center;gap:14px;padding:18px 22px;background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);margin-bottom:16px;border:1px solid #f1f5f9;animation:fadeUp .45s ease-out;">
        <div style="width:11px;height:11px;border-radius:50%;background:#10b981;flex-shrink:0;animation:pulseDot 2.2s ease-in-out infinite;"></div>
        <div>
            <div style="font-size:1.05rem;font-weight:700;color:#0f172a;letter-spacing:-.3px;line-height:1.2;">Library Attendance Dashboard</div>
            <div style="font-size:.76rem;color:#64748b;font-weight:450;">Real-time monitoring of today's attendance activity</div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div style="background:#fff;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.06),0 1px 4px rgba(0,0,0,.03);border:1px solid #f1f5f9;overflow:hidden;margin-bottom:16px;animation:fadeUp .5s ease-out .05s both;">
        <div style="padding:24px 26px;background:linear-gradient(175deg,#fafdfb 0%,#f8fcf9 35%,#f9fbfa 65%,#fafdfb 100%);">
            
            <!-- TOP ROW -->
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid #e8f0eb;">
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#fff;border-radius:14px;border:1px solid #d1fae5;box-shadow:0 1px 3px rgba(0,0,0,.04);min-width:250px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#d1fae5,#a7f3d0);display:flex;align-items:center;justify-content:center;color:#047857;font-size:.85rem;flex-shrink:0;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <div style="font-size:.58rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#6ee7b7;margin-bottom:2px;">Library Section</div>
                        <div id="currentLibraryDisplay" style="font-size:.88rem;font-weight:700;color:#064e3b;letter-spacing:-.2px;white-space:nowrap;">Main Library</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;padding:12px 18px;background:#fff;border-radius:14px;border:1px solid #d1fae5;box-shadow:0 1px 3px rgba(0,0,0,.04);min-width:250px;">
                    <span style="font-size:.58rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#6ee7b7;margin-bottom:4px;">Current Date &amp; Time</span>
                    <span id="kpiCurrentTime" style="font-size:.95rem;font-weight:650;color:#064e3b;letter-spacing:-.2px;">—</span>
                </div>
            </div>

            <!-- FORM -->
            <form id="logForm" autocomplete="off">
                <div style="margin-bottom:10px;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#4b7c6b;">
                    Students &amp; Employees — use your ID number below
                </div>
                <div style="display:flex;align-items:stretch;border-radius:14px;border:2px solid #d1fae5;background:#fff;overflow:hidden;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.04);">
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;padding:0 14px;background:#f9fefb;border-right:1.5px solid #d1fae5;min-width:58px;flex-shrink:0;">
                        <i class="fas fa-id-card" style="color:#10b981;font-size:.9rem;"></i>
                        <span style="font-size:.48rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#6ee7b7;white-space:nowrap;">ID No.</span>
                    </div>
                    <input type="password" id="inputIDNumber" placeholder="Enter student/employee number here" autocomplete="new-password" spellcheck="false"
                        style="flex:1;border:none;outline:none;background:transparent;padding:14px 15px;font-size:.92rem;color:#0f172a;letter-spacing:.2px;font-family:inherit;">
                    <button type="button" id="toggleIdVisibility" style="background:transparent;border:none;outline:none;padding:0 14px;color:#94a3b8;cursor:pointer;font-size:.9rem;flex-shrink:0;transition:color .15s;">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <hr style="margin:22px 0;border:none;height:1px;background:#e4efe9;">
                
                <!-- Animated confirm button -->
                <button type="submit" style="width:100%;padding:14px;background:#064e3b;border:none;border-radius:12px;font-size:.88rem;font-weight:650;color:#fff;letter-spacing:.02em;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:8px;font-family:inherit;box-shadow:0 2px 8px rgba(6,78,59,.15);animation:breatheBtn 2.8s ease-in-out infinite;">
                    <i class="fas fa-check-circle"></i> Confirm Access
                </button>
            </form>

            <!-- GUEST SECTION with thought float box -->
            <div style="margin-top:22px;border-top:1px dashed #c8e6d8;padding-top:14px;">
                <details style="font-size:.76rem;color:#4b5563;font-family:inherit;">
                    <summary style="cursor:pointer;font-weight:600;color:#6b7280;padding:8px 0;outline:none;user-select:none;list-style:none;">
                        <span style="text-decoration:none;border-bottom:1px dashed #9ca3af;">Not a student or employee? Click here for visitor access</span>
                    </summary>
                    <div style="margin-top:10px;background:#f9fafb;border-radius:14px;padding:14px 16px;border:1px solid #e5e7eb;">
                        <div style="font-size:.68rem;color:#6b7280;margin-bottom:10px;text-align:center;">
                            <i class="fas fa-info-circle" style="color:#059669;margin-right:4px;"></i> 
                            Visitors only — no ID required. Please use the ID field above if you have one.
                        </div>
                        <div style="display:flex;justify-content:center;align-items:center;gap:12px;flex-wrap:wrap;">
                            <!-- Guest Check In with nudge float box -->
                            <div style="position:relative;display:inline-flex;">
                                <button type="button" id="guestCheckIn" style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:999px;border:1.5px solid #a7f3d0;background:#f0fdf6;color:#047857;font-size:.78rem;font-weight:650;cursor:pointer;font-family:inherit;transition:all .15s;box-shadow:0 1px 2px rgba(0,0,0,.03);">
                                    <i class="fas fa-user-plus" style="font-size:.7rem;"></i> Guest Check In
                                </button>
                                <!-- Floating thought box -->
                                <div id="guestNudge" style="position:absolute;right:calc(100% + 14px);top:50%;transform:translateY(-50%);background:#fff;border:1.5px solid #a7f3d0;border-radius:10px;padding:6px 12px;font-size:.72rem;color:#047857;font-weight:600;white-space:nowrap;box-shadow:0 3px 12px rgba(6,78,59,.1);pointer-events:none;z-index:5;animation:floatNudge 3s ease-in-out infinite;">
                                    <span style="position:absolute;right:-7px;top:50%;transform:translateY(-50%);border-top:6px solid transparent;border-bottom:6px solid transparent;border-left:7px solid #a7f3d0;"></span>
                                    👋 Not a student? Just visiting?
                                </div>
                            </div>
                            <button type="button" id="guestCheckOut" style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:999px;border:1.5px solid #fca5a5;background:#fffbfb;color:#dc2626;font-size:.78rem;font-weight:650;cursor:pointer;font-family:inherit;transition:all .15s;box-shadow:0 1px 2px rgba(0,0,0,.03);">
                                <i class="fas fa-sign-out-alt" style="font-size:.7rem;"></i> Guest Check Out
                            </button>
                        </div>
                    </div>
                </details>
            </div>

            <!-- Morse -->
            <div style="text-align:center;margin-top:16px;user-select:none;pointer-events:none;">
                <span style="font-size:.6rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#b8dccb;font-style:italic;white-space:nowrap;">
                    .--. .-. --- .--- . -.-. - / -... -.-- ---... / .. ...- .- -. / .... .- .-. ...- . -.-- / -.. .- -. .- --- / .-. .. ...- . .-. .-
                </span>
            </div>
        </div>
    </div>

    <!-- KPI CARDS (perfectly centered, consistently rounded) -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;animation:fadeUp .55s ease-out .1s both;">
        
        <!-- KPI 1 -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);border:1px solid #f1f5f9;padding:18px 16px;text-align:center;min-height:130px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;position:relative;overflow:hidden;transition:box-shadow .2s,transform .2s;">
            <div style="position:absolute;top:0;left:12px;right:12px;height:3px;border-radius:0 0 3px 3px;background:#10b981;"></div>
            <div style="font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Total Check-Ins Today</div>
            <div id="kpiTotalCheckins" style="font-size:1.8rem;font-weight:700;color:#10b981;letter-spacing:-.4px;line-height:1;">—</div>
        </div>
        
        <!-- KPI 2 -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);border:1px solid #f1f5f9;padding:18px 16px;text-align:center;min-height:130px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;position:relative;overflow:hidden;transition:box-shadow .2s,transform .2s;">
            <div style="position:absolute;top:0;left:12px;right:12px;height:3px;border-radius:0 0 3px 3px;background:#3b82f6;"></div>
            <div style="font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Currently In Attendance</div>
            <div id="kpiActiveStudents" style="font-size:1.8rem;font-weight:700;color:#3b82f6;letter-spacing:-.4px;line-height:1;">—</div>
        </div>
        
        <!-- KPI 3: Top Colleges -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);border:1px solid #f1f5f9;padding:18px 16px;text-align:center;min-height:130px;display:flex;flex-direction:column;justify-content:center;gap:10px;position:relative;overflow:hidden;transition:box-shadow .2s,transform .2s;">
            <div style="position:absolute;top:0;left:12px;right:12px;height:3px;border-radius:0 0 3px 3px;background:#f59e0b;"></div>
            <div style="font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Top 3 Colleges Today</div>
            <div id="topColleges" style="display:flex;flex-direction:column;gap:4px;width:100%;padding:0 4px;">
                <div style="display:flex;align-items:baseline;gap:8px;font-size:.74rem;color:#4b5563;font-weight:500;">
                    <span style="font-weight:700;color:#9ca3af;font-size:.64rem;width:14px;flex-shrink:0;text-align:right;">1.</span>
                    <span style="color:#d97706;font-weight:600;">Loading...</span>
                </div>
                <div style="display:flex;align-items:baseline;gap:8px;font-size:.74rem;color:#4b5563;font-weight:500;">
                    <span style="font-weight:700;color:#9ca3af;font-size:.64rem;width:14px;flex-shrink:0;text-align:right;">2.</span>
                    <span style="color:#d97706;font-weight:600;">Loading...</span>
                </div>
                <div style="display:flex;align-items:baseline;gap:8px;font-size:.74rem;color:#4b5563;font-weight:500;">
                    <span style="font-weight:700;color:#9ca3af;font-size:.64rem;width:14px;flex-shrink:0;text-align:right;">3.</span>
                    <span style="color:#d97706;font-weight:600;">Loading...</span>
                </div>
            </div>
        </div>
        
        <!-- KPI 4: Top Courses -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);border:1px solid #f1f5f9;padding:18px 16px;text-align:center;min-height:130px;display:flex;flex-direction:column;justify-content:center;gap:10px;position:relative;overflow:hidden;transition:box-shadow .2s,transform .2s;">
            <div style="position:absolute;top:0;left:12px;right:12px;height:3px;border-radius:0 0 3px 3px;background:#06b6d4;"></div>
            <div style="font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Top 3 Courses Today</div>
            <div id="topCourses" style="display:flex;flex-direction:column;gap:4px;width:100%;padding:0 4px;">
                <div style="display:flex;align-items:baseline;gap:8px;font-size:.74rem;color:#4b5563;font-weight:500;">
                    <span style="font-weight:700;color:#9ca3af;font-size:.64rem;width:14px;flex-shrink:0;text-align:right;">1.</span>
                    <span style="color:#0891b2;font-weight:600;">Loading...</span>
                </div>
                <div style="display:flex;align-items:baseline;gap:8px;font-size:.74rem;color:#4b5563;font-weight:500;">
                    <span style="font-weight:700;color:#9ca3af;font-size:.64rem;width:14px;flex-shrink:0;text-align:right;">2.</span>
                    <span style="color:#0891b2;font-weight:600;">Loading...</span>
                </div>
                <div style="display:flex;align-items:baseline;gap:8px;font-size:.74rem;color:#4b5563;font-weight:500;">
                    <span style="font-weight:700;color:#9ca3af;font-size:.64rem;width:14px;flex-shrink:0;text-align:right;">3.</span>
                    <span style="color:#0891b2;font-weight:600;">Loading...</span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>
<script>
$(function () {

const BACKEND = "backend/bk_LibraryMenu/bk_libLogs.php";

let libraryID = null;
let libraryName = "";
let currentUser = null;
let duplicates = [];
let successTimer = null;

// Single teardown prevents stacked listeners on AJAX reload
$(document).off(".libLogs");

// Clock
setInterval(function () {
    $("#kpiCurrentTime").text(new Date().toLocaleString("en-US", {
        hour: "2-digit", minute: "2-digit", second: "2-digit",
        year: "numeric", month: "short", day: "numeric", hour12: true
    }));
}, 1000);

// Nudge animation
setTimeout(function cycle() {
    $("#guestNudge").css("opacity", "1");
    setTimeout(function () {
        $("#guestNudge").css("opacity", "0");
        setTimeout(cycle, 2000);
    }, 5000);
}, 2000);

// Hover effects
function applyHover(selector, enterStyles, leaveStyles) {
    $(selector)
        .on("mouseenter", function () { $(this).css(enterStyles); })
        .on("mouseleave", function () { $(this).css(leaveStyles); });
}

applyHover("#guestCheckIn",
    { background: "#d1fae5", boxShadow: "0 4px 14px rgba(6,78,59,.15)", transform: "translateY(-1px)" },
    { background: "#f0fdf9", boxShadow: "", transform: "" }
);
applyHover("#guestCheckOut",
    { background: "#fee2e2", boxShadow: "0 4px 14px rgba(220,38,38,.15)", transform: "translateY(-1px)" },
    { background: "#fff5f5", boxShadow: "", transform: "" }
);

// Modal helpers
function showModal(title, body, footer) {
    clearTimeout(successTimer);
    successTimer = null;
    $("#dynamicModalTitle").html(title);
    $("#dynamicModalBody").html(body);
    $("#dynamicModalFooter").html(footer || "");
    $("#dynamicModal").modal("show");
}

function hideModal() {
    clearTimeout(successTimer);
    successTimer = null;
    $("#dynamicModal").modal("hide");
}

function showMessage(type, message, autoClose) {
    const styleMap = {
        success: { icon: "fa-check-circle", colorClass: "alert-success", title: "Success" },
        error: { icon: "fa-exclamation-circle",  colorClass: "alert-danger",  title: "Error"   },
        info: { icon: "fa-info-circle", colorClass: "alert-info", title: "Info"    }
    };
    const style = styleMap[type];
    showModal(style.title, `
        <div class="alert ${style.colorClass} text-center mb-0">
            <i class="fas ${style.icon} me-2"></i>${message}
        </div>
    `);
    if (autoClose) successTimer = setTimeout(hideModal, autoClose);
}

// Load library on boot
$.ajax({
    type: "POST",
    url: BACKEND,
    data: { request: "getLibraries", userID: UserInfo.UserID },
    success: function (response) {
        if (!response.success || !response.data || !response.data.length) {
            $("#currentLibraryDisplay").text(response.error || "No Library Access");
            return;
        }
        libraryID = response.data[0].SectionID;
        libraryName = response.data[0].SectionName;
        $("#currentLibraryDisplay").text(libraryName);
        loadKPI();
    },
    error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
});

function loadKPI() {
    if (!libraryID) return;
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getKPI", sectionID: libraryID },
        success: function (response) {
            if (!response.success || !response.data) return;
            $("#kpiTotalCheckins").text(response.data.totalToday || 0);
            $("#kpiActiveStudents").text(response.data.currentlyInside || 0);

            function renderRankList(items, colorClass) {
                if (!items) items = ["-", "-", "-"];
                let html = "";
                items.forEach(function (label, index) {
                    html += `<div class="mb-1">
                                <span class="fw-bold">${index + 1}.</span>
                                <span class="${colorClass}">${label}</span>
                             </div>`;
                });
                return html;
            }

            $("#topColleges").html(renderRankList(response.data.topColleges, "text-warning"));
            $("#topCourses").html(renderRankList(response.data.topCourses, "text-info"));
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
}

function checkAndShowAttendance(user) {
    let resolvedAction;

    $.post(BACKEND, {
        request: "checkStatusToday",
        idNumber: user.id_number,
        name: user.name
    })
    .then(function (status) {
        const rawAction = !status.checkedIn ? "checkin"
                        : Number(status.sectionID) === Number(libraryID) ? "checkout"
                        : "switch";

        resolvedAction = rawAction === "switch" ? "checkin" : rawAction;

        return $.post(BACKEND, {
            request: "getAttendanceModal",
            user: JSON.stringify(user),
            action: rawAction,
            sectionName: status.sectionName || "",
            libraryName: libraryName
        });
    })
    .then(function (modal) {
        if (modal.success) {
            showModal("Attendance Confirmation", modal.body, modal.footer);
            // Store action on the button — no shared pendingAction variable needed
            $("#confirmAttendance").data("resolved-action", resolvedAction);
        }
    })
    .fail(function () { alert("Connection error."); });
}

function saveAttendance(user, resolvedAction) {
    if (!libraryID) return;
    if (user.classification !== "GUEST" && !user.id_number) return;

    const isCheckIn = resolvedAction === "checkin";

    const actionLabel = isCheckIn
        ? "checked in"
        : "checked out";

    // Inline-styled reminder cards (no external CSS dependencies)
    const reminder = isCheckIn
        ? `<div style="display:flex; align-items:center; gap:14px; margin-top:16px; padding:14px 16px; border-radius:14px; border:1px solid #fde68a; background:linear-gradient(135deg, #fffbeb, #fef3c7); box-shadow:0 2px 12px rgba(245,158,11,0.12);">
               <div style="width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#fef9c3; border:1.5px solid #fde68a;">
                   ⏰
               </div>
               <div>
                   <div style="font-size:0.67rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#d97706; margin-bottom:2px;">Reminder</div>
                   <div style="font-size:0.82rem; font-weight:500; color:#92400e; line-height:1.45;">Please don't forget to <strong>check out</strong> before leaving.</div>
               </div>
           </div>`
        : `<div style="display:flex; align-items:center; gap:14px; margin-top:16px; padding:14px 16px; border-radius:14px; border:1px solid #6ee7b7; background:linear-gradient(135deg, #ecfdf5, #d1fae5); box-shadow:0 2px 12px rgba(16,185,129,0.12);">
               <div style="width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#dcfce7; border:1.5px solid #6ee7b7;">
                   ✅
               </div>
               <div>
                   <div style="font-size:0.67rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#059669; margin-bottom:2px;">All Done</div>
                   <div style="font-size:0.82rem; font-weight:500; color:#065f46; line-height:1.45;">Thank you for visiting. See you next time!</div>
               </div>
           </div>`;

    showMessage(
        "success",
        `<strong>${user.name}</strong> successfully ${actionLabel}.${reminder}`,
        3500
    );

    $.ajax({
        type: "POST",
        url: BACKEND,
        data: {
            request: "getSaveAttendance",
            action: resolvedAction,
            idNumber: user.id_number,
            sectionID: libraryID,
            classification: user.classification || "STUDENT",
            name: user.name,
            college: user.college || "",
            course: user.course || "",
            sex: user.sex || "",
            agency_organization: user.agency_organization || ""
        },
        success: function (response) {
            if (response.error) { showMessage("error", response.error); return; }
            loadKPI();
            $("#inputIDNumber").val("");
        },
        error: function (xhr) {
            const msg = xhr.status === 0
                ? "Cannot reach the server. Check your connection."
                : "A server error occurred. Please try again.";
            showMessage("error", msg);
        }
    });
}

function updateGuestUI() {
    const guestCount = $("#guestCheckOutList .guest-row").length;
    const guestLabel = guestCount === 1 ? "guest" : "guests";
    $("#dynamicModal .badge.rounded-pill").text(`${guestCount} ${guestLabel}`);
    $("#guestEmptyState").toggle(guestCount === 0);
    $("#guestSearchInput").trigger("input");
}

// Event handlers 

// ID form submit
$(document).on("submit.libLogs", "#logForm", function (event) {
    event.preventDefault();
    const idNumber = $("#inputIDNumber").val().trim();
    if (!idNumber) { alert("Please enter an Identification Number."); return; }

    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getValidateUser", idNumber: idNumber },
        success: function (response) {
            if (response.error) {
                alert("No record found for that ID number.");
                $("#inputIDNumber").val("").focus();
                return;
            }
            if (response.duplicate) {
                duplicates = response.matches;
                showModal("Identity Verification", response.modalHTML);
                return;
            }
            currentUser = response.data;
            checkAndShowAttendance(response.data);
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Confirm attendance — action is read from button data, not a shared variable
$(document).on("click.libLogs", "#confirmAttendance", function () {
    if (!currentUser) return;
    saveAttendance(currentUser, $(this).data("resolved-action"));
});

// Secret key for duplicate users
$(document).on("input.libLogs", "#modalSecretKey", function () {
    const digits = $(this).val().replace(/\D/g, "").substring(0, 8);
    const formatted = digits.length > 4 ? `${digits.slice(0,2)}/${digits.slice(2,4)}/${digits.slice(4)}`
                    : digits.length > 2 ? `${digits.slice(0,2)}/${digits.slice(2)}`
                    : digits;
    $(this).val(formatted);

    if (digits.length < 8) {
        $("#verifiedStudentContainer").hide().empty();
        $("#secretKeyStatus").html(`<span class="text-muted"><i class="fas fa-info-circle me-1"></i>Enter birth date (MM/DD/YYYY)</span>`);
        return;
    }

    const matchedUser = duplicates.find(function (candidate) {
        return candidate.secretKey?.replace(/\D/g, "") === digits;
    });

    if (matchedUser) {
        currentUser = matchedUser;
        $("#secretKeyStatus").html(`<span class="text-success"><i class="fas fa-check-circle me-1"></i>Identity verified</span>`);
        $("#verifiedStudentContainer").show();
        checkAndShowAttendance(matchedUser);
    } else {
        $("#secretKeyStatus").html(`<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Invalid key — try again</span>`);
        $("#verifiedStudentContainer").hide().empty();
    }
});

// Toggle ID visibility
$(document).on("click.libLogs", "#toggleIdVisibility", function () {
    const inputField = $("#inputIDNumber");
    const isHidden = inputField.attr("type") === "password";
    inputField.attr("type", isHidden ? "text" : "password");
    $("#toggleIcon").toggleClass("fa-eye fa-eye-slash");
});

// Toggle secret key visibility
$(document).on("click.libLogs", "#toggleSecretKey", function () {
    const inputField = $("#modalSecretKey");
    const isHidden = inputField.attr("type") === "password";
    inputField.attr("type", isHidden ? "text" : "password");
    $("#secretIcon").toggleClass("fa-eye fa-eye-slash");
});

// Guest check-in modal
$(document).on("click.libLogs", "#guestCheckIn", function () {
    if (!libraryID) { alert("Library section not loaded yet."); return; }
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "guestCheckIn", libraryName: libraryName },
        success: function (response) {
            if (response.success) {
                showModal("Guest Check-In", response.body, response.footer);
            } else {
                alert(response.error || "Failed to load guest form.");
            }
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Confirm guest check-in
$(document).on("click.libLogs", "#confirmGuestCheckIn", function () {
    const guestFirst = $("#guestFirstName").val().trim();
    const guestMI = $("#guestMiddleInitial").val().trim().replace(/\.+$/, "");
    const guestLast = $("#guestLastName").val().trim();
    const guestName = guestMI
    ? `${guestFirst} ${guestMI}. ${guestLast}`
    : `${guestFirst} ${guestLast}`;
    const guestSex = $("#guestSex").val();
    const guestOrganization = $("#guestAgency").val().trim();
    if (!guestFirst){ 
        alert("First name is required."); 
        return; 
    }
    if (!guestLast){ 
        alert("Last name is required.");  
        return; 
    } 
    if (!guestSex){ 
        alert("Please select a sex.");
        return; 
    } 
    if (!guestOrganization){ 
        alert("Agency / Organization required."); 
        return;
    }
    saveAttendance({
        id_number: "",
        name: guestName,
        classification: "GUEST",
        college: "",
        course: "",
        sex: guestSex,
        agency_organization: guestOrganization
    }, "checkin");
});

// Guest check-out modal
$(document).on("click.libLogs", "#guestCheckOut", function () {
    if (!libraryID) { alert("Library section not loaded yet."); return; }
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getGuestCheckOutModal", sectionID: libraryID, libraryName: libraryName },
        success: function (response) {
            if (response.success) {
                showModal("Guest Check-Out", response.body, response.footer);
            } else {
                alert(response.error || "Failed to load guest list.");
            }
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Guest check-out action
$(document).on("click.libLogs", ".btn-guest-checkout", function () {
    const logID = $(this).data("logid");
    const guestName = $(this).data("name");
    if (!logID) return;
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "guestCheckOut", logID: logID, sectionID: libraryID },
        success: function (response) {
            if (response.error) { alert(response.error); return; }
            $(`.btn-guest-checkout[data-logid="${logID}"]`)
                .closest(".guest-row")
                .fadeOut(200, function () {
                    $(this).remove();
                    updateGuestUI();
                });
            const successAlert = $(`
                <div class="alert alert-success py-2 px-3 mb-2 text-center">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>${guestName}</strong> successfully checked out.
                </div>
            `).prependTo("#guestCheckOutList");
            setTimeout(function () {
                successAlert.fadeOut(400, function () { successAlert.remove(); });
            }, 2000);
            loadKPI();
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Guest search
$(document).on("input.libLogs", "#guestSearchInput", function () {
    const searchQuery = $(this).val().toLowerCase().trim();
    let visibleCount = 0;
    $("#guestCheckOutList .guest-row").each(function () {
        const nameMatches = ($(this).data("name") || "").toLowerCase().includes(searchQuery);
        $(this).toggle(nameMatches);
        if (nameMatches) visibleCount++;
    });
    $("#guestNoResults").toggle(visibleCount === 0 && searchQuery !== "");
});

// Close modal
$(document).on("click.libLogs",
    "#dynamicModal .btn-close, #dynamicModal [data-dismiss='modal'], #dynamicModal [data-bs-dismiss='modal']",
    hideModal
);

});
</script>