<div class="container-fluid py-4">

<style>
  @keyframes ldot {
    0%,100% { opacity:1; }
    50%      { opacity:.35; }
  }
  #toggleIdVisibility:hover { color:#064e3b !important; }
  #logForm button[type=submit]:hover {
    background:#047857 !important;
    box-shadow:0 5px 18px rgba(6,78,59,.25) !important;
  }
</style>

<div class="px-1">

  <!-- ═══ HEADER ═══════════════════════════════════════════════ -->
  <div class="card border-0 rounded-4 mb-3" style="box-shadow:0 1px 6px rgba(0,0,0,.07);">
    <div class="card-body py-3 px-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
          <div class="d-flex align-items-center mb-1" style="gap:9px;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                         background:#10b981;flex-shrink:0;
                         animation:ldot 2.2s ease-in-out infinite;"></span>
            <span class="fw-bold text-dark" style="font-size:1rem;letter-spacing:-.2px;">Library Attendance Dashboard</span>
          </div>
          <div class="text-muted" style="font-size:.76rem;padding-left:17px;">Real-time monitoring of today's attendance activity</div>
        </div>

      </div>
    </div>
  </div>

  <!-- ═══ LOG ATTENDANCE ════════════════════════════════════════ -->
  <div class="card border-0 rounded-4 mb-3"
       style="box-shadow:0 2px 16px rgba(6,78,59,.1);border:1.5px solid #6ee7b7 !important;">
    <div class="card-body p-4"
         style="background:linear-gradient(150deg,#e8faf3 0%,#f0fdf9 40%,#eaf6f2 100%);
                border-radius:calc(1rem - 1px);">

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4"
           style="border-bottom:1px solid #c6ead9;">



           <!-- Section badge -->
<div class="d-flex align-items-center px-3 py-3"
     style="background:#ffffff;
            border:1px solid #a7f3d0;
            border-radius:16px;
            gap:12px;min-width:280px;
            box-shadow:0 2px 12px rgba(6,78,59,.08),inset 0 1px 0 rgba(255,255,255,.9);">
  <div class="d-flex align-items-center justify-content-center flex-shrink-0"
       style="width:36px;height:36px;
              border-radius:10px;
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
<div class="px-4 py-3 d-flex flex-column"
style="background:linear-gradient(135deg,#ffffff,#ecfdf5);
    border:2px solid #6ee7b7;
    border-radius:18px;
    min-width:280px;
    padding:14px 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,.06),0 2px 6px rgba(0,0,0,.04),
    inset 0 1px 0 rgba(255,255,255,.9);">
  <span class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;"><b>Current Date &amp; Time</b></span>
  <span id="kpiCurrentTime" class="fw-semibold mt-1" style="font-size:1rem;color:#064e3b;letter-spacing:-.2px;">—</span>
</div>


      </div>
      <form id="logForm" autocomplete="off">
        <label for="inputStudentNumber"
               class="d-block fw-bold text-uppercase mb-2"
               style="font-size:.67rem;letter-spacing:.09em;color:#3d8a6e;">Identification Number</label>
        <div style="padding:2px;border-radius:11px;
                    background:linear-gradient(130deg,#10b981 0%,#3b82f6 55%,#06b6d4 100%);
                    box-shadow:0 3px 16px rgba(16,185,129,.15);">
          <div class="d-flex align-items-center bg-white" style="border-radius:9px;overflow:hidden;">
            <input type="password"
                   id="inputStudentNumber"
                   placeholder="Enter or scan identification number"
                   autocomplete="new-password"
                   spellcheck="false"
                   style="flex:1;border:none;outline:none;box-shadow:none;
                          background:transparent;padding:12px 16px;
                          font-size:.92rem;color:#0f172a;">
            <button type="button" id="toggleIdVisibility"
                    style="background:transparent;border:none;outline:none;
                           padding:0 14px;color:#94a3b8;cursor:pointer;
                           line-height:1;transition:color .15s;">
              <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
          </div>
        </div>

        <hr class="my-4" style="border-color:#c6ead9;">
        <button type="submit"
                class="btn w-100 fw-semibold text-white py-3"
                style="background:#064e3b;border:none;border-radius:8px;
                       font-size:.9rem;letter-spacing:.02em;
                       transition:background .18s,box-shadow .18s;">
          Confirm Access
        </button>
        <button type="button" class='form-control checkbutton'> Here Test Button</button>
      </form>
	  
<!-- ═══ GUEST CHECK-IN / CHECK-OUT BUTTONS ═══ -->
<div class="text-center mt-3">
  <div class="d-inline-flex gap-2 position-relative">
    
    <!-- CHECK IN -->
    <div class="d-inline-block position-relative">
      <button type="button" id="guestCheckIn"
              class="btn rounded-pill fw-semibold px-4 py-2"
              style="background:#f0fdf9;color:#047857;border:1.5px solid #6ee7b7;
                     font-size:.82rem;letter-spacing:.02em;
                     transition:background .18s,box-shadow .18s,transform .15s;">
        <i class="fas fa-user-clock me-2"></i>&nbsp;&nbsp;Check In as Guest
      </button>
<div id="guestNudge" class="position-absolute" 
     style="right:calc(100% + 12px);top:50%;transform:translateY(-50%);
            background:#fff;border:1.5px solid #a7f3d0;border-radius:12px;
            padding:6px 14px;font-size:.75rem;color:#047857;font-weight:600;
            white-space:nowrap;box-shadow:0 4px 14px rgba(6,78,59,.12);
            opacity:0;pointer-events:none;transition:opacity .4s ease;z-index:10;">
  <span style="position:absolute;right:-8px;top:50%;transform:translateY(-50%);
               border-top:7px solid transparent;border-bottom:7px solid transparent;
               border-left:8px solid #a7f3d0;"></span>
  <span style="position:absolute;right:-6px;top:50%;transform:translateY(-50%);
               border-top:6px solid transparent;border-bottom:6px solid transparent;
               border-left:7px solid #fff;"></span>
  👋 Not a student / just visiting?
</div>
    </div>
    <!-- CHECK OUT -->
    <button type="button" id="guestCheckOut"
            class="btn rounded-pill fw-semibold px-4 py-2"
            style="background:#fff5f5;color:#dc2626;border:1.5px solid #fca5a5;
                   font-size:.82rem;letter-spacing:.02em;
                   transition:background .18s,box-shadow .18s,transform .15s;">
      <i class="fas fa-sign-out-alt me-2"></i>&nbsp;&nbsp;Check Out as Guest
    </button>

  </div>
</div>
    </div>
  </div>

<!-- ═══ KPI CARDS ════════════════════════════════════════════ -->
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
</div><!-- /row -->

</div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>

$(document).on("click", ".checkbutton", function (){
  alert("Hello");
  $.ajax({
    type:"POST",
    url:"backend/bk_LibraryMenu/bk_libLogs-Test.php",
    data: {"getapi"},
    success: function(dataresult){
      console.log(dataresult);
    }
  });
});


$(function () {

// ── Static UI — cached once at boot (never replaced by AJAX) ──────────────
const UI = {
    btn: {
        checkIn:  document.getElementById("guestCheckIn"),
        checkOut: document.getElementById("guestCheckOut"),
        toggleId: document.getElementById("toggleIdVisibility"),
    },
    input: {
        studentNum: document.getElementById("inputStudentNumber"),
    },
    kpi: {
        time:     document.getElementById("kpiCurrentTime"),
        library:  document.getElementById("currentLibraryDisplay"),
        checkins: document.getElementById("kpiTotalCheckins"),
        active:   document.getElementById("kpiActiveStudents"),
        colleges: document.getElementById("topColleges"),
        courses:  document.getElementById("topCourses"),
    },
    modal: {
        el:     document.getElementById("dynamicModal"),
        title:  document.getElementById("dynamicModalTitle"),
        body:   document.getElementById("dynamicModalBody"),
        footer: document.getElementById("dynamicModalFooter"),
    },
    icon:  document.getElementById("toggleIcon"),
    nudge: document.getElementById("guestNudge"),
};

// ── Dynamic getters — elements inside PHP-injected modal HTML ─────────────
// These are replaced on every modal open and must be looked up fresh each time.
const live = {
    get secretKey()   
    { return document.getElementById("modalSecretKey"); },
    get guestName()   
    { return document.getElementById("guestName"); },
    get guestAgency() 
    { return document.getElementById("guestAgency"); },
    get guestSex()    
    { return document.getElementById("guestSex"); },
    get secretIcon()  
    { return document.getElementById("secretIcon"); },
    get keyStatus()   
    { return document.getElementById("secretKeyStatus"); },
    get verified()    
    { return document.getElementById("verifiedStudentContainer"); },
    get guestList()   
    { return document.getElementById("guestCheckoutList"); },
    get noResults()   
    { return document.getElementById("guestNoResults"); },
};

const State = {
    libraryID: null,
    libraryName: "",
    user: null,
    dupes: [],
    action: "checkin",
    timers: { clock: null, nudge: null, success: null },
};

const BACKEND = "backend/bk_LibraryMenu/bk_libLogs-Test.php";
const post = (request, data = {}) => $.post(BACKEND, { request, ...data });

console.log(post);

const ALERT = {
    success: { icon: "fa-check-circle", cls: "alert-success", title: "Success" },
    error: { icon: "fa-exclamation-circle", cls: "alert-danger", title: "Error"   },
    info: { icon: "fa-info-circle", cls: "alert-info", title: "Info"    },
};

// ── Utilities ──────────────────────────────────────────────────────────────

function startClock() {
    clearInterval(State.timers.clock);
    const clockFormat = {
        hour: "2-digit", minute: "2-digit", second: "2-digit",
        year: "numeric", month: "short", day: "numeric", hour12: true,
    };
    const tick = () => $(UI.kpi.time).text(new Date().toLocaleString("en-US", clockFormat));
    tick();
    State.timers.clock = setInterval(tick, 1000);
}

function startNudge() {
    clearTimeout(State.timers.nudge);
    function cycle() {
        UI.nudge.style.opacity = "1";
        State.timers.nudge = setTimeout(() => {
            UI.nudge.style.opacity = "0";
            State.timers.nudge = setTimeout(cycle, 2000);
        }, 5000);
    }
    State.timers.nudge = setTimeout(cycle, 2000);
}

function showModal(title, body, footer = "") {
    clearTimeout(State.timers.success);
    State.timers.success = null;
    $(UI.modal.title).html(title);
    $(UI.modal.body).html(body);
    $(UI.modal.footer).html(footer);
    $(UI.modal.el).modal("show");
}

function hideModal() {
    clearTimeout(State.timers.success);
    State.timers.success = null;
    $(UI.modal.el).modal("hide");
}

function showModalMessage(type, message, timeout = 0) {
    clearTimeout(State.timers.success);
    State.timers.success = null;
    const { icon, cls, title } = ALERT[type];
    showModal(title, `<div class="alert ${cls} text-center mb-0"><i class="fas ${icon} me-2"></i>${message}</div>`);
    if (timeout) State.timers.success = setTimeout(hideModal, timeout);
}

function setKeyStatus(type, icon, text) {
    const cssClass = { success: "text-success", danger: "text-danger" }[type] ?? "text-muted";
    $(live.keyStatus).html(`<span class="${cssClass}"><i class="fas ${icon} me-1"></i>${text}</span>`);
}

function togglePassword(inputEl, iconEl) {
    const isHidden = $(inputEl).attr("type") === "password";
    $(inputEl).attr("type", isHidden ? "text" : "password");
    $(iconEl).toggleClass("fa-eye", !isHidden).toggleClass("fa-eye-slash", isHidden);
}

function applyHover(el, enterStyles, leaveStyles) {
    $(el).off("mouseenter.libLogs mouseleave.libLogs")
         .on("mouseenter.libLogs", function () { $(this).css(enterStyles); })
         .on("mouseleave.libLogs", function () { $(this).css(leaveStyles); });
}

function renderKPI(kpiData) {
    $(UI.kpi.checkins).text(kpiData.totalToday    ?? 0);
    $(UI.kpi.active).text(kpiData.currentlyInside ?? 0);

    const buildRankedList = (items, colorClass) =>
        (items || ["-", "-", "-"]).map((label, index) =>
            `<div class="mb-1"><span class="fw-bold">${index + 1}.</span> <span class="${colorClass}">${label}</span></div>`
        ).join("");

    $(UI.kpi.colleges).html(buildRankedList(kpiData.topColleges, "text-warning"));
    $(UI.kpi.courses).html(buildRankedList(kpiData.topCourses, "text-info"));
}

// ── Data / Actions ─────────────────────────────────────────────────────────

function loadKPI() {
    if (!State.libraryID) return;
    post("getKPI", { sectionID: State.libraryID })
        .done(response => response.success && response.data && renderKPI(response.data));
}

function loadLibraries() {
    if (typeof UserInfo === "undefined" || !UserInfo.UserID)
        return $(UI.kpi.library).text("User not logged in");

    post("getLibraries", { userID: UserInfo.UserID })
        .done(response => {
            if (!response.success || !response.data?.length) {
                State.libraryName = response.error ?? "No Library Access";
                State.libraryID   = null;
                $(UI.kpi.library).text(State.libraryName);
                return;
            }
            State.libraryID   = response.data[0].SectionID;
            State.libraryName = response.data[0].SectionName;
            $(UI.kpi.library).text(State.libraryName);
            loadKPI();
        })
        .fail(() => $(UI.kpi.library).text("Connection error"));
}

function validateUser() {
    const idNumber = $(UI.input.studentNum).val().trim();
    if (!idNumber) return alert("Please enter an Identification Number.");

    post("getValidateUser", { idNumber }).done(response => {
        if (response.error) {
            alert("No record found for that ID number.");
            $(UI.input.studentNum).val("").focus();
            return;
        }
        if (response.duplicate) {
            State.dupes = response.matches;
            showModal("Identity Verification", response.modalHTML);
            return;
        }
        State.user = response.data;
        post("checkStatusToday", { idNumber: State.user.id_number, name: State.user.name })
            .done(status => determineAction(State.user, status));
    });
}

// Determines the attendance action and delegates all modal rendering to PHP.
// PHP owns ACTION_CONFIG (color/icon/btnText/message) — JS sends only the
// action key and sectionName, which is all the server needs to build the HTML.
function determineAction(user, status) {
    const action  = !status.checkedIn ? "checkin"
                  : Number(status.sectionID) === Number(State.libraryID) ? "checkout"
                  : "switch";
    const sectionName = status.sectionName || "";

    State.action = action;
    post("getAttendanceModal", {
        user: JSON.stringify(user),
        action,
        sectionName,
        libraryName: State.libraryName,
    }).done(response => response.success && showModal("Attendance Confirmation", response.body, response.footer));
}

function validateSecretKey(rawDigits) {
    const match = State.dupes.find(candidate => candidate.secretKey?.replace(/\D/g, "") === rawDigits);
    if (match) {
        State.user = match;
        setKeyStatus("success", "fa-check-circle", "Identity verified");
        $(live.verified).show();
        post("checkStatusToday", { idNumber: match.id_number, name: match.name })
            .done(status => determineAction(match, status));
    } else {
        setKeyStatus("danger", "fa-exclamation-circle", "Invalid key — try again");
        $(live.verified).hide().empty();
    }
}

function saveAttendance(user, action) {
    if (!State.libraryID || (user.classification !== "GUEST" && !user.id_number)) return;
    const resolvedAction = action === "switch" ? "checkin" : action;
    const statusLabel = resolvedAction === "checkin" ? "checked in" : "checked out";

    showModalMessage("success", `<strong>${user.name}</strong> successfully ${statusLabel}.`, 2000);

    post("getSaveAttendance", {
        action: resolvedAction,
        idNumber: user.id_number,
        sectionID: State.libraryID,
        classification: user.classification || "STUDENT",
        name: user.name,
        college: user.college || "",
        course: user.course || "",
        sex: user.sex || "",
        agency_organization: user.agency_organization || "",
    }).done(response => {
        if (response.error) { showModalMessage("error", response.error); return; }
        loadKPI();
        $(UI.input.studentNum).val("");
    });
}

function openGuestCheckIn() {
    if (!State.libraryID) return alert("Library section not loaded yet.");
    post("guestCheckIn", { libraryName: State.libraryName })
        .done(response => response.success
            ? showModal("Guest Check-In", response.body, response.footer)
            : alert(response.error || "Failed to load guest form."))
        .fail(() => alert("Connection error."));
}

function openGuestCheckOut() {
    if (!State.libraryID) return alert("Library section not loaded yet.");
    post("getGuestCheckoutModal", { sectionID: State.libraryID, libraryName: State.libraryName })
        .done(response => response.success
            ? showModal("Guest Check-Out", response.body, response.footer)
            : alert(response.error || "Failed to load guest list."))
        .fail(() => alert("Connection error."));
}

function checkoutGuest(logID, guestName) {
    if (!logID) return;
    post("guestCheckout", { logID, sectionID: State.libraryID }).done(response => {
        if (response.error) { alert(response.error); return; }

        $(`.btn-guest-checkout[data-logid="${logID}"]`).closest(".guest-row").fadeOut(200, function () {
            $(this).remove();
            updateGuestUI();
        });

        const $alert = $(`<div class="alert alert-success py-2 px-3 mb-2 text-center">
            <i class="fas fa-check-circle me-1"></i><strong>${guestName}</strong> successfully checked out.
        </div>`).prependTo(live.guestList);
        setTimeout(() => $alert.fadeOut(400, () => $alert.remove()), 2000);

        loadKPI();
    }).fail(() => alert("Connection error."));
}

function updateGuestUI() {
    const guestCount = $(live.guestList).find(".guest-row").length;
    $("#dynamicModal .badge.rounded-pill").text(`${guestCount} ${guestCount === 1 ? "guest" : "guests"}`);
    $("#guestEmptyState").toggle(guestCount === 0);
    $("#guestSearchInput").trigger("input");
}

function confirmGuestCheckIn() {
    const name = $(live.guestName).val().trim();
    const sex = $(live.guestSex).val();
    const organization = $(live.guestAgency).val().trim();
    if (!name) return alert("Guest name is required.");
    if (!sex) return alert("Please select a sex.");
    if (!organization) return alert("Agency / Organization required.");
    saveAttendance({ id_number: "", name, classification: "GUEST", college: "", course: "", sex, agency_organization: organization }, "checkin");
}

// ── Boot ───────────────────────────────────────────────────────────────────

$(document).off(".libLogs");
startClock();
startNudge();
loadLibraries();

applyHover(UI.btn.checkIn,
    { background: "#d1fae5", boxShadow: "0 4px 14px rgba(6,78,59,.15)", transform: "translateY(-1px)" },
    { background: "#f0fdf9", boxShadow: "", transform: "" }
);
applyHover(UI.btn.checkOut,
    { background: "#fee2e2", boxShadow: "0 4px 14px rgba(220,38,38,.15)", transform: "translateY(-1px)" },
    { background: "#fff5f5", boxShadow: "", transform: "" }
);

$(UI.btn.checkIn).off("click.libLogs").on("click.libLogs", openGuestCheckIn);
$(UI.btn.checkOut).off("click.libLogs").on("click.libLogs",openGuestCheckOut);
$(UI.btn.toggleId).off("click.libLogs").on("click.libLogs", () => togglePassword(UI.input.studentNum, UI.icon));

$(document)
    .on("submit.libLogs","#logForm", event => { event.preventDefault(); validateUser(); })
    .on("click.libLogs","#confirmAttendance", () => State.user && State.action && saveAttendance(State.user, State.action))
    .on("click.libLogs","#confirmGuestCheckIn", confirmGuestCheckIn)
    .on("click.libLogs",".btn-guest-checkout", function () { checkoutGuest($(this).data("logid"), $(this).data("name")); })
    .on("click.libLogs","#toggleSecretKey", () => togglePassword(live.secretKey, live.secretIcon))
    .on("click.libLogs","#dynamicModal .btn-close, #dynamicModal [data-dismiss='modal'], #dynamicModal [data-bs-dismiss='modal']", hideModal)
    .on("input.libLogs","#modalSecretKey", function () {
        const digits = $(this).val().replace(/\D/g, "").substring(0, 8);
        const formatted = digits.length > 4 ? `${digits.slice(0,2)}/${digits.slice(2,4)}/${digits.slice(4)}`
                        : digits.length > 2 ? `${digits.slice(0,2)}/${digits.slice(2)}`
                        : digits;
        $(this).val(formatted);
        if (digits.length === 8) {
            validateSecretKey(digits);
        } else {
            $(live.verified).hide().empty();
            setKeyStatus("muted", "fa-info-circle", "Enter birth date (MM/DD/YYYY)");
        }
    })
    .on("input.libLogs", "#guestSearchInput", function () {
        const query = $(this).val().toLowerCase().trim();
        let visible = 0;
        $(live.guestList).find(".guest-row").each(function () {
            const matches = ($(this).data("name") || "").toLowerCase().includes(query);
            $(this).toggle(matches);
            if (matches) visible++;
        });
        $(live.noResults).toggle(visible === 0 && query !== "");
    });

});
</script>