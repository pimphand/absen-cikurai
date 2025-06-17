<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sku;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Products
        $totalProducts = Sku::count();

        // 2. Total Orders
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();

        // 3. Total Amount Orders
        $totalAmount = Order::where('status', '!=', 'cancelled')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        // 4. 5 Latest Orders
        $latestOrders = Order::with(['customer', 'user'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer->name,
                    'sales_name' => $order->user->name,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'total_amount' => $order->orderItems->sum(function ($item) {
                        return $item->quantity * $item->price;
                    })
                ];
            });

        // 5. Top 5 Sales by Revenue
        $topSales = User::whereHas('orders', function ($query) {
            $query->where('status', '!=', 'cancelled');
        })
            ->withSum(['orders as total_revenue' => function ($query) {
                $query->where('status', '!=', 'cancelled')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->select(DB::raw('SUM(order_items.quantity * order_items.price)'));
            }], 'total_revenue')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_revenue' => $user->total_revenue ?? 0
                ];
            });

        return response()->json([
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_amount' => $totalAmount,
            'latest_orders' => $latestOrders,
            'top_sales' => $topSales
        ]);
    }
}
