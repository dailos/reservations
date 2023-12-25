<?php

namespace App\Console\Commands;

use App\Domain\Availability\Factories\AvailabilityFactory;
use App\Domain\Reservations\Actions\CreateReservation;
use App\Domain\Reservations\Enums\Size;
use App\Domain\Statistics\Services\StatisticsService;
use App\Exceptions\NestNotAvailable;
use App\Models\Location;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class Reservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation {--type=free} {--attempt=2000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random reservations';

    private const DATE = '01/01/1970 ';

    /**
     * @param CreateReservation $createReservation
     * @param StatisticsService $statisticsService
     * @param AvailabilityFactory $availabilityFactory
     * @return void
     */
    public function handle(CreateReservation $createReservation,
                           StatisticsService $statisticsService,
                           AvailabilityFactory $availabilityFactory): void
    {
        $attempt = $this->option('attempt');
        $type = $this->option('type');
        DB::table('reservations')->delete(); //clean last run
        $i = 0;
        $location = Location::find(1); // Essen
        while ($i < $attempt)
        {
            $i++;
            $random = $this->generateReservation();
            $availabilityService = $availabilityFactory->getAvailabilityService($type);
            try {
                $createReservation->create($location,
                    self::DATE.$random['start'],
                    $random['size'],
                    Reservation::STATUS_CONFIRMED,
                    $availabilityService);
                $this->info("Reservation created from ".$random['start'].", duration ".$random['size']->value);
            }catch (NestNotAvailable){
                //$this->error("Nest not available from ".$random['start']." to ".$random['end']);
            }
        }

        $statistics = $statisticsService->getStatistics($location,
            Carbon::createFromFormat( 'd/m/Y H:i', self::DATE.config('availability.opening_hour').':00'));

        $statistics->print();
    }

    #[ArrayShape(['start' => "string", 'size' => Size::class])] private function generateReservation() : array
    {
        $startHour = random_int(
                                config('availability.opening_hour'),
                                config('availability.closing_hour') -  config('availability.min_reservation_duration')
                    );
        $duration = random_int(config('availability.min_reservation_duration'),config('availability.max_reservation_duration'));
        $size = Size::fromHours($duration);
        $minute = array_rand(array_flip(['00', '10', '20', '30', '40', '50']), 1);
        $startHourMinute = "$startHour:$minute";
        return [
            'start' => $startHourMinute,
            'size' => $size,
        ];
    }
}

