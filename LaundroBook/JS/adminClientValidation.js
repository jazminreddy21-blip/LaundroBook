document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const errorContainer = document.querySelector(".login-error");

    form.addEventListener("submit", function (e) {

        const errors = [];

        const usernameField = document.getElementById("username");
        const passwordField = document.getElementById("password");

        const username = usernameField.value.trim();
        const password = passwordField.value.trim();

        if (!username) {
            errors.push("Username is required.");
        } else if (username.length < 2) {
            errors.push("Username must be at least 2 characters.");
        }

        if (!password) {
            errors.push("Password is required.");
        } else if (password.length < 4) {
            errors.push("Password must be at least 4 characters.");
        }

        // If there are errors, stop the form from submitting and show them
        if (errors.length > 0) {
            e.preventDefault();

            errorContainer.innerHTML = errors
                .map(function (err) {
                    return `<p>${err}</p>`;
                })
                .join("");
        } else {
            // Clear old errors if validation passes
            errorContainer.innerHTML = "";
        }
    });

});