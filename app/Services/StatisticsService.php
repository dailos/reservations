<?php

namespace App\Services;

use App\Finders\ReservationFinder;
use App\Models\Location;
use App\Models\Statistics;
use Carbon\Carbon;

class StatisticsService
{
    private const OPENING_HOUR = 10;
    private const CLOSING_HOUR = 24;
    private const STEP = 10; //10 min


    /**
     * @param ReservationFinder $reservationFinder
     */
    public function __construct(private readonly ReservationFinder $reservationFinder)
    {}

    /**
     * @param Location $location
     * @param Carbon $startOfDay
     * @return Statistics
     */
    public function getStatistics(Location $location, Carbon $startOfDay) : Statistics
    {
        $endOfDay = $startOfDay->copy()->setHour(self::CLOSING_HOUR);
        $slotPointer = $startOfDay->copy();
        $reservationMap = [];

        while ($slotPointer->lt($endOfDay)) {
            $reservationMap[] = $this->getNumberOfReservations($location, $slotPointer);
            $slotPointer->addMinutes(self::STEP);
        }

        $totalReservations = $this->reservationFinder->getReservationsForLocation($location->id)->count();
        return new Statistics($reservationMap,$totalReservations, $location->nest_amount,
                    self::CLOSING_HOUR - self::OPENING_HOUR );
    }

    /**
     * @param Location $location
     * @param Carbon $start
     * @return int
     */
    private function getNumberOfReservations(Location $location, Carbon $start) : int
    {
        $end = $start->clone()->addMinutes(self::STEP);
        return $this->reservationFinder->getReservedNestsInPeriod($location->id, $start, $end);
    }


}
