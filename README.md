## To run the simulation you only need docker.

### Installation

1. copy the .env.example to .env: `cp .env.example .env`
2. if you don't have local php: 
   
  `docker run --rm \
   -u "$(id -u):$(id -g)" \
   -v $(pwd):/opt \
   -w /opt \
   laravelsail/php82-composer:latest \
   composer install`
  
otherwise, just run `composer install`

3. Start the environment: `sail up -d`

4. You may need to create an APP Key : `sail artisan key:generate`
5. Run the migration: `sail artisan migrate`

6. Run the seeder: `sail artisan db:seed` , it will create 2 locations, Essen with 14 nests and Hamburg with 19

### Configuration
You can adjust some params in `config/availability.php` file:
- `max_in_reception`: Max number of customer that are allowed to be in reception
- `reception_time`:  In minutes, the period of time the max_in_reception customers will be evaluated
- `max_cleaning`:  Max number of cleanings that can run in parallel
- `cleaning_time`: In minutes, duration of the cleaning period to be evaluated
- `opening_hour`: Facilities opening hour, integer
- `closing_hour`:  Facilities closing hour, integer
- `reservation_steps`: In minutes, reservation steps, for example, every 10 minutes
- `min_reservation_duration`: In hours, min duration of a reservation
- `max_reservation_duration`: In hours, max duration of a reservation

### Running the simulation
Run the simulation: `sail artisan reservation --type=<free/grip> --attempt=<2000>`

The output will 
