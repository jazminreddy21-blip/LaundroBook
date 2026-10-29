# LaundroBook

A web-based laundry booking and management system. Customers book a washing machine for a time slot, choose a wash type and load type, and pick between self drop-off or home pickup and delivery. Staff manage everything through an admin portal.

Built with plain **PHP**, **MySQL/MariaDB**, **JavaScript**, **HTML** and **CSS**, designed to run locally on **XAMPP**.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Configuration](#configuration)
- [Using the System](#using-the-system)
- [Pricing and Services](#pricing-and-services)
- [Delivery Time Estimates](#delivery-time-estimates)
- [Database](#database)
- [Architecture](#architecture)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Known Limitations](#known-limitations)

---

## Features

### Customer side
- **Online booking**: pick a date, wash type, load type, machine and time slot.
- **Live availability**: the booking form fetches the free machine/slot combinations for the chosen date. Slots starting within 30 minutes (or already started) can't be booked.
- **Collection options**: *Self Drop off and Pickup*, or *Home Pickup and Delivery* (adds a flat delivery fee).
- **Booking confirmation**: confirmation page plus a confirmation email with a booking reference (format `LB-00001`).
- **Order tracking**: look up an order by reference on three pages: wash status, pickup tracking and delivery tracking.
- **Estimated times**: estimated pickup and delivery window (or "ready for collection from" for self drop-off).
- **Contact form**: enquiries are stored and shown to admins.
- **Payment**: no online payment. Customers pay on delivery (home service) or in store (self drop-off). *A payment gateway could be included in the future for online payments.*

### Admin side
- Secure login (passwords hashed with `password_hash` - one way hashing algorithm) and logout.
- **Dashboard** with today's bookings, pending bookings, pickups, deliveries, available machines, today's revenue and system notifications.
- **Management pages** for bookings, machines, pickups, deliveries, customers and enquiries, plus a **reports** page.
- **Super admin** (the manager with the lowest ID) can add and remove other admins and add and remove groundworkers (drivers and collectors).
- **Automatic groundworker assignment**: a pickup or delivery goes to the groundworker with the fewest open jobs (lowest ID wins ties). Admins can also reassign manually.
- **Automatic machine release**: when a booked slot has finished, the booking is marked `completed` and the machine returns to `available`. This runs on every availability check, with no cron job needed.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x (developed on 8.2) |
| Database | MySQL / MariaDB (developed on MariaDB 10.4) via `mysqli` |
| Frontend | HTML, CSS, vanilla JavaScript, Font Awesome, Google Fonts (Poppins) |
| Email | [PHPMailer](https://github.com/PHPMailer/PHPMailer) over Gmail SMTP (bundled in `Libraries/`) |
| Local server | XAMPP (Apache + MySQL) |

---

## Project Structure

```
LaundroBook/
├── Config/
│   ├── EmailConfig.example.txt   # Template for SMTP settings (see Configuration)
│   └── deliveryTimes.php         # Delivery/pickup estimate settings
├── Controllers/
│   ├── bookingController.php     # Validates and processes a booking
│   ├── availabilityController.php# JSON endpoint for free machine/slot combos
│   ├── TrackingController.php    # Order, pickup and delivery tracking
│   ├── EnquiryController.php     # Contact form handling
│   ├── AdminController.php       # All admin actions
│   └── process-booking.php       # Test-only page for server-side validation
├── Database/
│   ├── Connection.php            # Singleton mysqli connection
│   └── schema.sql                # OLD schema, do not use (see Database)
├── Seeder/
│   ├── databaseQuery.sql         # Creates the database and all tables
│   ├── seed.sql                  # Starting data (admin, machines, slots, services, groundworkers)
│   └── rename_quick_to_priority.sql  # One-off migration for older databases
├── Models/                       # Plain data classes (Booking, Customer, Service, ...)
├── Repositories/                 # Database access, one class per table
├── Interfaces/                   # Repository and email service interfaces
├── Services/
│   ├── BookingService.php        # Booking logic, pricing and transaction
│   ├── AvailabilityService.php   # Machine/slot availability and auto-release
│   ├── DeliveryEstimator.php     # Estimated ready/pickup/delivery times
│   ├── DashboardService.php      # Admin dashboard figures
│   └── EmailService.php          # Confirmation emails via PHPMailer
├── Public/                       # Admin entry points (login, logout, actions)
├── Views/
│   ├── index.html, about.html    # Landing and about pages
│   ├── booking.php, BookingConfirm.php, contact.php
│   ├── TrackingOrder.php, TrackingPick.php, TrackingDel.php
│   └── Admin/                    # Admin portal pages
├── JS/                           # booking.js, contact.js, adminClientValidation.js, prices.json
├── CSS/                          # style.css, secondStyle.css
├── Images/
├── Libraries/PHPMailer/
├── READMES/                      # Extra notes (server-side validation testing)
└── tests/                        # Mock/stub scripts for testing without a database
```

---

## Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL (PHP 8.x)
- A Gmail account with an [App Password](https://support.google.com/accounts/answer/185833) if you want confirmation emails

### 1. Place the project
Copy the `LaundroBook` folder into XAMPP's web root:

```
C:\xampp\htdocs\LaundroBook
```

Folder and file names are case-sensitive on some systems, so keep the capitalisation as is.

### 2. Start the services
Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Create the database
Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin), go to the **SQL** tab on the main dashboard (not from inside an existing database), and run these in order:

1. `Seeder/databaseQuery.sql` creates the `laundrobook` database and all tables.
2. `Seeder/seed.sql` adds the admin account, 3 machines, 8 time slots, 9 services and 3 groundworkers.

> Run `seed.sql` **only once**, or you'll get duplicate rows. To start over, drop the `laundrobook` database and repeat both steps.

### 4. Configure email
See [Configuration](#configuration). This is required: the admin login also loads the email config, so it fails without it.

### 5. Open the app

| Page | URL |
|---|---|
| Customer site | `http://localhost/LaundroBook/Views/index.html` |
| Admin login | `http://localhost/LaundroBook/Views/Admin/login.php` |

**Default admin login** (from `seed.sql`):

| Username | Password |
|---|---|
| `admin` | `Test@123` |

Change this password before using the system for anything beyond local development.

---

## Configuration

### Database connection
Edit `Database/Connection.php` if your MySQL setup differs from the XAMPP defaults:

```php
new mysqli('localhost', 'root', '', 'laundrobook');
```

### Email (SMTP)
1. Copy `Config/EmailConfig.example.txt` to `Config/EmailConfig.php`. It must be a `.php` file.
2. Fill in your real values:

```php
return [
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,
    'smtp_username' => 'your-address@gmail.com',
    'smtp_password' => 'your-16-digit-app-password',
    'smtp_secure'   => 'tls',
    'from_email'    => 'your-address@gmail.com',
    'from_name'     => 'LaundroBook',
];
```

`Config/EmailConfig.php` is listed in `.gitignore`. Never commit real credentials.

If an email fails to send, the booking still succeeds. Email errors are caught and logged, and never roll back a booking.

### Prices and delivery fee
Prices for the booking form and the home delivery fee live in `JS/prices.json`. The browser reads it to display prices, and `BookingService` reads the same file for the delivery fee, so there is one source of truth for that number. The prices in the **database** (`service` table) are what the server charges for the wash itself, so keep the two in sync when changing prices.

### Delivery time estimates
All estimate settings are in `Config/deliveryTimes.php`. See [Delivery Time Estimates](#delivery-time-estimates).

### Booking buffer
`AvailabilityService::BOOKING_BUFFER_MINUTES` (default 30) controls how close to a slot's start time a customer can still book it.

---

## Using the System

### Making a booking
1. Open **Book a wash/Machine**.
2. Enter name, email, and a 10-digit phone number.
3. Choose a date, wash type and load type.
4. Choose a collection method. Home delivery requires an address.
5. Pick one of the available machine/time-slot combinations.
6. Confirm. You'll see a confirmation page and receive an email containing your booking reference.

### Tracking an order
Open **Track Order** and enter your booking reference (e.g. `LB-00001`). Tracking needs no account. Self drop-off customers use the order tracker, and home delivery customers can also use the pickup and delivery trackers.

### Admin portal
Log in and use the navigation bar to manage bookings, machines, pickups, deliveries, customers, enquiries and reports. Only the super admin sees the **Add Admin** and **Groundworkers** pages. Other admins are redirected to the dashboard, and anyone not logged in is sent to the login page.

Admin form actions all post to `Public/adminAction.php`, which dispatches on an `action` field. Each handler re-checks authentication itself.

---

## Pricing and Services

Three wash types and three load types give nine services (prices in rand, from `seed.sql`):

| Wash type | Clothes | Beddings | Towels |
|---|---|---|---|
| **Priority** | R50 | R65 | R55 |
| **Normal** | R40 | R50 | R45 |
| **Heavy** | R55 | R70 | R65 |

- Home Pickup and Delivery adds a **R60** flat fee.
- Normal and Priority washes use **1 slot**. Heavy washes use **2 consecutive slots**.
- Time slots are 45 minutes each, from 08:00 to 14:00.

> "Priority Wash" was formerly called "Quick Wash". If you have an older database, run `Seeder/rename_quick_to_priority.sql` once. It renames rows in place, so old bookings keep working, and it is safe to run twice.

---

## Delivery Time Estimates

Estimates are **fixed placeholder values, not live scheduling**. They are produced by `Services/DeliveryEstimator.php` using the settings in `Config/deliveryTimes.php`.

| Wash type | Clothes | Beddings | Towels |
|---|---|---|---|
| Priority | Same day | 1 day | Same day |
| Normal | 1 day | 2 days | 1 day |
| Heavy | 1 day | 2 days | 2 days |

- **Same-day jobs** are ready after the wash plus drying and folding time, with a one-hour delivery window after a travel buffer.
- **Jobs of a day or more** are ready at 09:00 and delivered between 10:00 and 11:00 on that day.
- **Pickup** happens one hour before the first slot.
- A **Heavy** wash is calculated from the end of its second slot.

Estimates appear on the confirmation page, in the confirmation email, and on all three tracking pages.

---

## Database

Database name: `laundrobook`. The schema is created by `Seeder/databaseQuery.sql`.

| Table | Purpose |
|---|---|
| `system_manager` | Admin accounts (username and password hash) |
| `customer` | Customers (name, unique email, phone, address) |
| `service` | Wash type × load type combinations with price and duration |
| `machine` | Washing machines and their status |
| `slot` | Bookable time slots |
| `booking` | Bookings linking customer, machine, slot, service and manager |
| `delivery` | Pickup and delivery legs for home-service bookings |
| `groundworker` | Drivers and collectors |
| `enquiry` | Contact form messages |

Key rules enforced in the database:
- **No double booking**: `booking.active_combo_key` is a generated column built from machine, slot and date for non-cancelled bookings, with a unique index on it. A cancelled booking frees the combination.
- `booking.status`: `pending`, `in_progress`, `completed`, `cancelled`
- `machine.machine_status`: `available`, `in_use`, `under_maintenance`
- `delivery.delivery_type`: `collection`, `delivery`; `delivery_status`: `pending`, `in_progress`, `completed`
- `service.wash_type`: `priority`, `normal`, `heavy`; `load_type`: `clothes`, `beddings`, `towels`
- Prices must be greater than 0.

> **Note:** `Database/schema.sql` is an older phpMyAdmin export. It still uses `quick` instead of `priority`, doesn't create the database, and contains a syntax error (a stray comma in the `customer` indexes). **Use the files in `Seeder/` instead.**

---

## Architecture

The backend uses a layered structure with dependency injection through interfaces, which makes components swappable and testable with stubs.

```
View (PHP/HTML + JS)
   ↓
Controller        validates input, manages session/redirects
   ↓
Service           business logic (pricing, availability, estimates, email)
   ↓
Repository        SQL access (prepared statements via mysqli)
   ↓
Database
```

Notable design points:
- **Booking is transactional.** `BookingService::createBooking()` re-checks availability and writes the customer, booking and delivery records in one transaction. The email is sent only after commit.
- **Server-side validation mirrors client-side validation.** The browser validates for convenience, and the server always revalidates (name, email, 10-digit phone, date, wash/load/collection values, address for delivery, machine and slot).
- **Public vs. protected endpoints.** The availability and tracking endpoints are read-only and need no login. All admin handlers check the session, and super-admin-only handlers check again.
- **MySQL errors throw exceptions** (`mysqli_report` strict mode), so failures are handled with `try/catch` rather than silent `false` returns.

---

## Testing

There is no automated test framework. Manual and stub-based tests live in `tests/` and `READMES/`:

| File | Purpose |
|---|---|
| `tests/test.php` | Hardcoded stand-in for the availability endpoint, for developing `booking.js` without a database |
| `tests/Mock confirm booking.php` | Runs the full `confirmBooking()` flow using stub repositories, with no real database. Point the booking form's `action` at it temporarily |
| `tests/test-email.php` | Isolated SMTP diagnostic (git-ignored, not part of the real system) |
| `Controllers/process-booking.php` | Displays server-side validation messages |
| `READMES/serverside_validation_testing.txt` | Step-by-step guide to testing server-side validation with JavaScript disabled |

To test server-side validation: disable JavaScript in your browser's dev tools, point the booking form at `process-booking.php`, and submit invalid values (empty name, bad email, 12-digit phone, past date, delivery without an address, and so on). Switch the form action back afterwards.

---

## Troubleshooting

| Problem | Likely cause and fix |
|---|---|
| Admin login or booking crashes immediately | `Config/EmailConfig.php` is missing. Copy it from the example file (see Configuration) |
| "Could not connect" or mysqli errors | MySQL isn't running in XAMPP, the `laundrobook` database doesn't exist, or credentials in `Connection.php` are wrong |
| No machines or time slots appear | `seed.sql` wasn't run |
| Delivery booking fails with "no groundworker available" | The `groundworker` table is empty. Add one via the super admin page or re-seed |
| "Home delivery is unavailable right now" | `home_delivery_fee` is missing or invalid in `JS/prices.json` |
| Slots show as `0000-00-00` or lose their times | Slot rows were inserted with bare times. Use the full date-times from `seed.sql` |
| Confirmation email doesn't arrive | Wrong SMTP details, or you used your normal Gmail password instead of an App Password. Check the PHP/Apache error log |
| Old styles or scripts still showing | Hard refresh (Ctrl + F5) |
| Wash type error after pulling updates | Old database still uses `quick`. Run `Seeder/rename_quick_to_priority.sql` |

---

## Known Limitations

- Delivery and pickup times are static estimates, not live scheduling.
- No online payment, since payment is collected in person.
- Automatic groundworker assignment doesn't yet take the groundworker's role (driver vs. collector) into account.
- Designed for local XAMPP use. Review credentials, error display and HTTPS before any real deployment.
