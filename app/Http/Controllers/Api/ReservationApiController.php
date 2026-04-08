<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    /**
     * Public JSON for calendar widgets — returns busy ranges for a facility.
     */
    public function availability(Request $request, Facility $facility): JsonResponse
    {
        $from = $request->query('from', now()->subDays(7)->toDateString());
        $to = $request->query('to', now()->addDays(60)->toDateString());

        $busy = Reservation::query()
            ->where('facility_id', $facility->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('starts_at', '<=', $to)
            ->where('ends_at', '>=', $from)
            ->orderBy('starts_at')
            ->get(['id', 'starts_at', 'ends_at', 'status']);

        return response()->json([
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
            ],
            'busy' => $busy,
        ]);
    }
}
