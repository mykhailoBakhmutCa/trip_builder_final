<?php

namespace App\Services;

use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use DateTimeZone;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class TripSearcher
{
    protected TimezoneService $timezoneService;
    protected int $perPage = 5;

    public function __construct(TimezoneService $timezoneService)
    {
        $this->timezoneService = $timezoneService;
    }

    public function searchAndPaginate(Request $request): LengthAwarePaginator
    {
        $departureCode = $request->input('from');
        $arrivalCode = $request->input('to');
        $departureDate = $request->input('departure_date');
        $returnDate = $request->input('return_date');
        $airlineCode = $request->input('airline_code');
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));

        $trips = [];

        $outboundFlights = $this->findDirectFlights($departureCode, $arrivalCode, $departureDate, $airlineCode);

        foreach ($outboundFlights as $flight) {
            $trips[] = (new Trip('one-way', [$flight]))->toArray();
        }

        if ($returnDate) {
            $returnFlights = $this->findDirectFlights($arrivalCode, $departureCode, $returnDate, $airlineCode);
            if ($returnFlights->isNotEmpty()) {
                foreach ($outboundFlights as $outbound) {
                    foreach ($returnFlights as $return) {
                        $trips[] = (new Trip('round-trip', [$outbound, $return]))->toArray();
                    }
                }
            }
        }

        if ($sortBy && !empty($trips)) {
            $trips = $this->sortTrips($trips, $sortBy, $sortDir);
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($trips, $this->perPage * ($currentPage - 1), $this->perPage);

        return new LengthAwarePaginator($currentItems, count($trips), $this->perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function findDirectFlights(string $fromCode, string $toCode, string $date, ?string $airlineCode = null): Collection
    {
        $flights = Flight::with(['airline', 'departureAirport', 'arrivalAirport'])
            ->where('departure_airport_code', $fromCode)
            ->where('arrival_airport_code', $toCode);

        if ($airlineCode) {
            $flights->where('airline_code', $airlineCode);
        }

        $flights = $flights->get();

        return $flights->map(function ($flight) use ($date) {
            try {
                $times = $this->timezoneService->calculateFlightTimes(
                    $flight->departureAirport->timezone,
                    $flight->arrivalAirport->timezone,
                    $date,
                    $flight->departure_time,
                    $flight->arrival_time
                );

                $durationInSeconds = $times['duration_seconds'];
                $hours = floor($durationInSeconds / 3600);
                $minutes = floor(($durationInSeconds % 3600) / 60);
                $durationFormatted = sprintf('%dh %02dm', $hours, $minutes);

                return [
                    'id' => $flight->id,
                    'flight_number' => $flight->airline->code . $flight->number,
                    'airline' => $flight->airline->name,
                    'price' => (float) $flight->price,
                    'departure' => [
                        'airport_code' => $flight->departureAirport->code,
                        'city' => $flight->departureAirport->city,
                        'time_local' => $times['departure']->format('H:i'),
                        'date_local' => $times['departure']->format('Y-m-d'),
                        'timezone' => $flight->departureAirport->timezone,
                        'datetime_utc' => $times['departure']->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                    ],
                    'arrival' => [
                        'airport_code' => $flight->arrivalAirport->code,
                        'city' => $flight->arrivalAirport->city,
                        'time_local' => $times['arrival']->format('H:i'),
                        'date_local' => $times['arrival']->format('Y-m-d'),
                        'timezone' => $flight->arrivalAirport->timezone,
                        'datetime_utc' => $times['arrival']->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                    ],
                    'duration' => $durationFormatted,
                ];
            } catch (Exception $e) {
                Log::error("Flight calculation error: " . $e->getMessage());
                return null;
            }
        })->filter()->values();
    }

    private function sortTrips(array $trips, string $sortBy, string $sortDir): array
    {
        usort($trips, function ($a, $b) use ($sortBy, $sortDir) {

            if ($sortBy === 'price') {
                $comparison = $a['total_price'] <=> $b['total_price'];
            } elseif ($sortBy === 'duration') {
                $aDuration = Carbon::parse($a['info']['arrival_utc'])->diffInSeconds(Carbon::parse($a['info']['departure_utc']));
                $bDuration = Carbon::parse($b['info']['arrival_utc'])->diffInSeconds(Carbon::parse($b['info']['departure_utc']));

                $comparison = $aDuration <=> $bDuration;

                if ($comparison === 0) {
                    $comparison = $a['total_price'] <=> $b['total_price'];
                }
            } else {
                return 0;
            }

            return ($sortDir === 'desc') ? -$comparison : $comparison;
        });

        return $trips;
    }
}
