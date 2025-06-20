<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = QueryBuilder::for(Order::class)
            ->with(['user', 'customer', 'driver', 'orderItems', 'payments', 'collector'])
            ->allowedFilters([
                'status',
                'created_at',
                'user.name',
                'customer.name',
                'driver.name',
                'collector.name',
                'sales.name',
            ])
            ->allowedSorts(['created_at', 'status'])
            ->defaultSort('-created_at')
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'customer', 'driver', 'orderItems', 'payments', 'collector']);
        return OrderResource::make($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        if ($request->type === "edit-customer") {
            $order->user_id = $request->sales_id ?? $order->user_id;
            $order->driver_id = $request->shipper_id ?? $order->driver_id;
            $order->collector_id = $request->collector_id ?? $order->collector_id;
            $order->save();
            $order->load(['user', 'customer', 'driver', 'orderItems', 'payments', 'collector']);
            return OrderResource::make($order);
        }

        if ($request->type === "update-discount") {
            $order->discount = $request->discount ?? $order->discount;
            $order->type_discount = $request->type_discount ?? 0;
            $order->save();
            $order->load(['user', 'customer', 'driver', 'orderItems', 'payments', 'collector']);
            return OrderResource::make($order);
        }

        return response()->json([
            'message' => 'Invalid request type'
        ], 400);
    }

    /**
     * Add updateItem to the order.
     */
    public function updateItem(Request $request, OrderItem $order)
    {
        $orderItem = OrderItem::findOrFail($order->id);
        $orderItem->update([
            'quantity' => $request->quantity ?? $orderItem->quantity,
            'price' => $request->price ?? $orderItem->price,
            'subtotal' => $request->subtotal ?? $orderItem->subtotal,
            'discount' => $request->discount ?? $orderItem->discount,
            'is_percentage' => $request->is_percentage ?? $orderItem->is_percentage,
        ]);

        return response()->json([
            'message' => 'Order item updated successfully',
            'data' => $orderItem
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    /**
     * add payment to order
     */

    public function addPayment(Request $request, Order $order)
    {
        return DB::transaction(function () use ($request, $order) {
            $order->payments()->create([
                'method' => $request->method,
                'date' => Carbon::parse($request->date)->toDateString() . ' ' . now()->toTimeString(),
                'amount' => $request->amount,
                'remaining' => $order->payment()->first()->remaining - $request->amount,
                'customer' => $order->customer->name,
                'collector' => Auth::user()->name,
                'user_id' => $order->user_id,
                'customer_id' => $order->customer_id,
            ]);

            if ($request->paid) {
                $order->paid = $request->paid;
                $order->status = 'success';
                $order->save();
            }

            return response()->json([
                'message' => 'Payment created',
            ]);
        });
    }
}
