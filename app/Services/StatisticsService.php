<?php

namespace App\Services;

use App\Finders\ReservationFinder;
use App\Models\Location;
use App\Models\Statistics;
use Carbon\Carbon;

class StatisticsService
{
    /**
     * @param ReservationFinder $reservationFinder
     */
    public function __construct(private readonly ReservationFinder $reservationFinder){}

    /**
     * @param Location $location
     * @param Carbon $startOfDay
     * @return Statistics
     */
    public function getStatistics(Location $location, Carbon $startOfDay) : Statistics
    {
        $endOfDay = $startOfDay->copy()->setHour(config('availability.closing_hour'));
        $slotPointer = $startOfDay->copy();
        $reservationMap = [];

        while ($slotPointer->lt($endOfDay)) {
            $reservationMap[] = $this->getNumberOfReservations($location, $slotPointer);
            $slotPointer->addMinutes(config('availability.reservation_steps'));
        }

        $totalReservations = $this->reservationFinder->getReservationsForLocation($location->id)->count();
        return new Statistics($reservationMap,$totalReservations, $location->nest_amount,
            config('availability.closing_hour') - config('availability.opening_hour') );
    }

    /**
     * @param Location $location
     * @param Carbon $start
     * @return int
     */
    private function getNumberOfReservations(Location $location, Carbon $start) : int
    {
        $end = $start->clone()->addMinutes(config('availability.reservation_steps'));
        return $this->reservationFinder->getReservedNestsInPeriod($location->id, $start, $end);
    }


}
