<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Trip
{
    public string $type;

    public float $totalPrice;

    public ?Carbon $departureUtc;

    public ?Carbon $arrivalUtc;

    public array $segments = [];

    public function __construct(string $type, Collection|array $segments)
    {
        $this->segments = is_array($segments) ? $segments : $segments->toArray();
        $this->type = $type;
        $this->calculateTotals();
    }

    protected function calculateTotals(): void
    {
        $this->totalPrice = array_sum(
            array_column($this->segments, 'price')
        );

        if (!empty($this->segments)) {
            usort($this->segments, function($a, $b) {
                return $a['departure']['datetime_utc'] <=> $b['departure']['datetime_utc'];
            });

            $firstSegment = reset($this->segments);
            $lastSegment = end($this->segments);

            $this->departureUtc = Carbon::parse($firstSegment['departure']['datetime_utc']);
            $this->arrivalUtc = Carbon::parse($lastSegment['arrival']['datetime_utc']);
        }
    }

    protected function getDurationFormatted(): string
    {
        if (!$this->departureUtc || !$this->arrivalUtc) {
            return 'N/A';
        }

        $duration = $this->departureUtc->diff($this->arrivalUtc);

        $durationFormatted = '';
        if ($duration->days > 0) {
            $durationFormatted .= $duration->days . 'days ';
        }
        $durationFormatted .= sprintf('%dh %02dm', $duration->h, $duration->i);

        return trim($durationFormatted);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'segments' => $this->segments,
            'total_price' => round($this->totalPrice, 2),
            'info' => [
                'departure_location' => $this->segments[0]['departure']['city'],
                'arrival_location' => end($this->segments)['arrival']['city'],
                'departure_utc' => $this->departureUtc?->toDateTimeString(),
                'arrival_utc' => $this->arrivalUtc?->toDateTimeString(),
                'total_duration' => $this->getDurationFormatted(),
            ]
        ];
    }
}
