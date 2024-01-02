<?php

return [
    'max_in_reception' => 4, // Max number of customer that are allowed to be in reception
    'reception_time' => 10, // In minutes, the period of time the max_in_reception customers will be evaluated
    'max_cleaning' => 2, // Max number of cleanings that can run in parallel in the cleaning_time
    'cleaning_time' => 15, // In minutes, duration of the cleaning period to be evaluated
    'opening_hour' => 10, //Facilities open at 10:00
    'closing_hour' => 24, // Facilities close at 10:00
    'reservation_steps' => 10, // In minutes, reservation steps
    'min_reservation_duration' => 2, // In hours, min duration of a reservation
    'max_reservation_duration' => 5, // In hours, max duration of a reservation
    'weights' => [5, 3, 2, 1],
];
