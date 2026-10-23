<?php

/**
 * DeliveryEstimator
 * Turns a booking's slot times into estimated pickup, ready and
 * delivery times, using the static placeholder numbers in
 * Config/DeliveryTimes.php. Pure calculation.
 */
class DeliveryEstimator
{
    private array $cfg;

    public function __construct(?array $config = null)
    {
        $this->cfg = $config ?? require __DIR__ . '/../Config/DeliveryTimes.php';
    }

    /**
     * $slotStart / $lastSlotEnd: any strtotime-readable value; only the
     * time of day is used (slots are stored with an arbitrary date).
     * For a Heavy Wash $lastSlotEnd is the END of the second slot.
     */
    public function estimate(string $washType, string $loadType, string $bookingDate, string $slotStart, string $lastSlotEnd): array
    {
        $start = strtotime($bookingDate . ' ' . date('H:i:s', strtotime($slotStart)));
        $end   = strtotime($bookingDate . ' ' . date('H:i:s', strtotime($lastSlotEnd)));

        $finishing = (int)($this->cfg['finishing_minutes'][$washType][$loadType]
            ?? $this->cfg['default_finishing_minutes']);

        $days = (int)($this->cfg['turnaround_days'][$washType][$loadType]
            ?? $this->cfg['default_turnaround_days']);

        if ($days > 0) {
            // Ready/delivered on a later day at fixed morning times.
            $day         = date('Y-m-d', strtotime($bookingDate . " +{$days} days"));
            $ready       = strtotime($day . ' ' . $this->cfg['later_day_ready_time']);
            $windowStart = strtotime($day . ' ' . $this->cfg['later_day_delivery_time']);
        } else {
            $ready       = $end + $finishing * 60;
            $windowStart = $this->roundUp($ready + (int)$this->cfg['travel_buffer_minutes'] * 60);
        }
        $windowEnd   = $windowStart + (int)$this->cfg['window_minutes'] * 60;
        $collection  = $start - (int)$this->cfg['collection_lead_minutes'] * 60;

        return [
            'collection_time' => date('Y-m-d H:i:s', $collection),
            'ready_time'      => date('Y-m-d H:i:s', $ready),
            'window_start'    => date('Y-m-d H:i:s', $windowStart),
            'window_end'      => date('Y-m-d H:i:s', $windowEnd),
            'ready_text'      => self::formatMoment($ready),
            'window_text'     => self::formatWindow($windowStart, $windowEnd),
        ];
    }

    /** Window text for an already-scheduled time (used by the tracking pages). */
    public function windowFromScheduled(string $scheduledTime): string
    {
        $s = strtotime($scheduledTime);
        return self::formatWindow($s, $s + (int)$this->cfg['window_minutes'] * 60);
    }

    private function roundUp(int $ts): int
    {
        $step = max(1, (int)$this->cfg['rounding_minutes']) * 60;
        return (int)(ceil($ts / $step) * $step);
    }

    private static function formatMoment(int $ts): string
    {
        return date('D, d M Y \a\t H:i', $ts);
    }

    private static function formatWindow(int $from, int $to): string
    {
        return date('D, d M Y', $from) . ', ' . date('H:i', $from) . ' - ' . date('H:i', $to);
    }
}