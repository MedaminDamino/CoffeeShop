<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="branch_id", type="integer", example=1),
 *     @OA\Property(property="ord_total_amount", type="number", format="float", example=25.50),
 *     @OA\Property(property="ord_status", type="string", enum={"pending", "paid", "cancelled"}, example="pending"),
 *     @OA\Property(property="ord_payment_method", type="string", enum={"cash", "online"}, example="cash"),
 *     @OA\Property(property="meta", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class OrderController extends Controller
{
    /**
     * @OA\Get(path="/api/orders", summary="Get all orders", tags={"Orders"}, @OA\Response(response=200, description="List of orders"))
     */
    public function index()
    {
        return Order::with(['items.product','user'])->get();
    }

    /**
     * @OA\Post(path="/api/orders", summary="Create order", tags={"Orders"}, @OA\Response(response=201, description="Order created"))
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'ord_total_amount' => 'required|numeric|min:0',
            'ord_status' => 'in:pending,paid,cancelled',
            'ord_payment_method' => 'in:cash,online',
            'meta' => 'nullable|array',
        ]);

        $validated['ord_status'] = $validated['ord_status'] ?? 'pending';

        return Order::create($validated);
    }

    /**
     * @OA\Get(path="/api/orders/{id}", summary="Get order", tags={"Orders"}, @OA\Response(response=200, description="Order details"))
     */
    public function show(Order $order)
    {
        return $order->load(['items.product','user']);
    }

    /**
     * @OA\Put(path="/api/orders/{id}", summary="Update order", tags={"Orders"}, @OA\Response(response=200, description="Order updated"))
     */
    public function update(Request $request, Order $order)
    {
        $order->update($request->all());
        return $order;
    }

    /**
     * @OA\Delete(path="/api/orders/{id}", summary="Delete order", tags={"Orders"}, @OA\Response(response=204, description="Order deleted"))
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->noContent();
    }
}
