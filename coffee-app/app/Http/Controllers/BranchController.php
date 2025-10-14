<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Branch",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="branch_name", type="string", example="Main Branch"),
 *     @OA\Property(property="branch_address", type="string", example="123 Main St"),
 *     @OA\Property(property="branch_phone", type="string", example="+1234567890"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class BranchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/branches",
     *     summary="Get all branches",
     *     tags={"Branches"},
     *     @OA\Response(
     *         response=200,
     *         description="List of branches",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Branch")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            return Branch::with('tables')->orderBy('id')->paginate($perPage, ['*'], 'page', $page);
        }

        return Branch::with('tables')->orderBy('id')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/branches",
     *     summary="Create a new branch",
     *     tags={"Branches"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"branch_name"},
     *             @OA\Property(property="branch_name", type="string", example="New Branch"),
     *             @OA\Property(property="branch_address", type="string", example="456 Elm St"),
     *             @OA\Property(property="branch_phone", type="string", example="+0987654321")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Branch created",
     *         @OA\JsonContent(ref="#/components/schemas/Branch")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'branch_address' => 'nullable|string',
            'branch_phone' => 'nullable|string'
        ]);

        $phone = $request->input('branch_phone');

        if ($phone && strlen($phone) != 8) {
            return response()->json(['error' => 'The phone number should be exactly 8 digits'], 409);
        }

        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }


    /**
     * @OA\Get(
     *     path="/api/branches/{id}",
     *     summary="Get a specific branch",
     *     tags={"Branches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Branch details",
     *         @OA\JsonContent(ref="#/components/schemas/Branch")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch not found"
     *     )
     * )
     */
    public function show(Branch $branch)
    {
        return $branch->load('tables');
    }

    /**
     * @OA\Put(
     *     path="/api/branches/{id}",
     *     summary="Update a branch",
     *     tags={"Branches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_name", type="string", example="Updated Branch"),
     *             @OA\Property(property="branch_address", type="string", example="789 Oak St"),
     *             @OA\Property(property="branch_phone", type="string", example="+1122334455")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Branch updated",
     *         @OA\JsonContent(ref="#/components/schemas/Branch")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch not found"
     *     )
     * )
     */
    public function update(Request $request, Branch $branch)
    {
        $phone = $request->input('branch_phone');

        if ($phone && strlen($phone) != 8) {
            return response()->json(['error' => 'The phone number should be exactly 8 digits'], 409);
        }
        
        $branch->update($request->all());
        return $branch;
    }

    /**
     * @OA\Delete(
     *     path="/api/branches/{id}",
     *     summary="Delete a branch",
     *     tags={"Branches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Branch deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch not found"
     *     )
     * )
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return response()->noContent();
    }
}