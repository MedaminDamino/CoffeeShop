<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Coffee Shop API",
 *     version="1.0.0",
 *     description="API for managing coffee shop operations"
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="prod_name", type="string", example="Espresso"),
 *     @OA\Property(property="prod_price", type="number", format="float", example=2.50),
 *     @OA\Property(property="prod_description", type="string", example="Rich and bold espresso"),
 *     @OA\Property(property="prod_image_url", type="string", example="https://example.com/espresso.jpg"),
 *     @OA\Property(property="prod_is_active", type="boolean", example=true),
 *     @OA\Property(property="prod_meta", type="object"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && in_array($user->role, ['admin', 'super_admin']);
        $showAll = $request->get('show_all', false) || $isAdmin;

        $query = Product::with('category');

        // Handle sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortDirection = $request->get('sort_direction', 'asc');

            // Map frontend column keys to database columns
            $columnMapping = [
                'id' => 'id',
                'name' => 'prod_name',
                'price' => 'prod_price',
                'description' => 'prod_description',
                'isActive' => 'prod_is_active',
            ];

            if (array_key_exists($sortBy, $columnMapping)) {
                $query->orderBy($columnMapping[$sortBy], $sortDirection);
            } else {
                $query->orderBy('id', 'asc'); // Default fallback
            }
        } else {
            $query->orderBy('id', 'asc'); // Default sort
        }

        // Only filter by active status for non-admin users and when not explicitly requesting all
        if (!$showAll) {
            $query->where('prod_is_active', true);
        }

        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $products = $query->paginate($perPage, ['*'], 'page', $page);
            return $products;
        }

        $products = $query->get();
        return $products;
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "prod_name", "prod_price"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="prod_name", type="string", example="Latte"),
     *             @OA\Property(property="prod_price", type="number", format="float", example=4.50),
     *             @OA\Property(property="prod_description", type="string", example="Creamy latte"),
     *             @OA\Property(property="prod_image_url", type="string", example="https://example.com/latte.jpg"),
     *             @OA\Property(property="prod_is_active", type="boolean", example=true),
     *             @OA\Property(property="prod_meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
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
            'category_id' => 'required|exists:categories,id',
            'prod_name' => 'required|string|max:255',
            'prod_price' => 'required|numeric|min:0',
            'prod_description' => 'nullable|string',
            'prod_image_url' => 'nullable|string',
            'prod_is_active' => 'boolean',
            'prod_meta' => 'nullable|array',
        ]);

        $product = Product::create($validated);
        return response()->json($product->load('category'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show(Product $product)
    {
        return $product->load('category');
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="prod_name", type="string", example="Updated Latte"),
     *             @OA\Property(property="prod_price", type="number", format="float", example=5.00),
     *             @OA\Property(property="prod_description", type="string", example="Updated description"),
     *             @OA\Property(property="prod_image_url", type="string", example="https://example.com/updated-latte.jpg"),
     *             @OA\Property(property="prod_is_active", type="boolean", example=true),
     *             @OA\Property(property="prod_meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        return $product;
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
