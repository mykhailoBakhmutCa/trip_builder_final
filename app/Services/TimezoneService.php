<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

class TimezoneService
{
    /**
     * @throws Exception
     */
    public function calculateFlightTimes(
        string $departureAirportTimezone,
        string $arrivalAirportTimezone,
        string $departureDate,
        string $departureTime,
        string $arrivalTime
    ): array {
        try {
            $depTimezone = new DateTimeZone($departureAirportTimezone);
            $arrivalTimezone = new DateTimeZone($arrivalAirportTimezone);
            $departureString = "{$departureDate} {$departureTime}";
            $departureDateTime = new DateTimeImmutable($departureString, $depTimezone);

            $depUtcTimestamp = $departureDateTime->setTimezone(new DateTimeZone('UTC'))->getTimestamp();

            $arrivalBaseDateTime = new DateTimeImmutable("{$departureDate} {$arrivalTime}", $arrivalTimezone);
            $arrUtcTimestamp = $arrivalBaseDateTime->setTimezone(new DateTimeZone('UTC'))->getTimestamp();

            $durationSeconds = $arrUtcTimestamp - $depUtcTimestamp;

            if ($durationSeconds < 0) {
                $durationSeconds += (24 * 3600);
            }

            if ($durationSeconds <= 0 || $durationSeconds > (20 * 3600)) {
                throw new Exception("Flight duration is unrealistic or invalid ($durationSeconds seconds).");
            }

            $arrivalDateTimeCalculated = $departureDateTime
                ->modify("+{$durationSeconds} seconds")
                ->setTimezone($arrivalTimezone);

            return [
                'departure' => $departureDateTime,
                'arrival' => $arrivalDateTimeCalculated,
                'duration_seconds' => $durationSeconds
            ];

        } catch (Exception $e) {
            throw new Exception("Error calculating flight time: " . $e->getMessage());
        }
    }
}
