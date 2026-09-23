// ============================================================
// DELIVERY MANAGEMENT JAVASCRIPT
// ============================================================
// This file handles frontend behaviour for the admin
// Delivery Management page.
//
// IMPORTANT FOR BACKEND DEVELOPERS:
// The values used here are currently dummy/frontend values.
// The backend should later save and retrieve the real
// delivery information from the database.
// ============================================================


// ============================================================
// DELIVERY SEARCH
// ============================================================

const deliverySearchForm =
    document.getElementById("deliverySearchForm");

const deliverySearch =
    document.getElementById("deliverySearch");

deliverySearchForm.addEventListener("submit", function(event){

    event.preventDefault();

    const searchValue =
        deliverySearch.value.toLowerCase().trim();

    const deliveryRows =
        document.querySelectorAll(".delivery-row");

    deliveryRows.forEach(function(row){

        const bookingReference =
            row.querySelector(".delivery-booking-reference")
            .textContent
            .toLowerCase();

        const customerName =
            row.querySelector(".delivery-customer-name")
            .textContent
            .toLowerCase();

        if(
            bookingReference.includes(searchValue) ||
            customerName.includes(searchValue)
        ){
            row.style.display = "";
        }
        else{
            row.style.display = "none";
        }

    });

});


// ============================================================
// DELIVERY STATUS
// ============================================================

const saveDeliveryStatus =
    document.getElementById("saveDeliveryStatus");

saveDeliveryStatus.addEventListener("click", function(){

    const bookingReference =
        document.getElementById("deliveryBookingReference")
        .textContent;

    const bookingId =
        saveDeliveryStatus.getAttribute("data-booking-id");

    const statusSelect =
        document.getElementById("currentDeliveryStatus");

    const estimatedDeliveryTime =
        document.getElementById("estimatedDeliveryTime").value;

    const assignedDriver =
        document.getElementById("assignedDriver");

    const selectedStatus =
        statusSelect.value;

    const selectedDriver =
        assignedDriver.value;


    // Check that a status has been selected.
    if(selectedStatus === ""){

        alert("Please select a delivery status.");

        return;
    }


    // ========================================================
    // BACKEND:
    // Save the selected delivery status, estimated delivery
    // time and assigned driver using the booking/delivery ID.
    //
    // Example database values:
    //
    // booking_id = 412
    // status = out_for_delivery
    // estimated_delivery_time = selected time
    // driver_id = selected driver
    //
    // The backend will replace this frontend-only behaviour
    // with the actual database update.
    // ========================================================


    const statusText = {

        pickup_of_order: "Pickup of Order",

        order_confirmed: "Order Confirmed",

        laundry_completed: "Laundry Completed",

        ready_for_delivery: "Ready for Delivery",

        driver_assigned: "Driver Assigned",

        out_for_delivery: "Out for Delivery",

        driver_close_by: "Driver Close By",

        delivery_complete: "Delivery Complete"

    };


    const newStatus =
        statusText[selectedStatus];


    // Find the matching delivery row.
    const deliveryRow =
        document.querySelector(
            '.delivery-row[data-booking-id="' +
            bookingId +
            '"]'
        );


    if(deliveryRow){

        const statusBadge =
            deliveryRow.querySelector(
                ".delivery-status-badge"
            );

        const currentStatus =
            deliveryRow.querySelector(
                ".delivery-current-status"
            );

        const estimatedTime =
            deliveryRow.querySelector(
                ".delivery-estimated-time"
            );

        const driver =
            deliveryRow.querySelector(
                ".delivery-driver"
            );


        // Update the status text.
        currentStatus.textContent =
            newStatus;


        // Update the status badge.
        statusBadge.textContent =
            newStatus;


        // Remove old status classes.
        statusBadge.classList.remove(
            "pickup-of-order",
            "order-confirmed",
            "laundry-completed",
            "ready-for-delivery",
            "driver-assigned",
            "out-for-delivery",
            "driver-close-by",
            "delivery-complete"
        );


        // Add the new status class.
        const statusClass = {

            pickup_of_order:
                "pickup-of-order",

            order_confirmed:
                "order-confirmed",

            laundry_completed:
                "laundry-completed",

            ready_for_delivery:
                "ready-for-delivery",

            driver_assigned:
                "driver-assigned",

            out_for_delivery:
                "out-for-delivery",

            driver_close_by:
                "driver-close-by",

            delivery_complete:
                "delivery-complete"

        };


        statusBadge.classList.add(
            statusClass[selectedStatus]
        );


        // Update estimated delivery time.
        if(estimatedDeliveryTime !== ""){

            estimatedTime.textContent =
                estimatedDeliveryTime;

        }


        // Update assigned driver.
        if(selectedDriver !== ""){

            driver.textContent =
                assignedDriver.options[
                    assignedDriver.selectedIndex
                ].text;

        }

    }


    // Add the change to Recent Updates.
    addRecentUpdate(
        bookingReference,
        newStatus
    );


    alert(
        "Delivery " +
        bookingReference +
        " was updated to " +
        newStatus +
        "."
    );

});


// ============================================================
// DELIVERY PROGRESS
// ============================================================

const saveDeliveryProgress =
    document.getElementById("saveDeliveryProgress");

saveDeliveryProgress.addEventListener("click", function(){

    const bookingReference =
        document.getElementById("progressBookingReference")
        .textContent;

    const bookingId =
        saveDeliveryProgress.getAttribute("data-booking-id");

    const progressSelect =
        document.getElementById("currentDeliveryProgress");

    const selectedProgress =
        progressSelect.value;


    if(selectedProgress === ""){

        alert("Please select a delivery progress stage.");

        return;
    }


    const progressText = {

        pickup_of_order: "Pickup of Order",

        order_confirmed: "Order Confirmed",

        laundry_completed: "Laundry Completed",

        ready_for_delivery: "Ready for Delivery",

        driver_assigned: "Driver Assigned",

        out_for_delivery: "Out for Delivery",

        driver_close_by: "Driver Close By",

        delivery_complete: "Delivery Complete"

    };


    const newProgress =
        progressText[selectedProgress];


    // ========================================================
    // BACKEND:
    // Save the selected delivery progress against the
    // booking/delivery ID.
    //
    // The customer tracking page will later retrieve this
    // status from the backend and use it to update the
    // customer's delivery progress tracker.
    // ========================================================


    alert(
        "Delivery " +
        bookingReference +
        " progress updated to " +
        newProgress +
        "."
    );


    // Add the change to Recent Updates.
    addRecentUpdate(
        bookingReference,
        newProgress
    );

});


// ============================================================
// RECENT UPDATES
// ============================================================

function addRecentUpdate(
    bookingReference,
    newStatus
){

    const updatesContainer =
        document.getElementById(
            "deliveryUpdatesContainer"
        );


    const update =
        document.createElement("div");

    update.className =
        "delivery-update";


    const updateIcon =
        document.createElement("div");

    updateIcon.className =
        "update-icon";

    updateIcon.innerHTML =
        '<i class="fa-solid fa-rotate"></i>';


    const updateContent =
        document.createElement("div");

    updateContent.className =
        "update-content";


    const updateMessage =
        document.createElement("p");

    updateMessage.innerHTML =
        'Delivery <strong class="update-delivery-reference">' +
        bookingReference +
        '</strong> was updated to <strong class="update-delivery-status">' +
        newStatus +
        '</strong>.';


    const updateTime =
        document.createElement("span");

    updateTime.className =
        "update-time";

    updateTime.textContent =
        "Just now";


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


    updatesContainer.prepend(
        update
    );
