<?php

namespace App\Domain\Reservations\Actions;

use App\Domain\Availability\Contracts\AvailabilityServiceInterface;
use App\Domain\Reservations\Enums\Size;
use App\Exceptions\NestNotAvailable;
use App\Models\Location;
use App\Models\Reservation;
use Carbon\Carbon;

class CreateReservation
{

    /**
     * @param Location $location
     * @param string $start
     * @param Size $size
     * @param int $status
     * @param AvailabilityServiceInterface $availabilityService
     * @return void
     * @throws NestNotAvailable
     */
    public function create(Location $location, string $start, Size $size, int $status, AvailabilityServiceInterface $availabilityService): void
    {
        $start = Carbon::createFromFormat( 'd/m/Y H:i', $start);
        $end = $start->copy()->addHours($size->value)->addMinutes(config('availability.cleaning_time'));

        if($availabilityService->isNestAvailable($location, $start, $end )) {
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
