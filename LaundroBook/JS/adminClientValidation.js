document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");

    // The server only renders .login-error after a failed login, so on a
    // fresh page load it is not in the page at all. (This used to be looked up once at
    // page load and was null on a fresh page, so a short password blocked
    // the submit and then threw a TypeError - nothing visible happened.)
    function getErrorContainer() {
        let container = document.querySelector(".login-error");

        if (!container) {
            container = document.createElement("div");
            container.className = "login-error";
            form.insertBefore(container, form.querySelector(".login-btn"));
        }

        container.classList.add("visible");
        return container;
    }

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

            getErrorContainer().innerHTML = errors
                .map(function (err) {
                    return `<p>${err}</p>`;
                })
                .join("");
        } else {
            // Clear old errors if validation passes
            const existing = document.querySelector(".login-error");
            if (existing) {
                existing.remove();
            }
        }
    });

});