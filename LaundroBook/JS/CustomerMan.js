// ============================================================
// CUSTOMER MANAGEMENT JAVASCRIPT
// ============================================================
// This file handles frontend behaviour for the admin
// Customer Management page.
//
// IMPORTANT FOR BACKEND DEVELOPERS:
// The customer information currently displayed on the page
// is temporary/dummy data.
//
// The backend should later retrieve the real customer
// information from the database and populate the page.
// ============================================================


// ============================================================
// CUSTOMER SEARCH
// ============================================================

const customerSearchForm =
    document.getElementById("customerSearchForm");

const customerSearch =
    document.getElementById("customerSearch");


customerSearchForm.addEventListener("submit", function(event){

    event.preventDefault();

    const searchValue =
        customerSearch.value.toLowerCase().trim();

    const customerRows =
        document.querySelectorAll(".customer-row");


    customerRows.forEach(function(row){

        const customerName =
            row.querySelector(".customer-name")
            .textContent
            .toLowerCase();

        const customerEmail =
            row.querySelector(".customer-email")
            .textContent
            .toLowerCase();

        const customerPhone =
            row.querySelector(".customer-phone")
            .textContent
            .toLowerCase();


        // Show the customer if the search matches
        // their name, email or phone number.

        if(
            customerName.includes(searchValue) ||
            customerEmail.includes(searchValue) ||
            customerPhone.includes(searchValue)
        ){

            row.style.display = "";

        }
        else{

            row.style.display = "none";

        }

    });

});


// ============================================================
// CLEAR SEARCH WHEN SEARCH BOX IS EMPTY
// ============================================================

customerSearch.addEventListener("input", function(){

    const searchValue =
        customerSearch.value.toLowerCase().trim();

    if(searchValue === ""){

        const customerRows =
            document.querySelectorAll(".customer-row");

        customerRows.forEach(function(row){

            row.style.display = "";

        });

    }

});


// ============================================================
// BACKEND INFORMATION
// ============================================================
// The backend developers can later replace the temporary
// customer information in the HTML with information
// retrieved from the database.
//
// Information that can be populated from the database:
//
// - Customer ID
// - Customer name
// - Customer email
// - Customer phone
// - Total bookings
// - Latest booking reference
// - Latest booking status
//
// The following IDs/classes are available for backend use:
//
// #totalCustomers
// #customerTotalBookings
// #customerTableBody
// .customer-row
// .customer-name
// .customer-email
// .customer-phone
// .customer-booking-count
// .customer-latest-booking
// .customer-status-badge
// .customer-current-status
//
// ============================================================


// ============================================================
// EXAMPLE BACKEND FLOW
// ============================================================
//
// Database
//     ↓
// Backend/PHP
//     ↓
// Customer information
//     ↓
// Customer Management page
//
// The customer information displayed here should eventually
// come from the database instead of the temporary HTML data.
// ======================================================