<?php

namespace App\Domain\Availability\Contracts;

use App\Models\Location;
use Carbon\Carbon;

interface AvailabilityServiceInterface
{
    /**
     * @param Location $location
     * @param Carbon $proposedStart
     * @param Carbon $proposedEnd
     * @return bool
     */
    public function isNestAvailable(Location $location, Carbon $proposedStart, Carbon $proposedEnd) : bool;
}
