<?php
/*
    Delivery / pickup time estimates - STATIC PLACEHOLDER VALUES.

    These are fixed numbers, not live scheduling. They are set to look
    realistic for each service, and can be changed here without touching
    any other code. All values are in minutes.

    How an estimate is built (see Services/DeliveryEstimator.php):
        wash ends  (end of the booked slot, or of the 2nd slot for Heavy)
      + finishing  (drying and folding - differs per wash type and load)
      = READY at
      + travel buffer
      = delivery window STARTS, and it lasts 'window_minutes'.

    Collection (home pickup) is 'collection_lead_minutes' before the
    first slot starts.
*/
return [
    'collection_lead_minutes' => 60,
    'travel_buffer_minutes'   => 30,
    'window_minutes'          => 60,
    'rounding_minutes'        => 15,   // window start is rounded UP to this

    // Drying + folding time after the wash finishes.
    // Priority is quickest, Heavy takes longest; beddings dry slowest.
    'finishing_minutes' => [
        'priority' => ['clothes' => 30, 'beddings' => 45, 'towels' => 35],
        'normal'   => ['clothes' => 60, 'beddings' => 90, 'towels' => 75],
        'heavy'    => ['clothes' => 90, 'beddings' => 120, 'towels' => 105],
    ],
    'default_finishing_minutes' => 60,

    // Days after the wash date before the laundry is ready/delivered.
    // 0 = same day (uses the finishing minutes above); 1 or more = ready
    // at 'later_day_ready_time' and delivered from 'later_day_delivery_time'
    // on that day. Only quick, light loads are same-day.
    'turnaround_days' => [
        'priority' => ['clothes' => 0, 'beddings' => 1, 'towels' => 0],
        'normal'   => ['clothes' => 1, 'beddings' => 2, 'towels' => 1],
        'heavy'    => ['clothes' => 1, 'beddings' => 2, 'towels' => 2],
    ],
    'default_turnaround_days'  => 1,
    'later_day_ready_time'     => '09:00',
    'later_day_delivery_time'  => '10:00',  // used if a new service isn't listed above
];