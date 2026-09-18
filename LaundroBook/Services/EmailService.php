<?php

require_once __DIR__ . '/../Interfaces/EmailServiceInterface.php';
require_once __DIR__ . '/../Libraries/PHPMailer/Exception.php';
require_once __DIR__ . '/../Libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../Libraries/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * EmailService
 *
 * Sends the booking confirmation email via PHPMailer/SMTP, matching
 * the mechanism named in the Reliability Requirements (Analysis Phase
 * Section 3.7: "implemented via PHPMailer or SMTP integration").
 *
 * This is called AFTER BookingService::createBooking() has already
 * committed the transaction - a booking is considered successful the
 * moment it exists in the database, regardless of whether this email
 * later fails to send. Every failure path here is caught internally
 * and logged; nothing is ever allowed to throw back up into
 * BookingService and disrupt a booking that has already succeeded.
 */
class EmailService implements EmailServiceInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendBookingConfirmation(
        string $customerEmail,
        string $bookingReference,
        array $service,
        string $bookingDate,
        string $machineName,
        string $slotLabel,
        ?string $secondSlotLabel
    ): bool {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port = $this->config['smtp_port'];

            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($customerEmail);

            $mail->isHTML(true);
            $mail->Subject = 'LaundroBook Confirmation - ' . $bookingReference;
            $mail->Body = $this->buildEmailBody(
                $bookingReference, $service, $bookingDate, $machineName, $slotLabel, $secondSlotLabel
            );
            $mail->AltBody = $this->buildPlainTextBody(
                $bookingReference, $service, $bookingDate, $machineName, $slotLabel, $secondSlotLabel
            );

            $mail->send();
            $this->logResult($bookingReference, true, null);
            return true;

        } catch (PHPMailerException $e) {
            // Deliberately caught here and nowhere else - this is the
            // boundary. Whatever went wrong (bad credentials, SMTP host
            // unreachable, invalid recipient), it gets logged and
            // swallowed, never re-thrown.
            $this->logResult($bookingReference, false, $mail->ErrorInfo);
            return false;
        }
    }

    private function buildEmailBody(
        string $reference, array $service, string $date,
        string $machineName, string $slotLabel, ?string $secondSlotLabel
    ): string {
        // Every style below is inline, deliberately - email clients strip
        // out <style> blocks and often ignore external/embedded CSS
        // entirely, so inline styling on each element is the only
        // reliable way to control how an email actually looks across
        // Gmail, Outlook, Apple Mail, etc.
        $secondSlotRow = $secondSlotLabel
            ? "<tr>
                <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>Second Slot</td>
                <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>" . htmlspecialchars($secondSlotLabel) . "</td>
              </tr>"
            : '';

        return "
        <div style='margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif;'>
            <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #f3f4f6; padding: 30px 0;'>
                <tr>
                    <td align='center'>
                        <table role='presentation' width='500' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);'>

                            <!-- Header - text-based logo, always renders even with
                                 images blocked (most email clients block remote
                                 images by default until the user clicks 'show
                                 images'). -->
                            <tr>
                                <td style='background-color: #1e3a8a; padding: 30px 40px; text-align: center;'>
                                    <!--
                                        LOGO PLACEHOLDER: once you have a real, publicly
                                        hosted logo image (e.g. https://yourdomain.com/logo.png
                                        - NOT a local XAMPP path, email clients can't reach
                                        that), add it above the text below with:
                                        <img src='https://yourdomain.com/logo.png' alt='LaundroBook'
                                             width='150' style='display:block;margin:0 auto 12px auto;border:0;'>
                                    -->
                                    <p style='color: #ffffff; font-size: 26px; font-weight: 700; margin: 0 0 4px 0; letter-spacing: 0.5px;'>
                                        <span style='color: #93c5fd;'>Laundro</span>Book
                                    </p>
                                    <p style='color: #bfdbfe; font-size: 13px; margin: 0; letter-spacing: 0.5px;'>BOOKING CONFIRMATION</p>
                                </td>
                            </tr>

                            <!-- Confirmation message -->
                            <tr>
                                <td style='padding: 30px 40px 10px 40px;'>
                                    <h1 style='color: #111827; font-size: 20px; margin: 0 0 8px 0;'>Your booking is confirmed!</h1>
                                    <p style='color: #6b7280; font-size: 14px; margin: 0 0 20px 0; line-height: 1.5;'>
                                        Thank you for booking with LaundroBook. Here are your details:
                                    </p>
                                </td>
                            </tr>

                            <!-- Reference badge -->
                            <tr>
                                <td style='padding: 0 40px 20px 40px;'>
                                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;'>
                                        <tr>
                                            <td style='padding: 14px 20px; text-align: center;'>
                                                <span style='color: #1e40af; font-size: 12px; letter-spacing: 0.5px;'>BOOKING REFERENCE</span><br>
                                                <span style='color: #1e3a8a; font-size: 22px; font-weight: 700;'>" . htmlspecialchars($reference) . "</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Details table -->
                            <tr>
                                <td style='padding: 0 40px 30px 40px;'>
                                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>Date</td>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>" . htmlspecialchars($date) . "</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>Service</td>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>" . htmlspecialchars(ucfirst($service['wash_type']) . ' - ' . ucfirst($service['load_type'])) . "</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>Duration</td>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>" . htmlspecialchars((string)$service['duration_minutes']) . " minutes</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>Machine</td>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>" . htmlspecialchars($machineName) . "</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>Time Slot</td>
                                            <td style='padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-weight: 600; text-align: right;'>" . htmlspecialchars($slotLabel) . "</td>
                                        </tr>
                                        {$secondSlotRow}
                                        <tr>
                                            <td style='padding: 14px 0 0 0; color: #111827; font-size: 15px; font-weight: 700;'>Total Price</td>
                                            <td style='padding: 14px 0 0 0; color: #1e3a8a; font-size: 18px; font-weight: 700; text-align: right;'>R" . htmlspecialchars(number_format((float)$service['price'], 2)) . "</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                    <p style='color: #9ca3af; font-size: 12px; margin: 0; line-height: 1.5;'>
                                        Questions about your booking? Contact the laundromat directly.<br>
                                        LaundroBook &copy; " . date('Y') . "
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </div>
        ";
    }

    private function buildPlainTextBody(
        string $reference, array $service, string $date,
        string $machineName, string $slotLabel, ?string $secondSlotLabel
    ): string {
        $lines = [
            "Booking Confirmed",
            "Reference: {$reference}",
            "Date: {$date}",
            "Service: " . ucfirst($service['wash_type']) . ' - ' . ucfirst($service['load_type']),
            "Duration: {$service['duration_minutes']} minutes",
            "Price: R" . number_format((float)$service['price'], 2),
            "Machine: {$machineName}",
            "Time Slot: {$slotLabel}",
        ];
        if ($secondSlotLabel) {
            $lines[] = "Second Slot: {$secondSlotLabel}";
        }
        return implode("\n", $lines);
    }

    // No AuditLogRepository/audit_log table exists yet in this project
    // (that's part of the Audit Trail functional requirement, not yet
    // built). this logs to PHP's own error log for now as a
    // lightweight stand-in.
    private function logResult(string $reference, bool $success, ?string $error): void
    {
        if ($success) {
            error_log("CONFIRMATION_EMAIL_SENT: {$reference}");
        } else {
            error_log("CONFIRMATION_EMAIL_FAILED: {$reference} - {$error}");
        }
    }
}