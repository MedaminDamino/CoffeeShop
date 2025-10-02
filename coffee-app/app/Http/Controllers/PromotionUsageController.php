<?php

namespace App\Http\Controllers;

use App\Models\PromotionUsage;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="PromotionUsage",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="promotion_id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class PromotionUsageController extends Controller
{
    /**
     * @OA\Post(path="/api/promotion-usages", summary="Create promotion usage", tags={"Promotion Usages"}, @OA\Response(response=201, description="Promotion usage created"))
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
        ]);

        return PromotionUsage::create($validated);
    }
}
