<?php

namespace App\Domain\Availability\Factories;

use App\Domain\Availability\Contracts\AvailabilityServiceInterface;
use App\Domain\Availability\Services\FreeAvailabilityService;

class AvailabilityFactory
{
    public const FREE = 'free';
    public const GRID = 'grid';

    public function __construct(private readonly FreeAvailabilityService $freeAvailabilityService) {}


    public function getAvailabilityService(string $type) : AvailabilityServiceInterface
    {
        if($type === self::FREE){
            return $this->freeAvailabilityService;
        }
    }
}
