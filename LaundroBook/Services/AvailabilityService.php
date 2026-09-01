<?php

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

//  AvailabilityService
//   The one place Machine and Slot are allowed to meet. Type-hinted
//   against interfaces (not concrete repository classes), so this can be
//   built with real or stub repositories interchangeably.
//   Nothing in this class writes to the database - safe to call from an
//   unauthenticated, unconfirmed visitor because it can only ever read.
//   Used by both AvailabilityController (public check) and
//   BookingController (final re-check right before insert).
class AvailabilityService
{
    private MachineRepoInterface $machines;
    private SlotRepoInterface $slots;
    private BookingRepoInterface $bookings;

    public function __construct(
        MachineRepoInterface $machines,
        SlotRepoInterface $slots,
        BookingRepoInterface $bookings
    ) {
        $this->machines = $machines;
        $this->slots = $slots;
        $this->bookings = $bookings;
    }

    //  Returns a grid of machine/slot combinations that are free on the
    //  given date, taking duration_slots into account (Heavy Wash needs
    //  two consecutive free slots on the same machine).
    public function getAvailableCombos(string $bookingDate, int $durationSlots = 1): array
    {
        $availableMachines = $this->machines->getAvailableMachines();
        $activeSlots = $this->slots->getActiveSlots(); // expected sorted by start_time
        $bookedCombos = $this->bookings->getBookedCombosForDate($bookingDate);

        $taken = [];
        foreach ($bookedCombos as $combo) {
            $taken["{$combo['machine_id']}:{$combo['slot_id']}"] = true;
        }

        $results = [];

        foreach ($availableMachines as $machine) {
            foreach ($activeSlots as $index => $slot) {
                if ($durationSlots === 1) {
                    if (!isset($taken["{$machine['machine_id']}:{$slot['slot_id']}"])) {
                        $results[] = [
                            'machine_id' => $machine['machine_id'],
                            'machine_name' => $machine['machine_name'],
                            'slot_id' => $slot['slot_id'],
                            'slot_label' => $slot['slot_label'],
                        ];
                    }
                    continue;
                }

                // Heavy Wash: this slot AND the next one must both be free
                // on the same machine, and a "next slot" must exist.
                $nextSlot = $activeSlots[$index + 1] ?? null;
                if ($nextSlot === null) {
                    continue;
                }

                $thisFree = !isset($taken["{$machine['machine_id']}:{$slot['slot_id']}"]);
                $nextFree = !isset($taken["{$machine['machine_id']}:{$nextSlot['slot_id']}"]);

                if ($thisFree && $nextFree) {
                    $results[] = [
                        'machine_id' => $machine['machine_id'],
                        'machine_name' => $machine['machine_name'],
                        'slot_id' => $slot['slot_id'],
                        'slot_label' => $slot['slot_label'],
                        'second_slot_id' => $nextSlot['slot_id'],
                    ];
                }
            }
        }

        return $results;
    }

    //  Final check run right before insert in BookingController.
    //  Re-verifies the specific combo the customer picked is still free.
    public function isComboStillFree(int $machineId, int $slotId, string $bookingDate): bool
    {
        $bookedCombos = $this->bookings->getBookedCombosForDate($bookingDate);

        foreach ($bookedCombos as $combo) {
            if ((int)$combo['machine_id'] === $machineId && (int)$combo['slot_id'] === $slotId) {
                return false;
            }
        }

        return true;
    }
}