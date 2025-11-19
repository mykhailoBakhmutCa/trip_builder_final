<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchTripRequest;
use App\Services\TripSearcher;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TripController extends Controller
{
    protected TripSearcher $searcher;

    public function __construct(TripSearcher $searcher)
    {
        $this->searcher = $searcher;
    }

    public function index(): Factory|View
    {
        return view('trip-search', ['trips' => null]);
    }

    public function search(SearchTripRequest $request): Factory|View|RedirectResponse
    {
        $trips = $this->searcher->searchAndPaginate($request);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        if ($currentPage > $trips->lastPage() && $trips->lastPage() > 0) {
            $query = $request->query();
            $query['page'] = $trips->lastPage();

            return redirect()->route('trip.search', $query);
        }

        return view('trip-search', ['trips' => $trips]);
    }
}
