// ============================================================
// REPORTS JAVASCRIPT
// ============================================================
// This file handles frontend behaviour for the admin
// Reports page.
//
// IMPORTANT FOR BACKEND DEVELOPERS:
// The report information currently displayed on the page
// is temporary/dummy data.
//
// The backend should later retrieve the real booking,
// service and revenue information from the database
// based on the selected date range.
// ============================================================


// ============================================================
// GENERATE REPORT
// ============================================================

const generateReportBtn =
    document.getElementById("generateReportBtn");

const reportStartDate =
    document.getElementById("reportStartDate");

const reportEndDate =
    document.getElementById("reportEndDate");


generateReportBtn.addEventListener("click", function(){

    const startDate =
        reportStartDate.value;

    const endDate =
        reportEndDate.value;


    // Make sure both dates have been selected.

    if(startDate === "" || endDate === ""){

        alert(
            "Please select a start date and an end date."
        );

        return;

    }


    // Make sure the end date is not before
    // the start date.

    if(endDate < startDate){

        alert(
            "The end date cannot be before the start date."
        );

        return;

    }


    // ========================================================
    // TEMPORARY REPORT DATA
    // ========================================================
    // These values are only used for frontend testing.
    //
    // Backend developers should later replace these values
    // with information retrieved from the database.
    // ========================================================

    const reportData = {

        totalBookings: 54,

        completedBookings: 46,

        cancelledBookings: 4,

        totalRevenue: "R2,100.00"

    };


    // ========================================================
    // UPDATE REPORT SUMMARY
    // ========================================================

    document.getElementById(
        "reportTotalBookings"
    ).textContent =
        reportData.totalBookings;


    document.getElementById(
        "reportCompletedBookings"
    ).textContent =
        reportData.completedBookings;


    document.getElementById(
        "reportCancelledBookings"
    ).textContent =
        reportData.cancelledBookings;


    document.getElementById(
        "reportTotalRevenue"
    ).textContent =
        reportData.totalRevenue;


    // ========================================================
    // ADD REPORT ACTIVITY
    // ========================================================

    addReportActivity(
        startDate,
        endDate
    );


    // ========================================================
    // TEMPORARY SUCCESS MESSAGE
    // ========================================================

    alert(
        "Report generated successfully."
    );

});


// ============================================================
// REPORT ACTIVITY
// ============================================================

function addReportActivity(
    startDate,
    endDate
){

    const activityContainer =
        document.getElementById(
            "reportActivityContainer"
        );


    const activity =
        document.createElement("div");

    activity.className =
        "report-activity";


    const activityIcon =
        document.createElement("div");

    activityIcon.className =
        "report-activity-icon";

    activityIcon.innerHTML =
        '<i class="fa-solid fa-chart-column"></i>';


    const activityContent =
        document.createElement("div");

    activityContent.className =
        "report-activity-content";


    const activityMessage =
        document.createElement("p");

    activityMessage.innerHTML =
        "Report generated for <strong>" +
        formatDate(startDate) +
        " to " +
        formatDate(endDate) +
        "</strong>.";


    const activityTime =
        document.createElement("span");

    activityTime.textContent =
        "Just now";


    activityContent.appendChild(
        activityMessage
    );

    activityContent.appendChild(
        activityTime
    );


    activity.appendChild(
        activityIcon
    );

    activity.appendChild(
        activityContent
    );


    activityContainer.prepend(
        activity
    );

}


// ============================================================
// FORMAT DATE
// ============================================================
// Converts:
// 2026-09-21
//
// Into:
// 21/09/2026
// ============================================================

function formatDate(dateValue){

    const dateParts =
        dateValue.split("-");

    return (
        dateParts[2] +
        "/" +
        dateParts[1] +
        "/" +
        dateParts[0]
    );

}


// ============================================================
// BACKEND INFORMATION
// ============================================================
// Backend developers can later replace the temporary
// reportData object with information retrieved from
// the database.
//
// The backend should use the selected:
//
// #reportStartDate
// #reportEndDate
//
// to retrieve the relevant booking information.
//
// The following IDs are available for backend use:
//
// #reportTotalBookings
// #reportCompletedBookings
// #reportCancelledBookings
// #reportTotalRevenue
// #serviceReportTableBody
// #methodReportTableBody
// #reportActivityContainer
//
// ============================================================


// ============================================================
// FUTURE BACKEND FLOW
// ============================================================
//
// Admin selects date range
//          ↓
// Click Generate Report
//          ↓
// Backend receives date range
//          ↓
// Database is checked
//          ↓
// Bookings / services / revenue are calculated
//          ↓
// Reports page displays real information
//
// ============================================================