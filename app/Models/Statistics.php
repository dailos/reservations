<?php

namespace App\Models;



class Statistics
{
    private float $waste;


    /**
     * @param array $reservationMap
     * @param int $totalReservations
     * @param int $numberOfNests
     * @param int $openingHours
     */
    public function __construct(private readonly array $reservationMap,
                                private readonly int $totalReservations,
                                private readonly int $numberOfNests,
                                private readonly int $openingHours)
    {
        $this->waste = $this->calculateWastePercentage();
    }

    /**
     * @return void
     */
    public function print() : void
    {
        print_r($this->getReservationMap());
        echo "Waste: " . $this->getWaste() . "%\n";
        echo "Number of reservations: " . $this->getTotalReservations() . "\n";
    }

    /**
     * @return array
     */
    private function getReservationMap(): array
    {
        return $this->reservationMap;
    }

    /**
     * @return float
     */
    private function getWaste(): float
    {
        return round($this->waste, 2);
    }

    /**
     * @return int
     */
    private function getTotalReservations(): int
    {
        return $this->totalReservations;
    }


    /**
     * @return float
     */
    private function calculateWastePercentage(): float
    {
        $totalSlots = $this->numberOfNests * $this->openingHours * 6;
        $usedSlots = array_sum($this->reservationMap);
        return 100 - ($usedSlots/$totalSlots * 100);
    }

}
