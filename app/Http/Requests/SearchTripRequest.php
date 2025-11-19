<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SearchTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minDepartureDate = Carbon::now()->addDay()->format('Y-m-d');

        return [
            'from' => 'required|string|max:3|exists:airports,code',
            'to' => 'required|string|max:3|exists:airports,code',

            'departure_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . $minDepartureDate
            ],

            'return_date' => 'nullable|date_format:Y-m-d|after_or_equal:departure_date',
            'airline_code' => 'nullable|string|max:2',
            'sort_by' => 'nullable|in:price,duration',
            'sort_dir' => 'nullable|in:asc,desc',
        ];
    }

    public function messages(): array
    {
        return [
            'from.required' => 'The departure airport code is required.',
            'to.required' => 'The arrival airport code is required.',
            'departure_date.after_or_equal' => 'The departure date must be after or equal to today\'s date plus one day.',
        ];
    }

    public function attributes(): array
    {
        return [
            'from' => 'Departure Airport',
            'to' => 'Arrival Airport',
            'departure_date' => 'Departure Date',
            'return_date' => 'Return Date',
        ];
    }
}
