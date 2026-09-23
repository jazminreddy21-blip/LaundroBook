// ============================================================
// MACHINE MANAGEMENT JAVASCRIPT
// ============================================================
// This file handles frontend behaviour for the admin
// Machine Management page.
//
// IMPORTANT FOR BACKEND DEVELOPERS:
// The machine information used here is currently
// frontend/dummy data.
//
// The backend should later retrieve the real machine
// information from the database and save availability
// changes to the database.
// ============================================================


// ============================================================
// MACHINE SEARCH
// ============================================================

const machineSearchForm =
    document.getElementById("machineSearchForm");

const machineSearch =
    document.getElementById("machineSearch");


machineSearchForm.addEventListener("submit", function(event){

    event.preventDefault();

    const searchValue =
        machineSearch.value.toLowerCase().trim();

    const machineRows =
        document.querySelectorAll(".machine-row");


    machineRows.forEach(function(row){

        const machineNumber =
            row.querySelector(".machine-number")
            .textContent
            .toLowerCase();


        if(machineNumber.includes(searchValue)){

            row.style.display = "";

        }
        else{

            row.style.display = "none";

        }

    });

});


// ============================================================
// UPDATE MACHINE AVAILABILITY
// ============================================================

const saveMachineButtons =
    document.querySelectorAll(".save-machine-status-btn");


saveMachineButtons.forEach(function(button){

    button.addEventListener("click", function(){

        const machineId =
            button.getAttribute("data-machine-id");


        const statusSelect =
            document.querySelector(
                '.machine-status-select[data-machine-id="' +
                machineId +
                '"]'
            );


        const selectedStatus =
            statusSelect.value;


        // Make sure a status was selected.
        if(selectedStatus === ""){

            alert("Please select a machine status.");

            return;

        }


        // ====================================================
        // STATUS TEXT
        // ====================================================

        const statusText = {

            available: "Available",

            in_use: "In Use"

        };


        const newStatus =
            statusText[selectedStatus];


        // ====================================================
        // FIND THE MACHINE ROW
        // ====================================================

        const machineRow =
            document.querySelector(
                '.machine-row[data-machine-id="' +
                machineId +
                '"]'
            );


        if(machineRow){

            const statusBadge =
                machineRow.querySelector(
                    ".machine-status-badge"
                );


            const currentStatus =
                machineRow.querySelector(
                    ".machine-current-status"
                );


            const availableTime =
                machineRow.querySelector(
                    ".machine-available-time"
                );


            const machineBooking =
                machineRow.querySelector(
                    ".machine-booking"
                );


            // ================================================
            // UPDATE STATUS TEXT
            // ================================================

            statusBadge.textContent =
                newStatus;


            currentStatus.textContent =
                newStatus;


            // ================================================
            // REMOVE OLD STATUS CLASS
            // ================================================

            statusBadge.classList.remove(
                "available",
                "in-use"
            );


            // ================================================
            // ADD NEW STATUS CLASS
            // ================================================

            if(selectedStatus === "available"){

                statusBadge.classList.add(
                    "available"
                );

                availableTime.textContent =
                    "Available Now";

                machineBooking.textContent =
                    "No Current Booking";

            }


            if(selectedStatus === "in_use"){

                statusBadge.classList.add(
                    "in-use"
                );

                // This is temporary frontend information.
                // The backend should later replace this
                // with the actual booking and availability
                // information from the database.

                availableTime.textContent =
                    "Currently In Use";

            }

        }


        // ====================================================
        // BACKEND
        // ====================================================
        // Save the selected machine availability status
        // against the machine ID in the database.
        //
        // Example:
        //
        // machine_id = 2
        // status = available
        //
        // The backend should also make the updated machine
        // availability available to the customer booking page.
        // ====================================================


        addRecentUpdate(
            machineId,
            newStatus
        );


        alert(
            "Machine " +
            machineId +
            " status updated to " +
            newStatus +
            "."
        );

    });

});


// ============================================================
// RECENT UPDATES
// ============================================================

function addRecentUpdate(
    machineId,
    newStatus
){

    const updatesContainer =
        document.getElementById(
            "machineUpdatesContainer"
        );


    const update =
        document.createElement("div");

    update.className =
        "machine-update";


    // ========================================================
    // UPDATE ICON
    // ========================================================

    const updateIcon =
        document.createElement("div");

    updateIcon.className =
        "update-icon";

    updateIcon.innerHTML =
        '<i class="fa-solid fa-rotate"></i>';


    // ========================================================
    // UPDATE CONTENT
    // ========================================================

    const updateContent =
        document.createElement("div");

    updateContent.className =
        "update-content";


    const updateMessage =
        document.createElement("p");


    updateMessage.innerHTML =
        'Machine <strong class="update-machine-reference">' +
        "Machine " +
        machineId +
        '</strong> was changed to <strong class="update-machine-status">' +
        newStatus +
        '</strong>.';


    const updateTime =
        document.createElement("span");

    updateTime.className =
        "update-time";

    updateTime.textContent =
        "Just now";


    // ========================================================
    // BUILD UPDATE
    // ========================================================

    updateContent.appendChild(
        updateMessage
    );

    updateContent.appendChild(
        updateTime
    );


    update.appendChild(
        updateIcon
    );

    update.appendChild(
        updateContent
    );


    // Add newest update to the top.
    updatesContainer.prepend(
        update
    );

}