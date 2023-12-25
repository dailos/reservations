<?php

namespace App\Domain\Reservations\Enums;

enum Size : int
{
    case S = 2;
    case M = 3;
    case L = 4;
    case XL = 5;

    public static function fromHours(int $hours): self
    {
        return match (true) {
            $hours === 3 => self::M,
            $hours === 4 => self::L,
            $hours === 5 => self::XL,

            default => self::S,
        };
    }
}
