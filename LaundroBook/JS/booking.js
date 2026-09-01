document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("bookingForm");
    const bookingBtn = document.getElementById("booking_button");
    const confirmBtn = document.getElementById("confirmBookingBtn");
    const validationMessage = document.getElementById("validationMessage");
    const collectionMethod = document.getElementById("collection_method");
    const addressSection = document.getElementById("addressSection");
    const availabilitySection = document.getElementById("availabilitySection");
    const machineSelect = document.getElementById("machineSelect");
    const slotSelect = document.getElementById("slotSelect");

    // ADDED: if bookingController redirected back here with errors
    // (validation or booking failure), booking.php already rendered
    // them into validationMessage server-side before this script runs.
    // This just scrolls to them, same courtesy the client-side error
    // path already gives further down.
    if (validationMessage.classList.contains("error")) {
        validationMessage.scrollIntoView({ behavior: "smooth" });
    }

    //pricing data (static json, avoiding db calls)
    let pricingData = null; 

    // ADDED: duration only depends on wash_type, not load_type (Quick is
    // always 25 min/1 slot, Heavy is always 65 min/2 slots regardless of
    // whether it's clothes/towels/beddings), so this doesn't need to go
    // into prices.json, a small lookup here is enough.
    const DURATIONS = {
        quick:  { label: "25 min", durationSlots: 1 },
        normal: { label: "35 min", durationSlots: 1 },
        heavy:  { label: "65 min", durationSlots: 2 },
    };

    // ADDED: holds the last availability response so the machine->slot
    // filtering (further down) has something to filter against.
    let availableCombos = [];

    // ADDED: TEST MODE SWITCH, point this at the real AvailabilityController.php
    // once the backend is connected. Swap this one line only.
    const AVAILABILITY_ENDPOINT = "../tests/test.php";

    async function loadPricingData(){
        if(pricingData) return pricingData;

        try {
            const response = await fetch("../JS/prices.json"); 
            //probably unnecessary check since a string can be written to console
            if(!response.ok) throw new Error(`HTTP ${response.status}`); 
            pricingData = await response.json(); 
        } catch (error) {
            console.error("Failed to load pricing data: ", error); 
            pricingData = null; 
        }

        return pricingData; 
    }

    function getPrice(washType, loadType){
        if(!pricingData) return undefined; 
        const key = `${washType}_${loadType}`;
        return pricingData[key];  
    }

    // ADDED: fetches available machine/slot combos for the selected date
    // and duration, then builds the <option> elements for both selects.
    // This is the piece that was missing - without it, machineSelect and
    // slotSelect never get anything beyond their placeholder option, so
    // Confirm Bookings machine/slot check could never pass.
    async function loadAvailability(bookingDate, durationSlots) {
        try {
            const response = await fetch(AVAILABILITY_ENDPOINT, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    booking_date: bookingDate,
                    duration_slots: durationSlots
                })
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();

            if (data.error) {
                return { error: data.error };
            }

            return { available: data.available };
        } catch (error) {
            console.error("Failed to load availability: ", error);
            return { error: "Could not check availability. Please try again." };
        }
    }

    // ADDED: builds the machine/slot <option> elements from whatever
    // loadAvailability() returned.
    function populateAvailability(combos) {
        availableCombos = combos;

        machineSelect.innerHTML = '<option value="">Select an available machine</option>';
        slotSelect.innerHTML = '<option value="">Select an available time slot</option>';

        const seenMachines = new Set();
        combos.forEach(combo => {
            if (!seenMachines.has(combo.machine_id)) {
                seenMachines.add(combo.machine_id);
                const opt = document.createElement("option");
                opt.value = combo.machine_id;
                opt.textContent = combo.machine_name;
                machineSelect.appendChild(opt);
            }
        });
    }

    // Copies second_slot_id off the selected options data attribute into
    // a real form field,
    slotSelect.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById("secondSlotId").value = selectedOption.dataset.secondSlotId || "";
    });

    // ADDED: filters slotSelect down to whichever machine was picked.
    // second_slot_id (Heavy Wash only) is stashed on the option so
    // Confirm Booking can read it back without a second lookup.
    machineSelect.addEventListener("change", function () {
        slotSelect.innerHTML = '<option value="">Select an available time slot</option>';

        availableCombos
            .filter(combo => String(combo.machine_id) === this.value)
            .forEach(combo => {
                const opt = document.createElement("option");
                opt.value = combo.slot_id;
                opt.textContent = combo.slot_label;
                if (combo.second_slot_id) {
                    opt.dataset.secondSlotId = combo.second_slot_id;
                }
                slotSelect.appendChild(opt);
            });
    });



    // =========================================================
    // SHOW/HIDE DELIVERY ADDRESS
    // =========================================================
    collectionMethod.addEventListener("change", function () {
        if (this.value === "delivery") {
            addressSection.classList.remove("hidden");
        } else {
            addressSection.classList.add("hidden");
        }
    });

    // =========================================================
    // BOOK NOW - CLIENT-SIDE VALIDATION
    //
    // If validation passes, reveal the availability section.
    // Price and duration are left empty for PHP to populate
    // from the Service table using wash_type + load_type.
    // =========================================================
    bookingBtn.addEventListener("click", async function () {
        const errors = [];

        // --- CUSTOMER INFORMATION ---

        const name = document.getElementById("customer_name").value.trim();
        if (!name) {
            errors.push("Full name is required.");
        } else if (name.length < 2) {
            errors.push("Full name must be at least 2 characters.");
        }

        const email = document.getElementById("customer_email").value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errors.push("Email address is required.");
        } else if (!emailRegex.test(email)) {
            errors.push("Please enter a valid email address.");
        }

        // Phone - NUMBERS ONLY
        const phone = document.getElementById("customer_phone").value.trim();
        const phoneRegex = /^[0-9]+$/;
        if (!phone) {
            errors.push("Phone number is required.");
        } else if (!phoneRegex.test(phone)) {
            errors.push("Phone number must contain numbers only (no letters or special characters).");
        } else if (phone.length < 7 || phone.length > 15) {
            errors.push("Phone number must be between 7 and 15 digits.");
        }

        // --- BOOKING INFORMATION ---

        const bookingDate = document.getElementById("booking_date").value;
        if (!bookingDate) {
            errors.push("Booking date is required.");
        } else {
            const selected = new Date(bookingDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selected < today) {
                errors.push("Booking date cannot be in the past.");
            }
        }

        const washType = document.getElementById("wash_type").value;
        if (!washType) {
            errors.push("Please select a wash type.");
        }

        const loadType = document.getElementById("load_type").value;
        if (!loadType) {
            errors.push("Please select a load type.");
        }

        const collection = collectionMethod.value;
        if (!collection) {
            errors.push("Please select a collection method.");
        }

        if (collection === "delivery") {
            const deliveryAddress = document.getElementById("address").value.trim();
            if (!deliveryAddress) {
                errors.push("Delivery address is required for home delivery.");
            }
        }

        //load price data 
        await loadPricingData(); 
        let price; 
        if(washType && loadType){
            price = getPrice(washType, loadType); 
            if(price === undefined){
                errors.push("Price is not available for the selected wash and load type combination."); 
            }
        }

        // =========================================================
        // SHOW ERRORS OR REVEAL AVAILABILITY SECTION
        // =========================================================
        if (errors.length > 0) {
            validationMessage.innerHTML =
                "<ul>" + errors.map(e => "<li>" + e + "</li>").join("") + "</ul>";
            validationMessage.classList.add("error");
            validationMessage.classList.remove("hidden");
            validationMessage.scrollIntoView({ behavior: "smooth" });
            availabilitySection.classList.add("hidden");

        } else {
            validationMessage.innerHTML = "";
            validationMessage.classList.remove("error");
            validationMessage.classList.add("hidden");

            // Populate summary with user selections
            document.getElementById("selectedWashType").textContent =
                washType.charAt(0).toUpperCase() + washType.slice(1);
            document.getElementById("selectedLoadType").textContent =
                loadType.charAt(0).toUpperCase() + loadType.slice(1);
            document.getElementById("selectedBookingDate").textContent = bookingDate;
            document.getElementById("selectedCollectionMethod").textContent =
                collection === "pickup" ? "Self Pickup" : "Home Delivery";

            // contrary to the comment that was here, we're using JS 
            // to populate the booking summary, instead of using the db
            document.getElementById("servicePrice").textContent = `${price.toFixed(2)}`;
            // ADDED: was never being set before - element stayed stuck on "-"
            document.getElementById("serviceDuration").textContent = DURATIONS[washType].label;

            // ADDED: fetch available machines/slots for this date and
            // duration, then populate the two selects. durationSlots
            // comes from the same pricing JSON already loaded above -
            // this answers the "should an unconfirmed user be allowed
            // to query the DB" question from the bottom of this file:
            // yes, through a read-only endpoint like this one, which
            // never touches customer or writes anything.
            // FIXED: price is a plain number from prices.json, so
            // price.durationSlots was always undefined here before -
            // silently defaulting to 1 even for Heavy Wash. Now pulled
            // from DURATIONS by wash_type instead.
            const durationSlots = DURATIONS[washType].durationSlots;
            const availability = await loadAvailability(bookingDate, durationSlots);

            if (availability.error) {
                validationMessage.innerHTML = "<ul><li>" + availability.error + "</li></ul>";
                validationMessage.classList.add("error");
                validationMessage.classList.remove("hidden");
                availabilitySection.classList.add("hidden");
                return;
            }

            populateAvailability(availability.available);

            // Show availability section
            availabilitySection.classList.remove("hidden");
            availabilitySection.scrollIntoView({ behavior: "smooth" });
        }
    });
    // =========================================================
    // CONFIRM BOOKING
    // type="submit" - browser submits automatically by default.
    // This only validates machine/slot and blocks submission if
    // either is missing. No form.submit() needed.
    // =========================================================
    confirmBtn.addEventListener("click", function (event) {
        const machine = document.getElementById("machineSelect").value;
        const slot = document.getElementById("slotSelect").value;

        if (!machine || !slot) {
            event.preventDefault(); // prevents submission is selection is incomplete

            validationMessage.innerHTML =
                "<ul><li>Please select both a washing machine and a time slot.</li></ul>";
            validationMessage.classList.add("error");
            validationMessage.classList.remove("hidden");
            validationMessage.scrollIntoView({ behavior: "smooth" });
            return;
        }

        //if the form is valid -> browser submits form, does not rely on javascript for submission
    });

});