<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Returns Orders with a specific cart
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $orders = Order::filter(request(['cart_id']))->with('product')->get();
        if (!$orders) {
            return $this->failed('There are no orders with these information');
        }
        return $this->success(new OrderResource($orders));
    }

    /**
     * Creates an Order
     *
     * @param OrderRequest $request
     * @return JsonResponse
     */
    public function store(OrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $product = Product::find($data['product_id']);
        $product->quantity -= $data['quantity'];
        $product->number_of_sales += $data['quantity'];
        $product->save();
        $data['total'] = $data['quantity'] * $product->price;

        Order::query()->create($data);
        return $this->success(null);
    }

    /**
     * Returns an Order
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        return $this->success(new OrderResource($order));
    }

    /**
     * Updates an order
     *
     * @param Request $request
     * @param Order $order
     * @return JsonResponse
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $data = $request->all();
        $data['total'] = $data['quantity'] * $order->product->price;
        $order->update($data);
        return $this->success(null);
    }

    /**
     * Deletes an order
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return $this->success(null);
    }
}
