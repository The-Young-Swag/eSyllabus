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
$(function () {


    const UI = {

        buttons: {
            get guestCheckIn()       { return document.getElementById("guestCheckIn"); },
            get guestCheckOut()      { return document.getElementById("guestCheckOut"); },
            get toggleIdVisibility() { return document.getElementById("toggleIdVisibility"); },
        },

        icons: {
            get toggle() { return document.getElementById("toggleIcon"); },
            get secret() { return document.getElementById("secretIcon"); },
        },

        inputs: {
            get studentNumber() { return document.getElementById("inputStudentNumber"); },
            get secretKey()     { return document.getElementById("modalSecretKey"); },
            get guestName()     { return document.getElementById("guestName"); },
            get guestAgency()   { return document.getElementById("guestAgency"); },
            get guestSex()      { return document.getElementById("guestSex"); },
        },

        kpi: {
            get currentTime()    { return document.getElementById("kpiCurrentTime"); },
            get currentLibrary() { return document.getElementById("currentLibraryDisplay"); },
            get totalCheckins()  { return document.getElementById("kpiTotalCheckins"); },
            get activeStudents() { return document.getElementById("kpiActiveStudents"); },
            get topColleges()    { return document.getElementById("topColleges"); },
            get topCourses()     { return document.getElementById("topCourses"); },
        },

        modal: {
            get container() { return document.getElementById("dynamicModal"); },
            get title()     { return document.getElementById("dynamicModalTitle"); },
            get body()      { return document.getElementById("dynamicModalBody"); },
            get footer()    { return document.getElementById("dynamicModalFooter"); },
        },

        guest: {
            get nudgeTooltip() { return document.getElementById("guestNudge"); },
            get checkoutList() { return document.getElementById("guestCheckoutList"); },
            get noResults()    { return document.getElementById("guestNoResults"); },
        },

        validation: {
            get verifiedContainer() { return document.getElementById("verifiedStudentContainer"); },
            get secretKeyStatus()   { return document.getElementById("secretKeyStatus"); },
        },

    };


    const State = {
        currentLibraryID:    null,
        currentLibraryName:  "",
        selectedUser:        null,
        duplicateCandidates: [],
        currentAction:       "checkin",
        successTimer:        null,
        clockTimer:          null,
        nudgeTimer:          null,   // FIX: track nudge interval to prevent leak on re-injection
    };

    const BACKEND = "backend/bk_LibraryMenu/bk_libLogs-Test.php";

    const GET = {

        getLibraries: (userID) =>
            $.post(BACKEND, { request: "getLibraries", userID }),

        getKPI: (sectionID) =>
            $.post(BACKEND, { request: "getKPI", sectionID }),

        validateUser: (idNumber) =>
            $.post(BACKEND, { request: "getValidateUser", idNumber }),

        checkStatus: (idNumber, name) =>
            $.post(BACKEND, { request: "checkStatusToday", idNumber, name }),

        getAttendanceModal: (user, color, icon, btnText, message, libraryName) =>
            $.post(BACKEND, { request: "getAttendanceModal", user: JSON.stringify(user), color, icon, btnText, message, libraryName }),

        getGuestCheckInModal: (libraryName) =>
            $.post(BACKEND, { request: "guestCheckIn", libraryName }),

        getGuestCheckOutModal: (sectionID, libraryName) =>
            $.post(BACKEND, { request: "getGuestCheckoutModal", sectionID, libraryName }),

        guestCheckout: (logID, sectionID) =>
            $.post(BACKEND, { request: "guestCheckout", logID, sectionID }),

        saveAttendance: (user, action, sectionID) =>
            $.post(BACKEND, {
                request:             "getSaveAttendance",
                action,
                idNumber:            user.id_number,
                sectionID,
                classification:      user.classification      || "STUDENT",
                name:                user.name,
                college:             user.college             || "",
                course:              user.course              || "",
                sex:                 user.sex                 || "",
                agency_organization: user.agency_organization || "",
            }),

    };


    const Helpers = {

        // Clock -------------------------------------------------------
        // Clears any previous interval before starting, so re-injection
        // never spawns multiple ticking clocks on the same element.

        startClock() {
            if (State.clockTimer) clearInterval(State.clockTimer);

            const clockFormat = {
                hour: "2-digit", minute: "2-digit", second: "2-digit",
                year: "numeric", month: "short",    day:  "numeric", hour12: true,
            };
            const tick = () => $(UI.kpi.currentTime).text(new Date().toLocaleString("en-US", clockFormat));
            tick();
            State.clockTimer = setInterval(tick, 1000);
        },

        // Nudge tooltip -----------------------------------------------
        startGuestNudge() {
    if (State.nudgeTimer) clearTimeout(State.nudgeTimer);

    const showTooltip = () => {
        UI.guest.nudgeTooltip.style.opacity = "1"; // show
        // hide after 5 seconds
        State.nudgeTimer = setTimeout(() => {
            UI.guest.nudgeTooltip.style.opacity = "0"; // hide
            // show again after 2 seconds
            State.nudgeTimer = setTimeout(showTooltip, 2000);
        }, 5000);
    };
    // initial delay of 10 seconds
    State.nudgeTimer = setTimeout(showTooltip, 2000);
},

        // Hover styles ------------------------------------------------
        applyHover(targetElement, enterStyles, leaveStyles) {
            $(targetElement)
                .off("mouseenter.libLogs mouseleave.libLogs")   // FIX: prevent stacking on re-injection
                .on("mouseenter.libLogs", function () { $(this).css(enterStyles); })
                .on("mouseleave.libLogs", function () { $(this).css(leaveStyles); });
        },

        // Password toggle ---------------------------------------------
        // Shared logic for both the ID field and the secret key field.
        togglePassword(inputElement, iconElement) {
            const $inputField  = $(inputElement);
            const isHidden     = $inputField.attr("type") === "password";
            $inputField.attr("type", isHidden ? "text" : "password");
            $(iconElement).toggleClass("fa-eye", !isHidden).toggleClass("fa-eye-slash", isHidden);
        },

        // Modal -------------------------------------------------------
        showModal(title, body, footer = "") {
            if (State.successTimer) { clearTimeout(State.successTimer); State.successTimer = null; }
            $(UI.modal.title).html(title);
            $(UI.modal.body).html(body);
            $(UI.modal.footer).html(footer);
            $(UI.modal.container).modal("show");
        },

        hideModal() {
            if (State.successTimer) { clearTimeout(State.successTimer); State.successTimer = null; }
            $(UI.modal.container).modal("hide");
        },

        showSuccessModal(name, label) {
            Helpers.showModal("Success",
                `<div class="alert alert-success text-center mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>${name}</strong> successfully ${label}.
                 </div>`
            );
            State.successTimer = setTimeout(() => {
                State.successTimer = null;
                $(UI.modal.container).modal("hide");
            }, 2000);
        },

        showErrorModal(message) {
            Helpers.showModal("Error",
                `<div class="alert alert-danger text-center mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>${message}
                 </div>`
            );
        },

        // KPI ---------------------------------------------------------

        renderKPI(kpiData) {
            $(UI.kpi.totalCheckins).text(kpiData.totalToday       ?? 0);
            $(UI.kpi.activeStudents).text(kpiData.currentlyInside ?? 0);

            $(UI.kpi.topColleges).html(
                (kpiData.topColleges || ["-", "-", "-"]).map((collegeName, i) =>
                    `<div class="mb-1">
                        <span class="fw-bold">${i + 1}.</span>
                        <span class="text-warning">${collegeName}</span>
                     </div>`
                ).join("")
            );

            $(UI.kpi.topCourses).html(
                (kpiData.topCourses || ["-", "-", "-"]).map((courseName, i) =>
                    `<div class="mb-1">
                        <span class="fw-bold">${i + 1}.</span>
                        <span class="text-info">${courseName}</span>
                     </div>`
                ).join("")
            );
        },

        // Secret key status -------------------------------------------

        setSecretKeyStatus(type, faIcon, text) {
            const colorClass = type === "success" ? "text-success"
                             : type === "danger"  ? "text-danger"
                             : "text-muted";
            $(UI.validation.secretKeyStatus).html(
                `<span class="${colorClass}"><i class="fas ${faIcon} me-1"></i>${text}</span>`
            );
        },

    };

    const Actions = {

        loadLibraries() {
            if (typeof UserInfo === "undefined" || !UserInfo.UserID) {
                $(UI.kpi.currentLibrary).text("User not logged in");
                return;
            }

            GET.getLibraries(UserInfo.UserID)
                .done(libraryRes => {
                    if (!libraryRes.success || !libraryRes.data?.length) {
                        State.currentLibraryName = libraryRes.error ?? "No Library Access";
                        State.currentLibraryID   = null;
                        $(UI.kpi.currentLibrary).text(State.currentLibraryName);
                        return;
                    }

                    const library            = libraryRes.data[0];
                    State.currentLibraryID   = library.SectionID;
                    State.currentLibraryName = library.SectionName;
                    $(UI.kpi.currentLibrary).text(State.currentLibraryName);
                    Actions.loadKPI();
                })
                .fail(xhr => {
                    $(UI.kpi.currentLibrary).text("Connection error");
                    console.error("getLibraries failed:", xhr.responseText);
                });
        },

        loadKPI() {
            if (!State.currentLibraryID) return;
            GET.getKPI(State.currentLibraryID)
                .done(kpiRes => { if (kpiRes.success && kpiRes.data) Helpers.renderKPI(kpiRes.data); });
        },

        validateUser() {
            const idNumber = $(UI.inputs.studentNumber).val().trim();
            if (!idNumber) return alert("Please enter an Identification Number.");

            GET.validateUser(idNumber)
                .done(validationRes => {
                    if (validationRes.error) {
                        alert("No record found for that ID number.");
                        $(UI.inputs.studentNumber).val("").focus();
                        return;
                    }

                    if (validationRes.duplicate) {
                        State.duplicateCandidates = validationRes.matches;
                        Helpers.showModal("Identity Verification", validationRes.modalHTML, "");
                        return;
                    }

                    State.selectedUser = validationRes.data;
                    GET.checkStatus(State.selectedUser.id_number, State.selectedUser.name)
                        .done(status => Actions.determineAttendanceAction(State.selectedUser, status));
                });
        },

        determineAttendanceAction(user, status) {
            let action  = "checkin",        color   = "success";
            let icon    = "fa-sign-in-alt", btnText = "Check In";
            let message = "Not checked in yet";

            if (status.checkedIn) {
                const sameSection = Number(status.sectionID) === Number(State.currentLibraryID);
                if (sameSection) {
                    action  = "checkout"; color   = "danger";
                    icon    = "fa-sign-out-alt"; btnText = "Check Out";
                    message = "Currently in this library";
                } else {
                    const prevLibrary = status.sectionName || "another library";
                    action  = "switch"; color   = "warning";
                    icon    = "fa-random"; btnText = "Switch & Check In";
                    message = `You forgot to check out at <strong>${prevLibrary}</strong>. No worries — we've automatically checked you out and completed your check-in here.`;
                }
            }

            State.currentAction = action;

            GET.getAttendanceModal(user, color, icon, btnText, message, State.currentLibraryName)
                .done(attendanceRes => attendanceRes.success && Helpers.showModal("Attendance Confirmation", attendanceRes.body, attendanceRes.footer));
        },

        validateSecretKey(key) {
            const match = State.duplicateCandidates.find(candidate => candidate.secretKey?.replace(/\D/g, "") === key);

            if (match) {
                State.selectedUser = match;
                Helpers.setSecretKeyStatus("success", "fa-check-circle", "Identity verified");
                $(UI.validation.verifiedContainer).show();
                GET.checkStatus(State.selectedUser.id_number, State.selectedUser.name)
                    .done(status => Actions.determineAttendanceAction(State.selectedUser, status));
            } else {
                Helpers.setSecretKeyStatus("danger", "fa-exclamation-circle", "Invalid key — try again");
                $(UI.validation.verifiedContainer).hide().empty();
            }
        },

        processAttendance(user, action) {
            if (!State.currentLibraryID) return;
            if (user.classification !== "GUEST" && !user.id_number) return;

            const resolvedAction = action === "switch" ? "checkin" : action;
            const label          = resolvedAction === "checkin" ? "checked in" : "checked out";

            Helpers.showSuccessModal(user.name, label);

            GET.saveAttendance(user, resolvedAction, State.currentLibraryID)
                .done(saveRes => {
                    if (saveRes.error) { Helpers.showErrorModal(saveRes.error); return; }
                    Actions.loadKPI();
                    $(UI.inputs.studentNumber).val("");
                });
        },

        openGuestCheckIn() {
            if (!State.currentLibraryID) return alert("Library section not loaded yet.");
            GET.getGuestCheckInModal(State.currentLibraryName)
                .done(guestCheckInRes => {
                    if (!guestCheckInRes.success) return alert(guestCheckInRes.error || "Failed to load guest form.");
                    Helpers.showModal("Guest Check-In", guestCheckInRes.body, guestCheckInRes.footer);
                })
                .fail(xhr => { console.error(xhr.responseText); alert("Connection error."); });
        },

        openGuestCheckOut() {
            if (!State.currentLibraryID) return alert("Library section not loaded yet.");
            GET.getGuestCheckOutModal(State.currentLibraryID, State.currentLibraryName)
                .done(guestCheckOutRes => {
                    if (!guestCheckOutRes.success) return alert(guestCheckOutRes.error || "Failed to load guest list.");
                    Helpers.showModal("Guest Check-Out", guestCheckOutRes.body, guestCheckOutRes.footer);
                })
                .fail(xhr => { console.error(xhr.responseText); alert("Connection error."); });
        },

        checkoutGuest(logID, guestName) {
    if (!logID) return;
    GET.guestCheckout(logID, State.currentLibraryID)
        .done(checkoutRes => {
            if (checkoutRes.error) { alert(checkoutRes.error); return; }

            $(`.btn-guest-checkout[data-logid="${logID}"]`)
                .closest(".guest-row")
                .fadeOut(200, function () {
                    $(this).remove();
                    Actions.updateGuestCheckoutModalUI();   // <-- new line
                });

            // Prepend success alert (unchanged)
            const $alert = $(`<div class="alert alert-success py-2 px-3 mb-2 text-center">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>${guestName}</strong> successfully checked out.
                </div>`).prependTo(UI.guest.checkoutList);
            setTimeout(() => $alert.fadeOut(400, () => $alert.remove()), 2000);

            Actions.loadKPI();
        })
        .fail(() => alert("Connection error."));
},

        updateGuestCheckoutModalUI() {
    const $list = $(UI.guest.checkoutList);
    const guestRows = $list.find('.guest-row');
    const count = guestRows.length;

    // Update the badge (assumes it’s the only span with class 'badge' in the modal)
    const $badge = $('#dynamicModal .badge.rounded-pill');
    $badge.text(count + ' ' + (count === 1 ? 'guest' : 'guests'));

    // Show/hide the empty state
    const $emptyState = $('#guestEmptyState');
    if (count === 0) {
        $emptyState.show();
    } else {
        $emptyState.hide();
    }

    // Re-trigger search filtering to update visible rows and no‑results message
    $('#guestSearchInput').trigger('input');
},

        confirmGuestCheckIn() {
            const name   = $(UI.inputs.guestName).val().trim();
            const sex    = $(UI.inputs.guestSex).val();
            const agency = $(UI.inputs.guestAgency).val().trim();

            if (!name)   { alert("Guest name is required.");         return; }
            if (!sex)    { alert("Please select a sex.");            return; }
            if (!agency) { alert("Agency / Organization required."); return; }

            Actions.processAttendance({
                id_number: "", name, classification: "GUEST",
                college: "", course: "", sex, agency_organization: agency,
            }, "checkin");
        },

    };

    $(document).off(".libLogs"); // Clear stale delegated handlers from any previous AJAX injection

    // Boot
    Helpers.startClock();
    Helpers.startGuestNudge();
    Actions.loadLibraries();

    // Hover styles
    // FIX: namespaced mouseenter/mouseleave inside applyHover() prevents stacking on re-injection
    Helpers.applyHover(
        UI.buttons.guestCheckIn,
        { background: "#d1fae5", boxShadow: "0 4px 14px rgba(6,78,59,.15)",    transform: "translateY(-1px)" },
        { background: "#f0fdf9", boxShadow: "",                                transform: "" }
    );
    Helpers.applyHover(
        UI.buttons.guestCheckOut,
        { background: "#fee2e2", boxShadow: "0 4px 14px rgba(220,38,38,.15)", transform: "translateY(-1px)" },
        { background: "#fff5f5", boxShadow: "",                                transform: "" }
    );

    // FIX: direct binds are now namespaced and preceded by .off() so they don't
    // stack on AJAX re-injection (delegated binds are already cleared above via .off(".libLogs"))
    $(UI.buttons.guestCheckIn).off("click.libLogs").on("click.libLogs",  () => Actions.openGuestCheckIn());
    $(UI.buttons.guestCheckOut).off("click.libLogs").on("click.libLogs", () => Actions.openGuestCheckOut());
    $(UI.buttons.toggleIdVisibility).off("click.libLogs").on("click.libLogs", () =>
        Helpers.togglePassword(UI.inputs.studentNumber, UI.icons.toggle)
    );

    // Delegated binds — namespaced to prevent stacking on re-injection
    $(document).on("submit.libLogs", "#logForm", e => { e.preventDefault(); Actions.validateUser(); });

    $(document).on(                                                                             // PHP: buildAttendanceModalHTML()
        "click.libLogs",
        "#dynamicModal .btn-close, #dynamicModal [data-dismiss='modal'], #dynamicModal [data-bs-dismiss='modal']",
        () => Helpers.hideModal()
    );

    $(document).on("click.libLogs", "#confirmAttendance", () => {                              // PHP: buildAttendanceModalHTML()
        if (State.selectedUser && State.currentAction) {
            Actions.processAttendance(State.selectedUser, State.currentAction);
        }
    });

    $(document).on("click.libLogs",  "#confirmGuestCheckIn",  () => Actions.confirmGuestCheckIn());  // PHP: GuestCheckInModal()

    $(document).on("click.libLogs", ".btn-guest-checkout", function () {                             // PHP: GuestCheckoutModal()
        Actions.checkoutGuest($(this).data("logid"), $(this).data("name"));
    });

    $(document).on("input.libLogs", "#guestSearchInput", function () {
    const searchQuery = $(this).val().toLowerCase().trim();
    let visibleCount = 0;

    $(UI.guest.checkoutList).find(".guest-row").each(function () {
        const name = $(this).data("name") || "";          // fallback to empty string
        const matches = name.toLowerCase().includes(searchQuery);
        $(this).toggle(matches);
        if (matches) visibleCount++;
    });

    // Show "no results" only when there is a non‑empty search and no visible rows
    if (visibleCount === 0 && searchQuery !== "") {
        $(UI.guest.noResults).show();
    } else {
        $(UI.guest.noResults).hide();
    }
});

    $(document).on("click.libLogs", "#toggleSecretKey", () =>                                        // PHP: DuplicateModal()
        Helpers.togglePassword(UI.inputs.secretKey, UI.icons.secret)
    );

    $(document).on("input.libLogs", "#modalSecretKey", function () {                                 // PHP: DuplicateModal()
        let raw = $(this).val().replace(/\D/g, "").substring(0, 8);

        let formatted = raw;
        if      (raw.length > 4) formatted = raw.slice(0, 2) + "/" + raw.slice(2, 4) + "/" + raw.slice(4);
        else if (raw.length > 2) formatted = raw.slice(0, 2) + "/" + raw.slice(2);
        $(this).val(formatted);

        if (raw.length === 8) {
            Actions.validateSecretKey(raw);
        } else {
            $(UI.validation.verifiedContainer).hide().empty();
            Helpers.setSecretKeyStatus("muted", "fa-info-circle", "Enter birth date (MM/DD/YYYY)");
        }
    });

});
</script>