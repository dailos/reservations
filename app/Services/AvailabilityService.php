<?php

namespace App\Services;

use App\Finders\ReservationFinder;
use App\Models\Location;
use Carbon\Carbon;

class AvailabilityService
{

    private int $receptionTime;
    private int $cleaningTime;
    private int $maxCleaning;
    private int $maxInReception;
    /**
     * @param ReservationFinder $reservationFinder
     */
    public function __construct(private readonly ReservationFinder $reservationFinder)
    {
        $this->cleaningTime = config('availability.cleaning_time');
        $this->receptionTime = config('availability.reception_time');
        $this->maxCleaning = config('availability.max_cleaning');
        $this->maxInReception = config('availability.max_in_reception');
    }

    /**
     * @param Location $location
     * @param Carbon $proposedStart
     * @param Carbon $proposedEnd
     * @return bool
     */
    public function isNestAvailable(Location $location, Carbon $proposedStart, Carbon $proposedEnd) : bool
    {
        $concurrentReservations = 0;
        $concurrentCleanings = 0;
        $concurrentCustomersInReception = 0;
        $reservations = $this->reservationFinder->getReservationsForLocation($location->id)->toArray();
        $receptionEnd = $proposedStart->clone()->addMinutes($this->receptionTime);
        $cleaningStart = $proposedEnd->clone()->subMinutes($this->cleaningTime);

        //checks overbooking
        foreach ($reservations as $reservation) {
            $start = Carbon::make($reservation['start']);
            $end = Carbon::make($reservation['end']);

            if (($proposedStart->greaterThanOrEqualTo($start) && $proposedStart->lessThan($end)) ||
                ($proposedEnd->greaterThan($start) && $proposedEnd->lessThanOrEqualTo($end)) ||
                ($proposedStart->lessThanOrEqualTo($start) && $proposedEnd->greaterThanOrEqualTo($end))) {
                $concurrentReservations++;
                if ($concurrentReservations >= $location->nest_amount) {
                    return false;
                }
            }
            //checks customers in hall
            $endHall = Carbon::make($reservation['start'])->addMinutes($this->receptionTime);
            if (($proposedStart->greaterThanOrEqualTo($start) && $proposedStart->lessThan($endHall)) ||
                ($receptionEnd->greaterThan($start) && $receptionEnd->lessThanOrEqualTo($endHall)) ||
                ($proposedStart->lessThanOrEqualTo($start) && $receptionEnd->greaterThanOrEqualTo($endHall))) {

                $concurrentCustomersInReception++;
                if ($concurrentCustomersInReception >= $this->maxInReception) {
                    return false;
                }
            }
            //Checks concurrent cleanings
            $startCleaning = Carbon::make($reservation['end'])->subMinutes($this->cleaningTime);
            if (($cleaningStart->greaterThanOrEqualTo($startCleaning) && $cleaningStart->lessThan($end)) ||
                ($proposedEnd->greaterThan($startCleaning) && $proposedEnd->lessThanOrEqualTo($end)) ||
                ($cleaningStart->lessThanOrEqualTo($startCleaning) && $proposedEnd->greaterThanOrEqualTo($end))) {
                $concurrentCleanings++;
                if($concurrentCleanings >= $this->maxCleaning){
                     return false;
                }
            }
        }

        return true;
    }
}
