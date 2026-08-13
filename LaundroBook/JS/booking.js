
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

//full name validation
function validateCustomerName(){
    const value = customerName.value.trim();


    if(value === ""){
        return "Please enter your full name."; 
    }
    if(value.length < 2){
        return "Full name must be at least 2 characters.";
    }
    if(value.length > 60){
        return "Full name must be under 60 characters."
    }
    if(!/^[A-Za-z\s'-]+$/.test(value)){
        return "Full name can only contain letters, spaces, hyphens, and apostrophes."
    }
    return "";
}

//customer email validation
 function validateCustomerEmail(){
    const value = customerEmail.value.trim(); 

    if(value === ""){
        return "Please enter your email address."; 
    }

    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)){
        return "Please enter a valid email address.";
    }

    return "";
 }

 function validateCustomerPhone(){
    const value = customerPhone.value.trim(); 

    if(value === ""){
        return "Please enter your phone number.";
    }

    // 10 number regex
    if(!/^[0-9]{10}$/.test(value)){
        return "Please enter a valid phone number (10 digits long)."; 
    }
    return ""; 
 }

 function validateBookingDate(){
    const value = bookingDate.value; 

    if(value === ""){
        return "Please select a booking date.";
    }
    const selectedDate = new Date(value); 
    const today = new Date(); 
    today.setHours(0, 0, 0, 0);

    if(selectedDate < today){
        return "Booking date cannot be in the past."; 
    }
    return ""; 
 }

 function validateBookingTime(){
    if(bookingTime.value === ""){
        return "Please select a time slot."; 
    }
    return "";
 }

 //this will change since I believe that the system
 //decides the machine for the client
 function validateMachineNumber(){

    if(machineNumber.value === ""){
        return "Please select a washing machine"; 
    }
    return ""; 
 }

 function validateServiceType(){

    if(serviceType.value === ""){
        return "Please select a laundry service.";
    }
    return ""; 
 }

 function validateCollectionMethod(){

    if(collectionMethod.value === ""){
        return "Please select a collection method";
    }
    return "";
 }

 function validateDeliveryAddress(){

    if(collectionMethod.value === "delivery" && 
        deliveryAddress.value.trim() === ""
    ){
        return "Please enter a delivery address."; 
    }
    return ""; 
 }

 //responsible for running all the other modules
 //for validation when the user clicks book

function validateBooking(){
    //using the technique of storing errors in array
    const errors = [];

    const nameError = validateCustomerName(); 
    if(nameError !== "") errors.push(nameError); 

    const emailError = validateCustomerEmail(); 
    if(emailError !== "") errors.push(emailError); 
    
    const phoneError = validateCustomerPhone(); 
    if(phoneError !== "") errors.push(phoneError); 
    
    const dateError = validateBookingDate(); 
    if(dateError !== "") errors.push(dateError); 
    
    const timeError = validateBookingTime(); 
    if(timeError !== "") errors.push(timeError); 
    
    const machineError = validateMachineNumber();
    if(machineError !== "") errors.push(machineError);

    const serviceError = validateServiceType();
    if(serviceError !== "") errors.push(serviceError);

    const collectionError = validateCollectionMethod();
    if(collectionError !== "") errors.push(collectionError);

    const addressError = validateDeliveryAddress();
    if(addressError !== "") errors.push(addressError);

    if(errors.length > 0){
        showValidationMessage(errors); 
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

