<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Promotion",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code_promo", type="string", example="DISCOUNT10"),
 *     @OA\Property(property="promo_description", type="string", example="10% discount"),
 *     @OA\Property(property="promo_discount_type", type="string", enum={"percent", "fixed"}, example="percent"),
 *     @OA\Property(property="promo_discount_value", type="number", format="float", example=10.0),
 *     @OA\Property(property="promo_start_date", type="string", format="date", example="2023-10-01"),
 *     @OA\Property(property="promo_end_date", type="string", format="date", example="2023-10-31"),
 *     @OA\Property(property="promo_usage_limit", type="integer", example=100),
 *     @OA\Property(property="promo_is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class PromotionController extends Controller
{
    /**
     * @OA\Get(path="/api/promotions", summary="Get all promotions", tags={"Promotions"}, @OA\Response(response=200, description="List of promotions"))
     */
    public function index()
    {
        return Promotion::all();
    }

    /**
     * @OA\Post(path="/api/promotions", summary="Create promotion", tags={"Promotions"}, @OA\Response(response=201, description="Promotion created"))
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_promo' => 'required|string|unique:promotions,code_promo',
            'promo_description' => 'nullable|string',
            'promo_discount_type' => 'required|in:percent,fixed',
            'promo_discount_value' => 'required|numeric|min:0',
            'promo_start_date' => 'nullable|date',
            'promo_end_date' => 'nullable|date',
            'promo_usage_limit' => 'nullable|integer|min:0',
            'promo_is_active' => 'boolean'
        ]);

        return Promotion::create($validated);
    }

    /**
     * @OA\Put(path="/api/promotions/{id}", summary="Update promotion", tags={"Promotions"}, @OA\Response(response=200, description="Promotion updated"))
     */
    public function update(Request $request, Promotion $promotion)
    {
        $promotion->update($request->all());
        return $promotion;
    }

    /**
     * @OA\Delete(path="/api/promotions/{id}", summary="Delete promotion", tags={"Promotions"}, @OA\Response(response=204, description="Promotion deleted"))
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->noContent();
    }
}
