@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-dark text-white">✈️ Flight Search</div>

                    <div class="card-body">

                        <form action="/trips/search" method="GET" class="row g-3 mb-4">

                            @php
                                $airports = App\Models\Airport::all(['code', 'name', 'city']);
                            @endphp

                            <div class="col-md-3">
                                <label for="from" class="form-label">From</label>
                                <select id="from" name="from" class="form-select" required>
                                    <option value="">Select Departure Airport</option>
                                    @foreach ($airports as $airport)
                                        <option value="{{ $airport->code }}" {{ request('from') === $airport->code ? 'selected' : '' }}>
                                            {{ $airport->city }} ({{ $airport->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="to" class="form-label">To</label>
                                <select id="to" name="to" class="form-select" required>
                                    <option value="">Select Arrival Airport</option>
                                    @foreach ($airports as $airport)
                                        <option value="{{ $airport->code }}" {{ request('to') === $airport->code ? 'selected' : '' }}>
                                            {{ $airport->city }} ({{ $airport->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="departure_date" class="form-label">Departure Date</label>
                                <input type="date" id="departure_date" name="departure_date" class="form-control"
                                       value="{{ request('departure_date') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label for="return_date" class="form-label">Return Date (Optional)</label>
                                <input type="date" id="return_date" name="return_date" class="form-control"
                                       value="{{ request('return_date') }}">
                            </div>

                            <div class="col-md-3">
                                <label for="airline_code" class="form-label">Airline Code (e.g., AC, UA)</label>
                                <input type="text" id="airline_code" name="airline_code" class="form-control"
                                       value="{{ request('airline_code') }}" maxlength="2">
                            </div>

                            <div class="col-md-3">
                                <label for="sort_by" class="form-label">Sort By</label>
                                <select id="sort_by" name="sort_by" class="form-select">
                                    <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>Price</option>
                                    <option value="duration" {{ request('sort_by') === 'duration' ? 'selected' : '' }}>Duration</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="sort_dir" class="form-label">Direction</label>
                                <select id="sort_dir" name="sort_dir" class="form-select">
                                    <option value="asc" {{ request('sort_dir') === 'asc' ? 'selected' : '' }}>Ascending</option>
                                    <option value="desc" {{ request('sort_dir') === 'desc' ? 'selected' : '' }}>Descending</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">Search Trips</button>
                                <a href="{{ url('/') }}" class="btn btn-secondary">Clear Search</a>
                            </div>
                        </form>

                        @if (isset($trips) && $trips->count() > 0)
                            <hr>
                            <h4 class="mb-3">{{ $trips->total() }} Trips Found</h4>

                            @foreach ($trips->items() as $trip)
                                @php
                                    $isRoundTrip = $trip['type'] === 'round-trip';
                                    $segments = collect($trip['segments']);
                                @endphp

                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header {{ $isRoundTrip ? 'bg-success' : 'bg-primary' }} text-white">
                                        <h5 class="mb-0">{{ strtoupper($trip['type']) }} Trip ({{ $segments->count() }} Segments)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                @foreach ($segments as $segment)
                                                    <div class="card p-3 mb-2 bg-light border">
                                                        <h6>
                                                            {{ $segment['departure']['city'] }} ({{ $segment['departure']['airport_code'] }})
                                                            &rarr;
                                                            {{ $segment['arrival']['city'] }} ({{ $segment['arrival']['airport_code'] }})
                                                        </h6>
                                                        <p class="mb-1 small">
                                                            Flight: {{ $segment['flight_number'] }} ({{ $segment['airline'] }})
                                                            <br>
                                                            Departs: **{{ $segment['departure']['date_local'] }}** @ {{ $segment['departure']['time_local'] }} |
                                                            Arrives: **{{ $segment['arrival']['date_local'] }}** @ {{ $segment['arrival']['time_local'] }} |
                                                            Duration: {{ $segment['duration'] }}
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <h4 class="text-success">${{ number_format($trip['total_price'], 2) }}</h4>
                                                <p class="text-muted small">Total Trip Time: {{ $trip['info']['total_duration'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-4">
                                {{ $trips->links('pagination::bootstrap-5') }}
                            </div>

                        @elseif (isset($trips))
                            <p class="alert alert-warning">No trips found matching your criteria.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
