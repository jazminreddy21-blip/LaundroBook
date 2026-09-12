<?php

/**
 * EmailConfig
 *
 * SMTP credentials for sending real confirmation emails, used by
 * EmailService. Kept in its own file, separate from application logic,
 * so credentials never end up scattered through the codebase - and so
 * this specific file can be excluded from version control if it ever
 * holds real secrets (add it to .gitignore before putting a real
 * password in here).
 *
 * These are PLACEHOLDER values. Replace them with real credentials
 * before the email feature will actually send anything. A Gmail
 * account with an "App Password" (not your normal login password)
 * Google requires this for any app using plain SMTP login instead of full OAuth.
 */

return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'laundrobook@gmail.com',
    'smtp_password' => '16-digit-password',
    'smtp_secure' => 'tls',
    'from_email' => 'laundrobook@gmail.com',
    'from_name' => 'LaundroBook',
];