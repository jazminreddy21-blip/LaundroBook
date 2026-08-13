
/* ==========================================================
                    LAUNDROBOOK BOOKING

Purpose:
Handles all client-side functionality for the
booking page.

Functions:
1. Show/Hide delivery address
2. Validate customer information
3. Validate booking information
4. Populate booking summary
5. Display booking summary
6. Submit booking to PHP (Future Backend)

========================================================== */


/* ==========================================================
                    FORM ELEMENTS
========================================================== */

const bookingForm = document.getElementById("bookingForm");

const validationMessage = document.getElementById("validationMessage");

const bookingSummary = document.getElementById("bookingSummary");

const bookingButton = document.getElementById("booking_button");

const confirmBookingButton = document.getElementById("confirmBookingBtn");


/* ==========================================================
                CUSTOMER INFORMATION
========================================================== */

const customerName = document.getElementById("customer_name");

const customerEmail = document.getElementById("customer_email");

const customerPhone = document.getElementById("customer_phone");

/* ==========================================================
                    BOOKING DETAILS
========================================================== */


const bookingDate = document.getElementById("booking_date");

const bookingTime = document.getElementById("booking_time");

const machineNumber = document.getElementById("machine_number");

const serviceType = document.getElementById("service_type");

const collectionMethod = document.getElementById("collection_method");

const addressSection = document.getElementById("addressSection");

const deliveryAddress = document.getElementById("address");

const specialInstructions = document.getElementById("special_instructions");

/* ==========================================================
                    BOOKING SUMMARY
========================================================== */



const summaryDate =
document.getElementById("summary-date");

const summaryTime =
document.getElementById("summary-time");

const summaryMachine =
document.getElementById("summary-machine");

const summaryService =
document.getElementById("summary-service");

const summaryCollection =
document.getElementById("summary-collection");

const summaryInstructions =
document.getElementById("summary-instructions");

const summaryCost =
document.getElementById("summary-cost");

/* ==========================================================
            SHOW / HIDE DELIVERY ADDRESS
========================================================== */

collectionMethod.addEventListener("change", function(){

    if(this.value === "delivery")
    {
        addressSection.classList.remove("hidden");
        deliveryAddress.setAttribute("required", true);
    }
    else
    {
        addressSection.classList.add("hidden");
        deliveryAddress.removeAttribute("required");
        deliveryAddress.value = "";
    }

});

/* ==========================================
        BOOKING FORM SUBMISSION
========================================== */

bookingForm.addEventListener("submit", function(event){
	
console.log("Submit event fired");
    // Stop the form from submitting
    event.preventDefault();

    // Clear previous validation message
    validationMessage.textContent = "";

    // Call validation function
    if(validateBooking())
    {
        populateBookingSummary();

        bookingSummary.classList.remove("hidden");

        bookingButton.classList.add("hidden");

        confirmBookingButton.classList.remove("hidden");
    }

});
/* ==========================================
            VALIDATE BOOKING
========================================== */

function validateBooking(){

    if(customerName.value.trim() === "")
    {
        validationMessage.textContent =
        "Please enter your full name.";

        return false;
    }

    if(customerEmail.value.trim() === "")
    {
        validationMessage.textContent =
        "Please enter your email address.";

        return false;
    }

    if(customerPhone.value.trim() === "")
    {
        validationMessage.textContent =
        "Please enter your phone number.";

        return false;
    }

    if(bookingDate.value === "")
    {
        validationMessage.textContent =
        "Please select a booking date.";

        return false;
    }

    if(bookingTime.value === "")
    {
        validationMessage.textContent =
        "Please select a time slot.";

        return false;
    }

    if(machineNumber.value === "")
    {
        validationMessage.textContent =
        "Please select a washing machine.";

        return false;
    }

    if(serviceType.value === "")
    {
        validationMessage.textContent =
        "Please select a laundry service.";

        return false;
    }

    if(collectionMethod.value === "")
    {
        validationMessage.textContent =
        "Please select a collection method.";

        return false;
    }

    if(collectionMethod.value === "delivery" &&
       deliveryAddress.value.trim() === "")
    {
        validationMessage.textContent =
        "Please enter a delivery address.";

        return false;
    }

    return true;

}

/* ==========================================
        POPULATE BOOKING SUMMARY
========================================== */

function populateBookingSummary(){

    

    summaryDate.textContent =
    bookingDate.value;

    summaryTime.textContent =
    bookingTime.options[bookingTime.selectedIndex].text;

    summaryMachine.textContent =
    machineNumber.options[machineNumber.selectedIndex].text;

    summaryService.textContent =
    serviceType.options[serviceType.selectedIndex].text;

    summaryCollection.textContent =
    collectionMethod.options[collectionMethod.selectedIndex].text;

    if(specialInstructions.value.trim() === "")
    {
        summaryInstructions.textContent =
        "None";
    }
    else
    {
        summaryInstructions.textContent =
        specialInstructions.value;
    }

}

