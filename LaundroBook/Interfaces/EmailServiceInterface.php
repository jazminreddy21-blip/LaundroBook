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
}