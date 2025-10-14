<?php
namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Table",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="branch_id", type="integer", example=1),
 *     @OA\Property(property="table_number", type="string", example="T1"),
 *     @OA\Property(property="capacity", type="integer", example=4),
 *     @OA\Property(property="status", type="string", enum={"available", "reserved", "out_of_service"}, example="available"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class TableController extends Controller
{
    /**
     * @OA\Get(path="/api/tables", summary="Get all tables", tags={"Tables"}, @OA\Response(response=200, description="List of tables"))
     */
    public function index(Request $request)
    {
        $query = Table::with('branch');

        // Handle sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortDirection = $request->get('sort_direction', 'asc');

            // Map frontend column keys to database columns
            $columnMapping = [
                'id' => 'id',
                'number' => 'table_number',
                'capacity' => 'capacity',
                'status' => 'status',
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
     * @OA\Post(path="/api/tables", summary="Create table", tags={"Tables"}, @OA\Response(response=201, description="Table created"))
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'table_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'status' => 'in:available,reserved,out_of_service',
        ]);

        return Table::create($validated);
    }

    /**
     * @OA\Get(path="/api/tables/{id}", summary="Get table", tags={"Tables"}, @OA\Response(response=200, description="Table details"))
     */
    public function show(Table $table)
    {
        return $table->load('branch');
    }

 /**
 * @OA\Put(
 *     path="/api/tables/{id}",
 *     summary="Update table",
 *     tags={"Tables"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the table to update",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"branch_id","table_number","capacity"},
 *             @OA\Property(property="branch_id", type="integer", example=1),
 *             @OA\Property(property="table_number", type="string", example="T12"),
 *             @OA\Property(property="capacity", type="integer", example=4),
 *             @OA\Property(property="status", type="string", enum={"available","reserved","out_of_service"}, example="available")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Table updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Table not found"
 *     )
 * )
 */


    public function update(Request $request, Table $table)
    {
        $table->update($request->all());
        return $table;
    }

    /**
     * @OA\Delete(path="/api/tables/{id}", summary="Delete table", tags={"Tables"}, @OA\Response(response=204, description="Table deleted"))
     */
    public function destroy(Table $table)
    {
        $table->delete();
        return response()->noContent();
    }
}
