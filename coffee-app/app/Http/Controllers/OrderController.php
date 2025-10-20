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
 *     @OA\Property(property="products", type="array", @OA\Items(type="object")),
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
    public function index(Request $request)
    {
        $query = Order::with(['user']);

        // Handle sorting
        $sortBy = $request->get('sort_by');
        $sortDirection = $request->get('sort_direction', 'asc');

        if ($sortBy) {
            // Map frontend column names to database column names
            $columnMapping = [
                'id' => 'id',
                'userId' => 'user_id',
                'totalAmount' => 'ord_total_amount',
                'status' => 'ord_status',
                'created_at' => 'created_at',
            ];

            if (array_key_exists($sortBy, $columnMapping)) {
                $query->orderBy($columnMapping[$sortBy], $sortDirection);
            } else {
                // Default sort by id if invalid column
                $query->orderBy('id', 'asc');
            }
        } else {
            // Default sort by id ascending
            $query->orderBy('id', 'asc');
        }

        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            return $query->paginate($perPage, ['*'], 'page', $page);
        }

        return $query->get();
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
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'meta' => 'nullable|array',
            'meta.promotion_id' => 'nullable|exists:promotions,id',
        ]);

        $validated['ord_status'] = $validated['ord_status'] ?? 'pending';

        $order = Order::create($validated);

        // If promotion was applied, create promotion usage record
        if (isset($validated['meta']['promotion_id'])) {
            \App\Models\PromotionUsage::create([
                'promotion_id' => $validated['meta']['promotion_id'],
                'order_id' => $order->id,
                'user_id' => $validated['user_id'],
            ]);
        }

        return $order->load(['user']);
    }

    /**
     * @OA\Get(path="/api/orders/{id}", summary="Get order", tags={"Orders"}, @OA\Response(response=200, description="Order details"))
     */
    public function show(Order $order)
    {
        return $order->load(['user']);
    }

    /**
     * @OA\Put(path="/api/orders/{id}", summary="Update order", tags={"Orders"}, @OA\Response(response=200, description="Order updated"))
     */
    public function update(Request $request, Order $order)
    {
        // Check authorization - only admin or super_admin can update orders
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            \Log::warning('Unauthorized order update attempt', [
                'order_id' => $order->id,
                'user_role' => $user ? $user->role : null,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate input data with proper field mapping
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'ord_total_amount' => 'required|numeric|min:0',
            'ord_status' => 'in:pending,paid,cancelled',
            'ord_payment_method' => 'in:cash,online',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'meta' => 'nullable|array',
        ]);

        // Check for invalid status transitions
        if ($order->ord_status === 'paid' && in_array($validated['ord_status'], ['pending'])) {
            \Log::warning('Invalid status transition attempted', [
                'order_id' => $order->id,
                'from' => $order->ord_status,
                'to' => $validated['ord_status'],
            ]);
            return response()->json(['error' => 'Cannot change status from paid to pending'], 422);
        }

        if ($order->ord_status === 'cancelled') {
            \Log::warning('Attempt to update cancelled order', ['order_id' => $order->id]);
            return response()->json(['error' => 'Cannot update a cancelled order'], 422);
        }

        // Check for concurrency conflicts using updated_at timestamp
        if ($request->has('updated_at')) {
            $requestUpdatedAt = \Carbon\Carbon::parse($request->input('updated_at'));
            $currentUpdatedAt = $order->updated_at;

            if ($requestUpdatedAt->ne($currentUpdatedAt)) {
                \Log::warning('Concurrency conflict detected', [
                    'order_id' => $order->id,
                    'request_updated_at' => $requestUpdatedAt->toISOString(),
                    'current_updated_at' => $currentUpdatedAt->toISOString(),
                ]);
                return response()->json(['error' => 'Order has been modified by another user. Please refresh and try again.'], 409);
            }
        }

        // Use database transaction for atomicity
        \DB::beginTransaction();
        try {
            $order->update($validated);

            \DB::commit();
            return $order->load(['user', 'branch']);
        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollBack();
            \Log::error('Database error during order update', [
                'order_id' => $order->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ]);

            if ($e->getCode() == 23000) { // Integrity constraint violation
                return response()->json(['error' => 'Data integrity violation. Please check related records.'], 422);
            }
            return response()->json(['error' => 'Database error occurred'], 500);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Order update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to update order'], 500);
        }
    }


    /**
     * Add item to user's pending order or create new order if none exists
     * @OA\Post(path="/api/orders/add-item", summary="Add item to order", tags={"Orders"}, @OA\Response(response=201, description="Item added to order"))
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = $validated['user_id'];
        $branchId = $validated['branch_id'];
        $productId = $validated['product_id'];
        $quantity = $validated['quantity'];

        // Get product to calculate price
        $product = \App\Models\Product::findOrFail($productId);
        if (!$product->prod_is_active) {
            return response()->json(['error' => 'Product is not available'], 422);
        }

        $itemPrice = $product->prod_price * $quantity;

        \DB::beginTransaction();
        try {
            // Find or create pending order for user
            $order = Order::where('user_id', $userId)
                          ->where('ord_status', 'pending')
                          ->first();

            if (!$order) {
                $order = Order::create([
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'ord_total_amount' => 0,
                    'ord_status' => 'pending',
                    'ord_payment_method' => null,
                    'products' => [],
                    'meta' => ['source' => 'cart_addition'],
                ]);
            }

            // Get current products
            $currentProducts = $order->products ?? [];

            // Check if product already exists
            $productExists = false;
            foreach ($currentProducts as &$prod) {
                if ($prod['product_id'] == $productId) {
                    $prod['quantity'] += $quantity;
                    $prod['price'] = $prod['quantity'] * $product->prod_price;
                    $productExists = true;
                    break;
                }
            }

            if (!$productExists) {
                $currentProducts[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $itemPrice,
                ];
            }

            // Recalculate total amount
            $totalAmount = array_sum(array_column($currentProducts, 'price'));
            $order->update([
                'products' => $currentProducts,
                'ord_total_amount' => $totalAmount
            ]);

            \DB::commit();

            // Log the action
            \Log::info('Item added to order', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'item_price' => $itemPrice,
                'total_amount' => $totalAmount,
                'timestamp' => now(),
            ]);

            return response()->json([
                'message' => 'Item added to order successfully',
                'order' => $order->load(['user']),
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to add item to order', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to add item to order'], 500);
        }
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
