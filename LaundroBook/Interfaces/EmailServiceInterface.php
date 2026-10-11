<?php

/**
 * EmailServiceInterface
 *
 * Kept as its own interface, separate from the repository interfaces
 * file, since this isn't a repository - but the same reasoning applies
 * as everywhere else in this project: BookingService should depend on
 * this contract, not on the concrete EmailService class directly, so
 * a test can swap in a stub that never touches real SMTP.
 */
interface EmailServiceInterface
{
    /**
     * Fire-and-forget by design - this must never throw. A failure
     * here should never be able to undo or block a booking that has
     * already been committed to the database. Implementations should
     * catch their own exceptions internally and return false rather
     * than letting anything propagate.
     */
    public function sendBookingConfirmation(
        string $customerEmail,
        string $bookingReference,
        array $service,
        string $bookingDate,
        string $machineName,
        string $slotLabel,
        ?string $secondSlotLabel
    ): bool;

    /**
     * Sent when an admin marks a booking (or booking group, for a
     * Heavy Wash) as completed - matches sendBookingConfirmation()'s
     * same fire-and-forget contract, must never throw.
     */
    public function sendOrderCompleteEmail(
        string $customerEmail,
        string $bookingReference,
        array $service
    ): bool;

    /**
     * Sent when an admin cancels a booking. Deliberately a separate
     * method from sendOrderCompleteEmail() rather than reusing it with
     * a flag - reusing "your laundry is ready" wording for a
     * cancellation would be actively misleading, this needs its own
     * genuinely different message. Same fire-and-forget contract.
     */
    public function sendOrderCancelledEmail(
        string $customerEmail,
        string $bookingReference,
        array $service
    ): bool;
}