<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\TimezoneService;
use DateTimeZone;

class TimezoneServiceTest extends TestCase
{
    protected TimezoneService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TimezoneService();
    }

    public function test_simple_timezone_shift()
    {
        $date = '2025-12-01';
        $times = $this->service->calculateFlightTimes(
            'America/Montreal', // UTC-5
            'America/Chicago',  // UTC-6
            $date,
            '15:00',
            '16:30'
        );

        $this->assertEquals(9000, $times['duration_seconds']); // 2h 30m

        $this->assertEquals($date, $times['arrival']->format('Y-m-d'));

        // (20:00 UTC + 2h 30m = 22:30 UTC)
        $this->assertEquals('22:30', $times['arrival']->setTimezone(new DateTimeZone('UTC'))->format('H:i'));
    }

    public function test_overnight_flight_and_day_change()
    {
        $departureDate = '2025-12-01';
        $expectedArrivalDate = '2025-12-02';

        $times = $this->service->calculateFlightTimes(
            'America/Chicago',  // UTC-6
            'Europe/London',    // UTC+0
            $departureDate,
            '18:00', // 2025-12-02 00:00 UTC
            '07:40'
        );

        $this->assertEquals(7 * 3600 + 40 * 60, $times['duration_seconds']); // 7h 40m = 27600s

        $this->assertEquals($expectedArrivalDate, $times['arrival']->format('Y-m-d'));
        $this->assertEquals('07:40', $times['arrival']->format('H:i'));
    }
}
