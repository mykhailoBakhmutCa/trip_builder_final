<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\SampleDataSeeder;
use Carbon\Carbon;

class TripSearchTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(SampleDataSeeder::class);
    }

    public function test_validation_prevents_past_date_search()
    {
        $pastDate = Carbon::now()->subDay(5)->format('Y-m-d');

        $response = $this->get('/trips/search?from=YUL&to=YVR&departure_date=' . $pastDate);

        $response->assertSessionHasErrors('departure_date');
        $response->assertStatus(302);
    }

    public function test_search_results_can_be_sorted_by_price_desc()
    {
        $futureDate = Carbon::now()->addMonth()->format('Y-m-d');
        $returnDate = Carbon::now()->addMonth()->addWeek()->format('Y-m-d');

        $response = $this->get("/trips/search?from=YUL&to=YVR&departure_date={$futureDate}&return_date={$returnDate}&sort_by=price&sort_dir=desc");

        $trips = $response->viewData('trips');

        $this->assertEquals('round-trip', $trips->items()[0]['type']);
        $this->assertGreaterThan($trips->items()[1]['total_price'], $trips->items()[0]['total_price']);
    }

    public function test_successful_round_trip_search_returns_correct_data()
    {
        $futureDate = Carbon::now()->addMonth()->format('Y-m-d');
        $returnDate = Carbon::now()->addMonth()->addWeek()->format('Y-m-d');

        $response = $this->get("/trips/search?from=YUL&to=YVR&departure_date={$futureDate}&return_date={$returnDate}&sort_by=price&sort_dir=desc");

        $trips = $response->viewData('trips');

        $this->assertEquals(15, $trips->total());

        $roundTrip = collect($trips->items())->where('type', 'round-trip')->first();
        $this->assertNotNull($roundTrip);
        $this->assertGreaterThanOrEqual(400.00, $roundTrip['total_price']);
    }

    public function test_search_results_can_be_sorted_by_duration_desc()
    {
        $futureDate = Carbon::now()->addMonth()->format('Y-m-d');

        $response = $this->get("/trips/search?from=YUL&to=YVR&departure_date={$futureDate}&sort_by=duration&sort_dir=desc");

        $trips = $response->viewData('trips')->items();

        $this->assertEquals('AC307', $trips[0]['segments'][0]['flight_number']);

        $this->assertCount(5, $trips);
    }

    public function test_search_results_are_paginated_correctly()
    {
        $futureDate = Carbon::now()->addMonth()->format('Y-m-d');
        $returnDate = Carbon::now()->addMonth()->addWeek()->format('Y-m-d');

        $responsePage3 = $this->get("/trips/search?from=YUL&to=YVR&departure_date={$futureDate}&return_date={$returnDate}&page=3");

        $tripsPage3 = $responsePage3->viewData('trips');

        $this->assertEquals(15, $tripsPage3->total());

        $this->assertEquals(3, $tripsPage3->currentPage());

        $this->assertCount(5, $tripsPage3->items());
    }
}
