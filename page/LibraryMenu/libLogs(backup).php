<script>
$(function () {

    // ─── Guard: prevent duplicate init (e.g. AJAX re-injection) ─────────────
    if (window.__libLogsInitialized) return;
    window.__libLogsInitialized = true;

    const BACKEND = "backend/bk_LibraryMenu/bk_libLogs-Test.php";

    // ─── Wipe delegated leftovers only ───────────────────────────────────────
    $(document).off(".libLogs");

    // ─── Module state ────────────────────────────────────────────────────────
    let libraryID     = null;
    let libraryName   = "";
    let currentUser   = null;
    let currentAction = null;
    let duplicates    = [];
    let successTimer  = null;

    // ─── Clock ───────────────────────────────────────────────────────────────
    setInterval(function () {
        $("#kpiCurrentTime").text(new Date().toLocaleString("en-US", {
            hour: "2-digit", minute: "2-digit", second: "2-digit",
            year: "numeric", month: "short", day: "numeric", hour12: true
        }));
    }, 1000);

    // ─── Guest nudge animation ───────────────────────────────────────────────
    setTimeout(function cycle() {
        $("#guestNudge").css("opacity", "1");
        setTimeout(function () {
            $("#guestNudge").css("opacity", "0");
            setTimeout(cycle, 2000);
        }, 5000);
    }, 2000);

    // ─── Hover effects ───────────────────────────────────────────────────────
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

    // ─── Modal helpers ───────────────────────────────────────────────────────
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
        const styles = {
            success: ["fa-check-circle",       "alert-success", "Success"],
            error:   ["fa-exclamation-circle",  "alert-danger",  "Error"],
            info:    ["fa-info-circle",          "alert-info",    "Info"]
        };
        const [icon, colorClass, title] = styles[type];
        showModal(title, `
            <div class="alert ${colorClass} text-center mb-0">
                <i class="fas ${icon} me-2"></i>${message}
            </div>
        `);
        if (autoClose) successTimer = setTimeout(hideModal, autoClose);
    }

    // ─── KPI ─────────────────────────────────────────────────────────────────
    function loadKPI() {
        if (!libraryID) return;
        $.ajax({
            type: "POST",
            url: BACKEND,
            data: { request: "getKPI", sectionID: libraryID },
            success: function (response) {
                if (!response.success || !response.data) return;
                $("#kpiTotalCheckins").text(response.data.totalToday       || 0);
                $("#kpiActiveStudents").text(response.data.currentlyInside || 0);

                function renderRankList(items, colorClass) {
                    if (!items) items = ["-", "-", "-"];
                    return items.map((label, i) =>
                        `<div class="mb-1">
                            <span class="fw-bold">${i + 1}.</span>
                            <span class="${colorClass}">${label}</span>
                        </div>`
                    ).join("");
                }
                $("#topColleges").html(renderRankList(response.data.topColleges, "text-warning"));
                $("#topCourses").html(renderRankList(response.data.topCourses,   "text-info"));
            },
            error: function () { alert("Connection error."); }
        });
    }

    // ─── Boot: load library ──────────────────────────────────────────────────
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getLibraries", userID: UserInfo.UserID },
        success: function (response) {
            if (!response.success || !response.data || !response.data.length) {
                $("#currentLibraryDisplay").text(response.error || "No Library Access");
                return;
            }
            libraryID   = response.data[0].SectionID;
            libraryName = response.data[0].SectionName;
            $("#currentLibraryDisplay").text(libraryName);
            loadKPI();
        },
        error: function () { alert("Connection error."); }
    });

    // ─── Guest UI update ─────────────────────────────────────────────────────
    function updateGuestUI() {
        const count = $("#guestCheckoutList .guest-row").length;
        $("#dynamicModal .badge.rounded-pill").text(`${count} ${count === 1 ? "guest" : "guests"}`);
        $("#guestEmptyState").toggle(count === 0);
        $("#guestSearchInput").trigger("input");
    }

    // ─── Attendance helpers ──────────────────────────────────────────────────
    function checkAndShowAttendance(user) {
        $.post(BACKEND, {
            request:  "checkStatusToday",
            idNumber: user.id_number,
            name:     user.name
        })
        .then(function (status) {
            const resolved = !status.checkedIn                              ? "checkin"
                           : Number(status.sectionID) === Number(libraryID) ? "checkout"
                           : "switch";

            currentAction = resolved === "switch" ? "checkin" : resolved;

            return $.post(BACKEND, {
                request:     "getAttendanceModal",
                user:        JSON.stringify(user),
                action:      resolved,
                sectionName: status.sectionName || "",
                libraryName: libraryName
            });
        })
        .then(function (modal) {
            if (modal.success) showModal("Attendance Confirmation", modal.body, modal.footer);
        })
        .fail(function () {
            showMessage("error", "Connection error. Please try again.");
        });
    }

    function saveAttendance(user, action) {
        if (!libraryID) return;
        if (user.classification !== "GUEST" && !user.id_number) return;

        $.ajax({
            type: "POST",
            url: BACKEND,
            data: {
                request:             "getSaveAttendance",
                action:              action,
                idNumber:            user.id_number,
                sectionID:           libraryID,
                classification:      user.classification || "STUDENT",
                name:                user.name,
                college:             user.college || "",
                course:              user.course  || "",
                sex:                 user.sex     || "",
                agency_organization: user.agency_organization || ""
            },
            success: function (response) {
                if (response.error) { showMessage("error", response.error); return; }
                const label = action === "checkin" ? "checked in" : "checked out";
                showMessage("success", `<strong>${user.name}</strong> successfully ${label}.`, 2000);
                loadKPI();
                $("#inputIDNumber").val("");
            },
            error: function () {
                showMessage("error", "Connection error. Please try again.");
            }
        });
    }

    // =========================================================================
    //  EVENT BINDINGS
    //  Direct   → static elements, no namespace needed, guard already protects
    //  Delegated → dynamic/modal elements, namespaced so .off(".libLogs") clears cleanly
    // =========================================================================

    // ─── Direct bindings (static elements) ───────────────────────────────────
    $("#logForm").on("submit", function (e) {
        e.preventDefault();
        const id = $("#inputIDNumber").val().trim();
        if (!id) { alert("Please enter an Identification Number."); return; }

        $.ajax({
            type: "POST",
            url: BACKEND,
            data: { request: "getValidateUser", idNumber: id },
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
            error: function () { alert("Connection error."); }
        });
    });

    $("#toggleIdVisibility").on("click", function () {
        const $input   = $("#inputIDNumber");
        const isHidden = $input.attr("type") === "password";
        $input.attr("type", isHidden ? "text" : "password");
        $("#toggleIcon").toggleClass("fa-eye", !isHidden).toggleClass("fa-eye-slash", isHidden);
    });

    $("#guestCheckIn").on("click", function () {
        if (!libraryID) { alert("Library section not loaded yet."); return; }
        $.ajax({
            type: "POST",
            url: BACKEND,
            data: { request: "guestCheckIn", libraryName: libraryName },
            success: function (response) {
                if (response.success) showModal("Guest Check-In", response.body, response.footer);
                else alert(response.error || "Failed to load guest form.");
            },
            error: function () { alert("Connection error."); }
        });
    });

    $("#guestCheckOut").on("click", function () {
        if (!libraryID) { alert("Library section not loaded yet."); return; }
        $.ajax({
            type: "POST",
            url: BACKEND,
            data: { request: "getGuestCheckoutModal", sectionID: libraryID, libraryName: libraryName },
            success: function (response) {
                if (response.success) showModal("Guest Check-Out", response.body, response.footer);
                else alert(response.error || "Failed to load guest list.");
            },
            error: function () { alert("Connection error."); }
        });
    });

    // ─── Delegated bindings (dynamic/modal elements, namespaced) ─────────────
    $(document).on("click.libLogs", "#confirmAttendance", function () {
        if (!currentUser || !currentAction) return;
        saveAttendance(currentUser, currentAction);
    });

    $(document).on("click.libLogs", "#confirmGuestCheckIn", function () {
        const name         = $("#guestName").val().trim();
        const sex          = $("#guestSex").val();
        const organization = $("#guestAgency").val().trim();
        if (!name)         { alert("Guest name is required.");         return; }
        if (!sex)          { alert("Please select a sex.");            return; }
        if (!organization) { alert("Agency / Organization required."); return; }
        saveAttendance({
            id_number: "", name,
            classification: "GUEST",
            college: "", course: "", sex,
            agency_organization: organization
        }, "checkin");
    });

    $(document).on("click.libLogs", ".btn-guest-checkout", function () {
        const logID     = $(this).data("logid");
        const guestName = $(this).data("name");
        if (!logID) return;

        $.ajax({
            type: "POST",
            url: BACKEND,
            data: { request: "guestCheckout", logID: logID, sectionID: libraryID },
            success: function (response) {
                if (response.error) { alert(response.error); return; }
                $(`.btn-guest-checkout[data-logid="${logID}"]`)
                    .closest(".guest-row")
                    .fadeOut(200, function () { $(this).remove(); updateGuestUI(); });
                $(`<div class="alert alert-success py-2 px-3 mb-2 text-center">
                       <i class="fas fa-check-circle me-1"></i>
                       <strong>${guestName}</strong> successfully checked out.
                   </div>`)
                    .prependTo("#guestCheckoutList")
                    .delay(2000)
                    .fadeOut(400, function () { $(this).remove(); });
                loadKPI();
            },
            error: function () { alert("Connection error."); }
        });
    });

    $(document).on("input.libLogs", "#guestSearchInput", function () {
        const query = $(this).val().toLowerCase().trim();
        let visible = 0;
        $("#guestCheckoutList .guest-row").each(function () {
            const matches = ($(this).data("name") || "").toLowerCase().includes(query);
            $(this).toggle(matches);
            if (matches) visible++;
        });
        $("#guestNoResults").toggle(visible === 0 && query !== "");
    });

    $(document).on("input.libLogs", "#modalSecretKey", function () {
        const digits    = $(this).val().replace(/\D/g, "").substring(0, 8);
        const formatted = digits.length > 4 ? `${digits.slice(0,2)}/${digits.slice(2,4)}/${digits.slice(4)}`
                        : digits.length > 2 ? `${digits.slice(0,2)}/${digits.slice(2)}`
                        : digits;
        $(this).val(formatted);

        if (digits.length < 8) {
            $("#verifiedStudentContainer").hide().empty();
            $("#secretKeyStatus").html(`<span class="text-muted"><i class="fas fa-info-circle me-1"></i>Enter birth date (MM/DD/YYYY)</span>`);
            return;
        }

        const match = duplicates.find(c => c.secretKey?.replace(/\D/g, "") === digits);
        if (match) {
            currentUser = match;
            $("#secretKeyStatus").html(`<span class="text-success"><i class="fas fa-check-circle me-1"></i>Identity verified</span>`);
            $("#verifiedStudentContainer").show();
            checkAndShowAttendance(match);
        } else {
            $("#secretKeyStatus").html(`<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Invalid key — try again</span>`);
            $("#verifiedStudentContainer").hide().empty();
        }
    });

    $(document).on("click.libLogs", "#toggleSecretKey", function () {
        const $input   = $("#modalSecretKey");
        const isHidden = $input.attr("type") === "password";
        $input.attr("type", isHidden ? "text" : "password");
        $("#secretIcon").toggleClass("fa-eye", !isHidden).toggleClass("fa-eye-slash", isHidden);
    });

    $(document).on("click.libLogs",
        "#dynamicModal .btn-close, #dynamicModal [data-dismiss='modal'], #dynamicModal [data-bs-dismiss='modal']",
        hideModal
    );

});
</script>