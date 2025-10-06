<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="table_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01 14:00:00"),
 *     @OA\Property(property="res_status", type="string", enum={"pending", "confirmed", "canceled", "completed"}, example="pending"),
 *     @OA\Property(property="res_notes", type="string", example="Special occasion"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class ReservationController extends Controller
{
    /**
     * @OA\Get(path="/api/reservations", summary="Get all reservations", tags={"Reservations"}, @OA\Response(response=200, description="List of reservations"))
     */
    public function index()
    {
        return Reservation::with(['user','table.branch'])->get();
    }

    /**
     * @OA\Post(path="/api/reservations", summary="Create reservation", tags={"Reservations"}, @OA\Response(response=201, description="Reservation created"))
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'required|exists:users,id',
            'start_at' => 'required|date',
            'res_status' => 'in:pending,confirmed,canceled,completed',
            'res_notes' => 'nullable|string',
        ]);

        $start = Carbon::parse($validated['start_at']);

        if ($start->isPast()) {
            return response()->json(['error' => 'Reservation date must be in the future'], 409);
        }

        $validated['res_status'] = $validated['res_status'] ?? 'pending';

        $date = $start->toDateString();

        // Check if user already has reservation on this date
        $existsUser = Reservation::where('user_id', $validated['user_id'])
            ->whereDate('start_at', $date)
            ->exists();

        if ($existsUser) {
            return response()->json(['error' => 'You already have a reservation on this date'], 409);
        }

        // Check table capacity
        $table = Table::find($validated['table_id']);
        $count = Reservation::where('table_id', $validated['table_id'])
            ->whereDate('start_at', $date)
            ->count();

        if ($count >= $table->capacity) {
            return response()->json(['error' => 'Table is fully booked on this date'], 409);
        }

        // Check if table is already reserved at this time
        $exists = Reservation::where('table_id', $validated['table_id'])
            ->where('start_at', $validated['start_at'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Table already reserved during this time'], 409);
        }

        return Reservation::create($validated);
    }

    /**
     * @OA\Get(path="/api/reservations/{id}", summary="Get reservation", tags={"Reservations"}, @OA\Response(response=200, description="Reservation details"))
     */
    public function show(Reservation $reservation)
    {
        return $reservation->load(['user','table']);
    }

    /**
     * @OA\Delete(path="/api/reservations/{id}", summary="Delete reservation", tags={"Reservations"}, @OA\Response(response=204, description="Reservation deleted"))
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->noContent();
    }
}
