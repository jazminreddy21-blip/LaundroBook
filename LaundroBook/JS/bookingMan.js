/* =========================================================
   LAUNDROBOOK - BOOKING MANAGEMENT JAVASCRIPT
========================================================= */


/* =========================================================
   SEARCH BOOKINGS
========================================================= */

const bookingSearchForm = document.getElementById("bookingSearchForm");
const bookingSearch = document.getElementById("bookingSearch");


/*
    Frontend search for testing.

    Backend:
    The backend developers can later connect the search
    to the database using the booking reference or
    customer name.
*/

bookingSearchForm.addEventListener("submit", function(event){

    event.preventDefault();

    const searchValue = bookingSearch.value.toLowerCase().trim();

    const bookingRows = document.querySelectorAll(".booking-row");


    bookingRows.forEach(function(row){

        const bookingReference =
            row.querySelector(".booking-reference").textContent.toLowerCase();

        const customerName =
            row.querySelector(".customer-name").textContent.toLowerCase();


        if(
            bookingReference.includes(searchValue) ||
            customerName.includes(searchValue)
        ){

            row.style.display = "";

        }else{

            row.style.display = "none";

        }

    });

});


/* =========================================================
   UPDATE BOOKING STATUS
========================================================= */


/*
    Backend:
    When connected to the backend, the booking ID and
    selected status should be sent to the backend.

    The backend will then update the booking status
    in the database.
*/

const saveButtons = document.querySelectorAll(".save-status-btn");


saveButtons.forEach(function(button){

    button.addEventListener("click", function(){

        const bookingId = button.getAttribute("data-booking-id");


        const statusSelect =
            document.querySelector(
                '.booking-status-select[data-booking-id="' + bookingId + '"]'
            );


        const selectedStatus = statusSelect.value;


        /* Check that a status was selected */

        if(selectedStatus === ""){

            alert("Please select a booking status.");

            return;

        }


        /*
            Find the booking row.
        */

        const bookingRow =
            document.querySelector(
                '.booking-row[data-booking-id="' + bookingId + '"]'
            );


        /*
            Find the current status displayed
            for this booking.
        */

        const statusBadge =
            bookingRow.querySelector(".status-badge");

        const currentStatus =
            bookingRow.querySelector(".booking-current-status");


        /*
            Convert the database-style status value
            into readable text.
        */

        const statusText = {

            pending: "Pending",

            accepted: "Accepted",

            in_washing: "In Washing",

            ready_for_collection: "Ready for Collection",

            ready_for_delivery: "Ready for Delivery",

            out_for_delivery: "Out for Delivery",

            completed: "Completed",

            cancelled: "Cancelled"

        };


        /*
            Update the status displayed on the page.
        */

        currentStatus.textContent =
            statusText[selectedStatus];


        /*
            Remove the previous status CSS class.
        */

        statusBadge.classList.remove(
            "pending",
            "accepted",
            "washing",
            "ready-collection",
            "ready-delivery",
            "out-for-delivery",
            "completed",
            "cancelled"
        );


        /*
            Add the CSS class for the new status.
        */

        const statusClass = {

            pending: "pending",

            accepted: "accepted",

            in_washing: "washing",

            ready_for_collection: "ready-collection",

            ready_for_delivery: "ready-delivery",

            out_for_delivery: "out-for-delivery",

            completed: "completed",

            cancelled: "cancelled"

        };


        statusBadge.classList.add(
            statusClass[selectedStatus]
        );


        /*
            Add the change to Recent Updates.
        */

        addRecentUpdate(
            bookingId,
            statusText[selectedStatus]
        );


        /*
            Frontend testing only.

            Backend developers can replace this later
            with the request that saves the status
            to the database.
        */

        alert(
            "Booking #" +
            bookingId +
            " status updated to " +
            statusText[selectedStatus] +
            "."
        );


        /*
            Reset the dropdown after saving.
        */

        statusSelect.value = "";

    });

});


/* =========================================================
   RECENT BOOKING UPDATES
========================================================= */


/*
    Adds a new status update to the Recent Updates section.

    Backend:
    The backend can later populate this section with
    real booking status updates from the database.
*/

function addRecentUpdate(bookingId, newStatus){

    const updatesContainer =
        document.getElementById("bookingUpdatesContainer");


    /* Create the update */

    const update =
        document.createElement("div");

    update.className = "booking-update";


    /* Create the icon */

    const updateIcon =
        document.createElement("div");

    updateIcon.className = "update-icon";

    updateIcon.innerHTML =
        '<i class="fa-solid fa-rotate"></i>';


    /* Create the update content */

    const updateContent =
        document.createElement("div");

    updateContent.className = "update-content";


    /* Create the update message */

    const updateMessage =
        document.createElement("p");

    updateMessage.innerHTML =
        'Booking <strong class="update-booking-reference">#' +
        bookingId +
        '</strong> was updated to <strong class="update-booking-status">' +
        newStatus +
        '</strong>.';


    /* Create the time */

    const updateTime =
        document.createElement("span");

    updateTime.className = "update-time";

    updateTime.textContent = "Just now";


    /* Put everything together */

    updateContent.appendChild(updateMessage);

    updateContent.appendChild(updateTime);

    update.appendChild(updateIcon);

    update.appendChild(updateContent);


    /*
        Add the newest update at the top.
    */

    updatesContainer.prepend(update);

}