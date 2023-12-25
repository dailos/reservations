<?php

namespace App\Services;

use App\Finders\ReservationFinder;
use App\Models\Location;
use Carbon\Carbon;

class AvailabilityService
{
    private const MAX_IN_RECEPTION = 2;
    private const RECEPTION_TIME = 10; //minutes
    private const MAX_CLEANINGS = 2;
    public const CLEANING_TIME = 15; //minutes


    /**
     * @param ReservationFinder $reservationFinder
     */
    public function __construct(private readonly ReservationFinder $reservationFinder)
    {}

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
        $receptionEnd = $proposedStart->clone()->addMinutes(self::RECEPTION_TIME);
        $cleaningStart = $proposedEnd->clone()->subMinutes(self::CLEANING_TIME);

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
            $endHall = Carbon::make($reservation['start'])->addMinutes(self::RECEPTION_TIME);
            if (($proposedStart->greaterThanOrEqualTo($start) && $proposedStart->lessThan($endHall)) ||
                ($receptionEnd->greaterThan($start) && $receptionEnd->lessThanOrEqualTo($endHall)) ||
                ($proposedStart->lessThanOrEqualTo($start) && $receptionEnd->greaterThanOrEqualTo($endHall))) {

                $concurrentCustomersInReception++;
                if ($concurrentCustomersInReception >= self::MAX_IN_RECEPTION) {
                    return false;
                }
            }
            //Checks concurrent cleanings
            $startCleaning = Carbon::make($reservation['end'])->subMinutes(self::CLEANING_TIME);
            if (($cleaningStart->greaterThanOrEqualTo($startCleaning) && $cleaningStart->lessThan($end)) ||
                ($proposedEnd->greaterThan($startCleaning) && $proposedEnd->lessThanOrEqualTo($end)) ||
                ($cleaningStart->lessThanOrEqualTo($startCleaning) && $proposedEnd->greaterThanOrEqualTo($end))) {
                $concurrentCleanings++;
                if($concurrentCleanings >= self::MAX_CLEANINGS){
                     return false;
                }
            }
        }

        return true;
    }
}
