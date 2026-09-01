<?php

class Slot
{
    private $slot_id;
    private $manager_id;
    private $slot_label;
    private $start_time;
    private $end_time;
    private $is_active;

    public function __construct(
        $slot_id,
        $manager_id,
        $slot_label,
        $start_time,
        $end_time,
        $is_active
    ) {
        $this->slot_id = $slot_id;
        $this->manager_id = $manager_id;
        $this->slot_label = $slot_label;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->is_active = $is_active;
    }

    public function getSlotId()
    {
        return $this->slot_id;
    }

    public function getSlotLabel()
    {
        return $this->slot_label;
    }

    public function printSlotDetails()
    {
        echo "Slot: " . $this->slot_label . "<br>";
        echo "Start time: " . $this->start_time . "<br>";
        echo "End time: " . $this->end_time . "<br>";
        echo "Active: " . ($this->is_active ? "Yes" : "No");
    }
}