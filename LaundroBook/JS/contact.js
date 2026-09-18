document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("contact-form");
    const sendBtn = document.getElementById("send-message-btn");
    const validationMessage = document.getElementById("validationMessage");

    // If enquiryController redirected back here with errors, contact.php
    // already rendered them into validationMessage server-side before
    // this script runs. This scrolls to them, same courtesy the
    // client-side error path gives below.
    if (validationMessage.classList.contains("error")) {
        validationMessage.scrollIntoView({ behavior: "smooth" });
    }

    
    // CLIENT-SIDE VALIDATION
    //
    // Mirrors enquiryController::validate_input() exactly, so a
    // customer gets instant feedback without a page reload. 
    
    form.addEventListener("submit", function (event) {
        const errors = [];

        const name = document.getElementById("full-name").value.trim();
        if (!name) {
            errors.push("Full name is required.");
        } else if (name.length < 2) {
            errors.push("Full name must be at least 2 characters.");
        }

        const email = document.getElementById("email-address").value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errors.push("Email address is required.");
        } else if (!emailRegex.test(email)) {
            errors.push("Please enter a valid email address.");
        }

        const subject = document.getElementById("message-subject").value.trim();
        if (!subject) {
            errors.push("Subject is required.");
        }

        const message = document.getElementById("customer-message").value.trim();
        if (!message) {
            errors.push("Message is required.");
        }

        if (errors.length > 0) {
            event.preventDefault(); // stop submission, show errors instead

            validationMessage.innerHTML =
                "<ul>" + errors.map(e => "<li>" + e + "</li>").join("") + "</ul>";
            validationMessage.classList.add("error");
            validationMessage.classList.remove("hidden");
            validationMessage.scrollIntoView({ behavior: "smooth" });
        }

    });

});