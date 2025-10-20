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
    public function index(Request $request)
    {
        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            return Promotion::orderBy('id')->paginate($perPage, ['*'], 'page', $page);
        }

        return Promotion::orderBy('id')->get();
    }

    /**
     * @OA\Post(path="/api/promotions", summary="Create promotion", tags={"Promotions"}, @OA\Response(response=201, description="Promotion created"))
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_promo' => 'required|string|unique:promotions,code_promo|max:50',
            'promo_description' => 'nullable|string|max:255',
            'promo_discount_type' => 'required|in:percent,fixed',
            'promo_discount_value' => 'required|numeric|min:0',
            'promo_start_date' => 'nullable|date|before_or_equal:promo_end_date|after_or_equal:today',
            'promo_end_date' => 'nullable|date|after_or_equal:promo_start_date',
            'promo_usage_limit' => 'nullable|integer|min:0',
            'promo_is_active' => 'boolean'
        ]);

        // Additional business logic validation
        if ($validated['promo_discount_type'] === 'percent' && $validated['promo_discount_value'] > 100) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'promo_discount_value' => ['Percentage discount cannot exceed 100%']
                ]
            ], 422);
        }

        if ($validated['promo_discount_type'] === 'fixed' && $validated['promo_discount_value'] <= 0) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'promo_discount_value' => ['Fixed discount must be greater than 0']
                ]
            ], 422);
        }

        // Check for date conflicts with existing active promotions
        if ($validated['promo_start_date'] && $validated['promo_end_date']) {
            $conflictingPromotion = Promotion::where('code_promo', '!=', $validated['code_promo'])
                ->where('promo_is_active', true)
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('promo_start_date', [$validated['promo_start_date'], $validated['promo_end_date']])
                          ->orWhereBetween('promo_end_date', [$validated['promo_start_date'], $validated['promo_end_date']])
                          ->orWhere(function ($q) use ($validated) {
                              $q->where('promo_start_date', '<=', $validated['promo_start_date'])
                                ->where('promo_end_date', '>=', $validated['promo_end_date']);
                          });
                })
                ->first();

            if ($conflictingPromotion) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'promo_start_date' => ['Promotion dates conflict with existing active promotion: ' . $conflictingPromotion->code_promo]
                    ]
                ], 422);
            }
        }

        return Promotion::create($validated);
    }

    /**
     * @OA\Put(path="/api/promotions/{id}", summary="Update promotion", tags={"Promotions"}, @OA\Response(response=200, description="Promotion updated"))
     */
    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'code_promo' => 'required|string|max:50|unique:promotions,code_promo,' . $promotion->id,
            'promo_description' => 'nullable|string|max:255',
            'promo_discount_type' => 'required|in:percent,fixed',
            'promo_discount_value' => 'required|numeric|min:0',
            'promo_start_date' => 'nullable|date|before_or_equal:promo_end_date|after_or_equal:today',
            'promo_end_date' => 'nullable|date|after_or_equal:promo_start_date',
            'promo_usage_limit' => 'nullable|integer|min:0',
            'promo_is_active' => 'boolean'
        ]);

        // Additional business logic validation
        if ($validated['promo_discount_type'] === 'percent' && $validated['promo_discount_value'] > 100) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'promo_discount_value' => ['Percentage discount cannot exceed 100%']
                ]
            ], 422);
        }

        if ($validated['promo_discount_type'] === 'fixed' && $validated['promo_discount_value'] <= 0) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'promo_discount_value' => ['Fixed discount must be greater than 0']
                ]
            ], 422);
        }

        // Check for date conflicts with other active promotions (excluding current one)
        if ($validated['promo_start_date'] && $validated['promo_end_date']) {
            $conflictingPromotion = Promotion::where('id', '!=', $promotion->id)
                ->where('promo_is_active', true)
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('promo_start_date', [$validated['promo_start_date'], $validated['promo_end_date']])
                          ->orWhereBetween('promo_end_date', [$validated['promo_start_date'], $validated['promo_end_date']])
                          ->orWhere(function ($q) use ($validated) {
                              $q->where('promo_start_date', '<=', $validated['promo_start_date'])
                                ->where('promo_end_date', '>=', $validated['promo_end_date']);
                          });
                })
                ->first();

            if ($conflictingPromotion) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'promo_start_date' => ['Promotion dates conflict with existing active promotion: ' . $conflictingPromotion->code_promo]
                    ]
                ], 422);
            }
        }

        $promotion->update($validated);
        return $promotion;
    }

    /**
     * @OA\Post(path="/api/promotions/validate", summary="Validate promo code", tags={"Promotions"}, @OA\Response(response=200, description="Promotion valid"), @OA\Response(response=422, description="Invalid code"))
     */
    public function validateCode(Request $request)
    {
        $validated = $request->validate([
            'code_promo' => 'required|string|max:50'
        ]);


        $promotion = Promotion::where('code_promo', $validated['code_promo'])->first();


        if (!$promotion) {
            return response()->json([
                'message' => 'Invalid promo code'
            ], 422);
        }

        if (!$promotion->promo_is_active) {
            return response()->json([
                'message' => 'Promo code is not active'
            ], 422);
        }

        $now = now();
        if ($promotion->promo_start_date && $now->lt($promotion->promo_start_date)) {
            return response()->json([
                'message' => 'Promo code is not yet valid'
            ], 422);
        }

        if ($promotion->promo_end_date && $now->gt($promotion->promo_end_date)) {
            return response()->json([
                'message' => 'Promo code has expired'
            ], 422);
        }

        $usageLimit = $promotion->promo_usage_limit ?? 1;
        $usageCount = $promotion->usages()->count();
        if ($usageCount >= $usageLimit) {
            return response()->json([
                'message' => 'Promo code usage limit exceeded'
            ], 422);
        }

        $response = [
            'id' => $promotion->id,
            'code' => $promotion->code_promo,
            'description' => $promotion->promo_description,
            'discountType' => $promotion->promo_discount_type,
            'discountValue' => $promotion->promo_discount_value,
            'startDate' => $promotion->promo_start_date,
            'endDate' => $promotion->promo_end_date,
            'usageLimit' => $usageLimit,
            'isActive' => $promotion->promo_is_active
        ];


        return response()->json($response);
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
