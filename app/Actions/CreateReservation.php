<?php

namespace App\Actions;

use App\Exceptions\NestNotAvailable;
use App\Models\Location;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Carbon\Carbon;

class CreateReservation
{

    public function __construct(private readonly AvailabilityService $availabilityService)
    {}

    /**
     * @param Location $location
     * @param string $start
     * @param string $end
     * @param int $status
     * @return void
     * @throws NestNotAvailable
     */
    public function create(Location $location, string $start, string $end, int $status): void
    {
        $start = Carbon::createFromFormat( 'd/m/Y H:i', $start);
        $end = Carbon::createFromFormat( 'd/m/Y H:i', $end)->addMinutes(config('availability.cleaning_time'));

        if($this->availabilityService->isNestAvailable($location, $start, $end )) {
            Reservation::create([
                'start' => $start,
                'end' => $end,
                'status' => $status,
                'location_id' => $location->id
            ]);
        }else{
            throw new NestNotAvailable();
        }
    }
}
