<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleData = [
            "airlines" => [
                ["code" => "AC", "name" => "Air Canada"],
                ["code" => "UA", "name" => "United Airlines"],
                ["code" => "FR", "name" => "Ryanair"]
            ],
            "airports" => [
                [
                    "code" => "YUL", "city_code" => "YMQ", "name" => "Pierre Elliott Trudeau International", "city" => "Montreal", "country_code" => "CA", "region_code" => "QC", "latitude" => 45.457714, "longitude" => -73.749908, "timezone" => "America/Montreal"
                ],
                [
                    "code" => "YVR", "city_code" => "YVR", "name" => "Vancouver International", "city" => "Vancouver", "country_code" => "CA", "region_code" => "BC", "latitude" => 49.194698, "longitude" => -123.179192, "timezone" => "America/Vancouver"
                ],
                [
                    "code" => "LHR", "city_code" => "LON", "name" => "London Heathrow", "city" => "London", "country_code" => "GB", "region_code" => "ENG", "latitude" => 51.470020, "longitude" => -0.454295, "timezone" => "Europe/London"
                ],
                [
                    "code" => "ORD", "city_code" => "CHI", "name" => "O'Hare International", "city" => "Chicago", "country_code" => "US", "region_code" => "IL", "latitude" => 41.974162, "longitude" => -87.907327, "timezone" => "America/Chicago"
                ]
            ],
            "flights" => [
                ["airline" => "AC", "number" => "309", "departure_airport" => "YUL", "departure_time" => "06:00", "arrival_airport" => "YVR", "arrival_time" => "08:30", "price" => "200.00"],
                ["airline" => "AC", "number" => "301", "departure_airport" => "YUL", "departure_time" => "07:35", "arrival_airport" => "YVR", "arrival_time" => "10:05", "price" => "273.23"],
                ["airline" => "AC", "number" => "303", "departure_airport" => "YUL", "departure_time" => "14:00", "arrival_airport" => "YVR", "arrival_time" => "17:30", "price" => "350.00"],
                ["airline" => "AC", "number" => "305", "departure_airport" => "YUL", "departure_time" => "18:00", "arrival_airport" => "YVR", "arrival_time" => "20:30", "price" => "400.00"],
                ["airline" => "AC", "number" => "307", "departure_airport" => "YUL", "departure_time" => "22:00", "arrival_airport" => "YVR", "arrival_time" => "00:30", "price" => "450.00"],

                ["airline" => "AC", "number" => "302", "departure_airport" => "YVR", "departure_time" => "13:00", "arrival_airport" => "YUL", "arrival_time" => "21:00", "price" => "200.00"],
                ["airline" => "AC", "number" => "304", "departure_airport" => "YVR", "departure_time" => "20:00", "arrival_airport" => "YUL", "arrival_time" => "04:00", "price" => "300.00"],

                ["airline" => "UA", "number" => "908", "departure_airport" => "ORD", "departure_time" => "18:00", "arrival_airport" => "LHR", "arrival_time" => "07:40", "price" => "650.99"],
                ["airline" => "UA", "number" => "909", "departure_airport" => "LHR", "departure_time" => "12:00", "arrival_airport" => "ORD", "arrival_time" => "14:40", "price" => "680.99"],
                ["airline" => "FR", "number" => "101", "departure_airport" => "LHR", "departure_time" => "09:00", "arrival_airport" => "YUL", "arrival_time" => "11:45", "price" => "550.00"],
                ["airline" => "AC", "number" => "105", "departure_airport" => "YUL", "departure_time" => "15:00", "arrival_airport" => "ORD", "arrival_time" => "16:30", "price" => "190.50"],

                ["airline" => "FR", "number" => "102", "departure_airport" => "ORD", "departure_time" => "10:00", "arrival_airport" => "YUL", "arrival_time" => "13:30", "price" => "150.00"],
                ["airline" => "UA", "number" => "101", "departure_airport" => "ORD", "departure_time" => "09:00", "arrival_airport" => "YUL", "arrival_time" => "12:30", "price" => "160.00"],
            ]
        ];

        Schema::disableForeignKeyConstraints();

        DB::table('flights')->truncate();
        DB::table('airports')->truncate();
        DB::table('airlines')->truncate();

        $airlines = collect($sampleData['airlines'])->map(fn ($item) => ['code' => $item['code'], 'name' => $item['name']])->toArray();
        DB::table('airlines')->insert($airlines);

        $airports = collect($sampleData['airports'])->map(fn ($item) => [
            'code' => $item['code'], 'city_code' => $item['city_code'], 'name' => $item['name'],
            'city' => $item['city'], 'country_code' => $item['country_code'], 'region_code' => $item['region_code'],
            'latitude' => $item['latitude'], 'longitude' => $item['longitude'], 'timezone' => $item['timezone'],
        ])->toArray();
        DB::table('airports')->insert($airports);

        $flights = collect($sampleData['flights'])->map(fn ($item) => [
            'airline_code' => $item['airline'], 'number' => $item['number'],
            'departure_airport_code' => $item['departure_airport'], 'departure_time' => $item['departure_time'],
            'arrival_airport_code' => $item['arrival_airport'], 'arrival_time' => $item['arrival_time'],
            'price' => $item['price'],
        ])->toArray();
        DB::table('flights')->insert($flights);

        Schema::enableForeignKeyConstraints();
    }
}
