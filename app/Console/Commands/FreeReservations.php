<?php

namespace App\Console\Commands;

use App\Actions\CreateReservation;
use App\Exceptions\NestNotAvailable;
use App\Models\Location;
use App\Models\Reservation;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class FreeReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation:free';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random reservations';

    private const ATTEMPT = 2000;
    private const DATE = '01/01/1970 ';

    /**
     * @param CreateReservation $createReservation
     * @param StatisticsService $statisticsService
     * @return void
     * @throws \Exception
     */
    public function handle(CreateReservation $createReservation, StatisticsService $statisticsService): void
    {
        DB::table('reservations')->delete(); //clean last run
        $i = 0;
        $location = Location::find(1); // Essen
        while ($i < self::ATTEMPT)
        {
            $i++;
            $random = $this->generateReservation();
            try {
                $createReservation->create($location,
                    self::DATE.$random['start'],
                    self::DATE.$random['end'],
                    Reservation::STATUS_CONFIRMED );
                $this->info("Reservation created from ".$random['start']." to ".$random['end']);
            }catch (NestNotAvailable){
                //$this->error("Nest not available from ".$random['start']." to ".$random['end']);
            }
        }

        $statistics = $statisticsService->getStatistics($location,
            Carbon::createFromFormat( 'd/m/Y H:i', self::DATE.config('availability.opening_hour').':00'));

        $statistics->print();
    }

    #[ArrayShape(['start' => "string", 'end' => "string"])] private function generateReservation() : array
    {
        $startHour = random_int(
                                config('availability.opening_hour'),
                                config('availability.closing_hour') -  config('availability.min_reservation_duration')
                    );
        $duration = random_int(config('availability.min_reservation_duration'),config('availability.max_reservation_duration'));
        $minute = array_rand(array_flip(['00', '10', '20', '30', '40', '50']), 1);
        $endHour = $startHour + $duration;
        $startHourMinute = "$startHour:$minute";
        $endHourMinute = "$endHour:$minute";
        return [
            'start' => $startHourMinute,
            'end' => $endHourMinute
        ];
    }
}

