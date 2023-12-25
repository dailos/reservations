<?php

namespace App\Finders;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationFinder
{

    /**
     * @var Reservation
     */
    protected Reservation $reservation;

    /**
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * @param int $location
     * @return Collection
     */
    public function getReservationsForLocation(int $location) : Collection
    {

        return $this->reservation
            ->with(['location' => function ($q) { $q->select('id', 'nest_amount'); }])
            ->select('start', 'end', 'location_id')
            ->where('location_id', $location)
            ->get();
    }

    /**
     * @param int $location
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function getReservedNestsInPeriod(int $location, Carbon $start, Carbon $end)
    {
        return $this->reservation
            ->where('location_id', $location)
            ->where('reservations.start', '<', $end->toDateTimeString())
            ->where('reservations.end', '>', $start->toDateTimeString() )
            ->count();
    }

}
