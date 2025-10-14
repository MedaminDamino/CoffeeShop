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
    public function index(Request $request)
    {
        // Check for expired reservations and auto-complete them
        $expiredReservations = Reservation::where('start_at', '<', now()->subHours(2))
            ->whereIn('res_status', ['pending', 'confirmed'])
            ->get();

        foreach ($expiredReservations as $reservation) {
            $reservation->update(['res_status' => 'completed']);
            $reservation->table->update(['status' => 'available']);
        }

        $query = Reservation::with(['user','table.branch']);

        // Handle sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortDirection = $request->get('sort_direction', 'asc');

            // Map frontend column keys to database columns
            $columnMapping = [
                'id' => 'id',
                'userId' => 'user_id',
                'tableId' => 'table_id',
                'startAt' => 'start_at',
                'status' => 'res_status',
            ];

            if (array_key_exists($sortBy, $columnMapping)) {
                $query->orderBy($columnMapping[$sortBy], $sortDirection);
            } else {
                $query->orderBy('id', 'asc'); // Default fallback
            }
        } else {
            $query->orderBy('id', 'asc'); // Default sort
        }

        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            return $query->paginate($perPage, ['*'], 'page', $page);
        }

        return $query->get();
    }

    /**
     * @OA\Post(path="/api/reservations", summary="Create reservation", tags={"Reservations"}, @OA\Response(response=201, description="Reservation created"))
     */
    public function store(Request $request)
{

    try {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'required|exists:users,id',
            'start_at' => 'required|date',
            'res_status' => 'in:pending,confirmed,canceled,completed',
            'res_notes' => 'nullable|string',
        ]);


        $start = Carbon::parse($validated['start_at']);
        $end = $start->copy()->addHours(2); // fixed variable and added copy()


        // Check if date is in the past
        if ($start->isPast()) {
            return response()->json(['error' => 'Reservation date and time must be in the future. Please select a valid future date and time.'], 409);
        }

        $validated['res_status'] = $validated['res_status'] ?? 'pending';
        $validated['end_at'] = $end; // include end time

        $date = $start->toDateString();

        // Check if user already has a reservation on this date
        $existsUser = Reservation::where('user_id', $validated['user_id'])
            ->whereDate('start_at', $date)
            ->exists();

        if ($existsUser) {
            return response()->json(['error' => 'You already have a reservation on this date'], 409);
        }

        // Check if table is already reserved during the same period (overlap check)
        // Since end_at was dropped, we calculate end_at as start_at + 2 hours for existing reservations
        $overlap = Reservation::where('table_id', $validated['table_id'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_at', [$start, $end])
                      ->orWhereRaw("start_at + INTERVAL '2 hours' BETWEEN ? AND ?", [$start, $end])
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_at', '<=', $start)
                            ->whereRaw("start_at + INTERVAL '2 hours' >= ?", [$end]);
                      });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['error' => 'Table is already reserved during this time'], 409);
        }


        // Create reservation
        $reservation = Reservation::create($validated);


        // Only update table status to "reserved" if reservation is confirmed
        if ($validated['res_status'] === 'confirmed') {
            $table = Table::find($validated['table_id']);
            $table->update(['status' => 'reserved']);
        }

        return response()->json([
            'message' => 'Reservation created successfully',
            'reservation' => $reservation
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Reservation creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        throw $e;
    }
}


    /**
     * @OA\Put(path="/api/reservations/{id}", summary="Update reservation", tags={"Reservations"}, @OA\Response(response=200, description="Reservation updated"))
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'table_id' => 'sometimes|exists:tables,id',
            'user_id' => 'sometimes|exists:users,id',
            'start_at' => 'sometimes|date',
            'res_status' => 'sometimes|in:pending,confirmed,canceled,completed',
            'res_notes' => 'nullable|string',
        ]);

        // If table_id is being changed, update table statuses
        if (isset($validated['table_id']) && $validated['table_id'] !== $reservation->table_id) {
            // Set old table back to available
            $oldTable = $reservation->table;
            if ($oldTable) {
                $oldTable->update(['status' => 'available']);
            }

            // Set new table to reserved
            $newTable = Table::find($validated['table_id']);
            if ($newTable) {
                $newTable->update(['status' => 'reserved']);
            }
        }

        // If status is being changed to completed or canceled, set table back to available
        if (isset($validated['res_status']) && in_array($validated['res_status'], ['completed', 'canceled'])) {
            $table = $reservation->table;
            if ($table) {
                $table->update(['status' => 'available']);
            }
        }

        // If status is being changed to confirmed, set table to reserved
        if (isset($validated['res_status']) && $validated['res_status'] === 'confirmed') {
            $table = $reservation->table;
            if ($table) {
                $table->update(['status' => 'reserved']);
            }
        }

        $reservation->update($validated);
        return $reservation->load(['user','table']);
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
    // Get the associated table
    $table = $reservation->table;

    // Delete the reservation
    $reservation->delete();

    // Set table back to available (only if it exists)
    if ($table) {
        $table->update(['status' => 'available']);
    }

    return response()->json([
        'message' => 'Reservation deleted successfully, table is now available again'
    ], 200);
}

}
