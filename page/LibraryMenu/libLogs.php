<div class="container-fluid py-4">

<style>
  @keyframes ldot{0%,100%{opacity:1}50%{opacity:.3}}
  @keyframes gin{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.3)}50%{box-shadow:0 0 0 8px rgba(16,185,129,0)}}
  @keyframes gout{0%,100%{box-shadow:0 0 0 0 rgba(220,38,38,.22)}50%{box-shadow:0 0 0 8px rgba(220,38,38,0)}}

  /* ID input wrapper */
  .iw{border-radius:12px;border:2px solid #10b981;background:#fff;overflow:hidden;display:flex;align-items:stretch;}
  .iw:focus-within{border-color:#047857;box-shadow:0 0 0 3px rgba(16,185,129,.15);}
  .itag{display:flex;flex-direction:column;justify-content:center;align-items:center;gap:2px;
    padding:0 14px;background:#f0fdf4;border-right:1.5px solid #bbf7d0;min-width:64px;flex-shrink:0;}
  .itag i{color:#10b981;font-size:1rem;}
  .itag span{font-size:.48rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#6ee7b7;white-space:nowrap;}
  #inputIDNumber{flex:1;border:none;outline:none;background:transparent;padding:13px 14px;font-size:.92rem;color:#0f172a;}
  #inputIDNumber::placeholder{color:#94a3b8;font-style:italic;font-size:.82rem;}

  /* Hover states */
  #toggleIdVisibility:hover{color:#047857!important;}
  #logForm button[type=submit]:hover{background:#047857!important;box-shadow:0 4px 14px rgba(6,78,59,.2)!important;}

  /* Guest buttons */
  #guestCheckIn{display:inline-flex;align-items:center;gap:10px;
    padding:10px 20px 10px 10px;border-radius:999px;border:2px solid #6ee7b7;cursor:pointer;
    background:#f0fdf9;color:#047857;font-size:.82rem;font-weight:700;
    transition:background .15s,transform .15s;animation:gin 3s ease-in-out infinite;}
  #guestCheckIn:hover{background:#d1fae5;transform:translateY(-1px);}
  #guestCheckOut{display:inline-flex;align-items:center;gap:10px;
    padding:10px 20px 10px 10px;border-radius:999px;border:2px solid #fca5a5;cursor:pointer;
    background:#fff5f5;color:#dc2626;font-size:.82rem;font-weight:700;
    transition:background .15s,transform .15s;animation:gout 3s ease-in-out 1.5s infinite;}
  #guestCheckOut:hover{background:#fee2e2;transform:translateY(-1px);}
  .gico{width:30px;height:30px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.85rem;}
  .gico-g{background:#dcfce7;color:#047857;}
  .gico-r{background:#fee2e2;color:#dc2626;}
  .glbl{display:flex;flex-direction:column;line-height:1.2;text-align:left;}
  .gbg{font-size:.48rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
    border-radius:999px;padding:1px 6px;margin-bottom:1px;width:fit-content;}
  .gbg-g{background:#dcfce7;color:#047857;border:1px solid #6ee7b7;}
  .gbg-r{background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;}
</style>

<div class="px-1">

  <!-- ═ HEADER ═ -->
  <div class="card border-0 rounded-4 mb-3" style="box-shadow:0 1px 6px rgba(0,0,0,.07);">
    <div class="card-body py-3 px-4">
      <div class="d-flex align-items-center mb-1" style="gap:9px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                     background:#10b981;flex-shrink:0;
                     animation:ldot 2.2s ease-in-out infinite;"></span>
        <span class="fw-bold text-dark" style="font-size:1rem;letter-spacing:-.2px;">Library Attendance Dashboard</span>
      </div>
      <div class="text-muted" style="font-size:.76rem;padding-left:17px;">Real-time monitoring of today's attendance activity</div>
    </div>
  </div>

  <!-- ═ LOG ATTENDANCE CARD ═ -->
  <div class="card border-0 rounded-4 mb-3"
       style="box-shadow:0 2px 16px rgba(6,78,59,.1);border:1.5px solid #6ee7b7 !important;">
    <div class="card-body p-4"
         style="background:linear-gradient(150deg,#e8faf3 0%,#f0fdf9 40%,#eaf6f2 100%);
                border-radius:calc(1rem - 1px);">

      <!-- TOP ROW: Section badge + Time badge -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4"
           style="border-bottom:1px solid #c6ead9;">

        <!-- Section badge -->
        <div class="d-flex align-items-center"
             style="background:#fff;border:1px solid #a7f3d0;border-radius:16px;
                    gap:12px;min-width:280px;padding:12px 16px;
                    box-shadow:0 2px 12px rgba(6,78,59,.08),inset 0 1px 0 rgba(255,255,255,.9);">
          <div class="d-flex align-items-center justify-content-center flex-shrink-0"
               style="width:36px;height:36px;border-radius:10px;
                      background:linear-gradient(135deg,#d1fae5,#a7f3d0);
                      color:#047857;font-size:.85rem;
                      box-shadow:0 1px 4px rgba(6,78,59,.12);">
            <i class="fas fa-book-open"></i>
          </div>
          <div style="line-height:1.5;">
            <div class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;margin-bottom:1px;"><b>Library Section</b></div>
            <div class="fw-bold" style="font-size:.9rem;color:#064e3b;white-space:nowrap;" id="currentLibraryDisplay">Main Library</div>
          </div>
        </div>

        <!-- Time badge -->
        <div class="d-flex flex-column"
             style="background:linear-gradient(135deg,#fff,#ecfdf5);
                    border:2px solid #6ee7b7;border-radius:18px;
                    min-width:280px;padding:14px 20px;
                    box-shadow:0 6px 20px rgba(0,0,0,.06),0 2px 6px rgba(0,0,0,.04),
                               inset 0 1px 0 rgba(255,255,255,.9);">
          <span class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;"><b>Current Date &amp; Time</b></span>
          <span id="kpiCurrentTime" class="fw-semibold mt-1" style="font-size:1rem;color:#064e3b;letter-spacing:-.2px;">—</span>
        </div>

      </div><!-- /TOP ROW -->

      <!-- FORM -->
      <form id="logForm" autocomplete="off">

        <!-- Label + type chips -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
          <label for="inputIDNumber" class="fw-bold text-uppercase mb-0"
                 style="font-size:.67rem;letter-spacing:.09em;color:#3d8a6e;">
                 Enter Student Number or Employee Number :
          </label>
          <div style="display:flex;gap:6px;">
            <span style="font-size:.58rem;font-weight:700;padding:2px 9px;border-radius:999px;
                         background:#f0fdf4;color:#047857;border:1px solid #bbf7d0;letter-spacing:.04em;">
              🎓 Student
            </span>
            <span style="font-size:.58rem;font-weight:700;padding:2px 9px;border-radius:999px;
                         background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;letter-spacing:.04em;">
              💼 Employee
            </span>
          </div>
        </div>

        <!-- ID Input -->
        <div class="iw">
          <div class="itag">
            <i class="fas fa-id-card"></i>
            <span>ID No.</span>
          </div>
          <input type="password" id="inputIDNumber"
                 placeholder="Check In / Check Out Here:"
                 autocomplete="new-password" spellcheck="false">
          <button type="button" id="toggleIdVisibility"
                  style="background:transparent;border:none;outline:none;
                         padding:0 14px;color:#94a3b8;cursor:pointer;font-size:.95rem;line-height:1;transition:color .15s;">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>

        <hr class="my-4" style="border-color:#c6ead9;">

        <button type="submit" class="btn w-100 fw-semibold text-white py-3"
                style="background:#064e3b;border:none;border-radius:8px;
                       font-size:.9rem;letter-spacing:.02em;transition:background .18s,box-shadow .18s;">
          <i class="fas fa-check-circle mr-2"></i> Confirm Access
        </button>
      </form>

      <!-- GUEST SECTION -->
      <div class="mt-4">

        <!-- Guest divider -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
          <div style="flex:1;height:1px;background:linear-gradient(to right,transparent,#a7f3d0);"></div>
          <span style="font-size:.57rem;font-weight:700;letter-spacing:.13em;text-transform:uppercase;
                       color:#3d8a6e;font-style:italic;white-space:nowrap;">
            &nbsp;·&nbsp; Not a student or employee? &nbsp;·&nbsp;
          </span>
          <div style="flex:1;height:1px;background:linear-gradient(to left,transparent,#a7f3d0);"></div>
        </div>

        <!-- Guest buttons -->
        <div class="d-flex justify-content-center align-items-center w-100" style="gap:12px;flex-wrap:wrap;">

          <div class="position-relative">
            <button type="button" id="guestCheckIn">
              <div class="gico gico-g"><i class="fas fa-user-plus"></i></div>
              <div class="glbl">
                <span class="gbg gbg-g">Guest</span>
                <span>Check In</span>
              </div>
            </button>
            <!-- Nudge tooltip -->
            <div id="guestNudge" class="position-absolute"
                 style="right:calc(100% + 13px);top:50%;transform:translateY(-50%);
                        background:#fff;border:1.5px solid #a7f3d0;border-radius:10px;
                        padding:5px 12px;font-size:.72rem;color:#047857;font-weight:600;
                        white-space:nowrap;box-shadow:0 3px 12px rgba(6,78,59,.1);
                        opacity:1;pointer-events:none;transition:opacity .4s ease;z-index:10;">
              <span style="position:absolute;right:-7px;top:50%;transform:translateY(-50%);
                           border-top:6px solid transparent;border-bottom:6px solid transparent;
                           border-left:7px solid #a7f3d0;"></span>
              👋 Not a student / just visiting?
            </div>
          </div>

          <button type="button" id="guestCheckOut">
            <div class="gico gico-r"><i class="fas fa-sign-out-alt"></i></div>
            <div class="glbl">
              <span class="gbg gbg-r">Guest</span>
              <span>Check Out</span>
            </div>
          </button>

        </div>
      </div><!-- /GUEST SECTION -->

      <!-- MORSE DIVIDER -->
      <div class="text-center mt-3" style="user-select:none;pointer-events:none;">
        <span style="font-size:.65rem;font-weight:700;letter-spacing:.16em;
                     text-transform:uppercase;color:#6ee7b7;font-style:italic;white-space:nowrap;">
          .--. .-. --- .--- . -.-. - / -... -.-- ---... / .. ...- .- -. / .... .- .-. ...- . -.-- / -.. .- -. .- --- / .-. .. ...- . .-. .-
        </span>
      </div>

    </div>
  </div><!-- /LOG ATTENDANCE CARD -->

  <!-- ═ KPI CARDS ═ -->
  <div class="row g-3">
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 rounded-4 overflow-hidden"
           style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #10b981 !important;height:130px;">
        <div class="card-body d-flex flex-column align-items-center text-center" style="padding:14px 12px;">
          <div class="text-uppercase fw-bold text-muted" style="font-size:.64rem;letter-spacing:.09em;">Total Check-Ins Today</div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-center">
            <div id="kpiTotalCheckins" style="font-size:1.75rem;font-weight:600;color:#10b981;line-height:1;">—</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 rounded-4 overflow-hidden"
           style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #3b82f6 !important;height:130px;">
        <div class="card-body d-flex flex-column align-items-center text-center" style="padding:14px 12px;">
          <div class="text-uppercase fw-bold text-muted" style="font-size:.64rem;letter-spacing:.09em;">Currently In Attendance</div>
          <div class="flex-grow-1 d-flex align-items-center justify-content-center">
            <div id="kpiActiveStudents" style="font-size:1.75rem;font-weight:600;color:#3b82f6;line-height:1;">—</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 rounded-4 overflow-hidden"
           style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #f59e0b !important;height:130px;">
        <div class="card-body d-flex flex-column justify-content-center" style="padding:14px 16px;">
          <div class="text-uppercase fw-bold text-muted mb-2 text-center" style="font-size:.64rem;letter-spacing:.09em;">Top 3 Colleges Today</div>
          <div id="topColleges">
            <div class="d-flex align-items-baseline gap-2 mb-1">
              <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">1.</span>
              <span class="text-warning fw-semibold" style="font-size:.75rem;">Loading...</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
              <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">2.</span>
              <span class="text-warning fw-semibold" style="font-size:.75rem;">Loading...</span>
            </div>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">3.</span>
              <span class="text-warning fw-semibold" style="font-size:.75rem;">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 rounded-4 overflow-hidden"
           style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #06b6d4 !important;height:130px;">
        <div class="card-body d-flex flex-column justify-content-center" style="padding:14px 16px;">
          <div class="text-uppercase fw-bold text-muted mb-2 text-center" style="font-size:.64rem;letter-spacing:.09em;">Top 3 Courses Today</div>
          <div id="topCourses">
            <div class="d-flex align-items-baseline gap-2 mb-1">
              <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">1.</span>
              <span class="text-info fw-semibold" style="font-size:.75rem;">Loading...</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
              <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">2.</span>
              <span class="text-info fw-semibold" style="font-size:.75rem;">Loading...</span>
            </div>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">3.</span>
              <span class="text-info fw-semibold" style="font-size:.75rem;">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div><!-- /KPI row -->

</div><!-- /px-1 -->
</div><!-- /container-fluid -->

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

    const reminder = isCheckIn
        ? `<div class="lib-reminder lib-reminder--in">
               <div class="lib-reminder__icon">⏰</div>
               <div>
                   <div class="lib-reminder__tag">Reminder</div>
                   <div class="lib-reminder__text">Please don't forget to <strong>check out</strong> before leaving.</div>
               </div>
           </div>`
        : `<div class="lib-reminder lib-reminder--out">
               <div class="lib-reminder__icon">✅</div>
               <div>
                   <div class="lib-reminder__tag">All Done</div>
                   <div class="lib-reminder__text">Thank you for visiting. See you next time!</div>
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