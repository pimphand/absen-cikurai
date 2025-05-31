<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use App\Service\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('debt-collector')) {
            $data = $user->ordersDeptCollector()->when(request('search'), function ($query) {
                $query->whereHas('customer', function ($query) {
                    $query->where('name', 'like', '%' . request('search') . '%');
                });
            });
            return OrderResource::collection($data->paginate(10));
        }

        if ($user->hasRole('driver')) {
            $data = $user->orderDrivers()->with(['customer'])
                ->when(request('search'), function ($query) {
                    $query->whereHas('customer', function ($query) {
                        $query->where('name', 'like', '%' . request('search') . '%');
                    });
                });
            return OrderResource::collection($data->paginate(10));
        }

        if ($user->hasRole('sales')) {
            $data = $user->orders()->with(['customer'])
                ->when(request('search'), function ($query) {
                    $query->whereHas('customer', function ($query) {
                        $query->where('name', 'like', '%' . request('search') . '%');
                    });
                });
            return OrderResource::collection($data->paginate(10));
        }

        return  response()->json([
            'message' => 'terjadi kesalahan',
        ], 400);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'quantity' => 'required|array',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:skus,id',
            'quantity.*' => 'required|integer',
        ], [
            'items.*.product_id.exists' => 'Produk tidak ditemukan',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka',
            'items.*.quantity.required' => 'Jumlah harus diisi',
            'items.*.product_id.required' => 'Produk harus diisi',
            'customer_id.required' => 'Customer harus diisi',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'status' => 'pending',
                'discount' => $request->discount ?? 0,
                'user_id' => $request->sales_id ?? Auth::id(),
            ]);
            $total = 0;
            foreach ($request->items as $key => $value) {
                $sku = Sku::find($value['product_id']);
                OrderItem::create([
                    'quantity' => $value['quantity'],
                    'sku_id' => $sku->id,
                    'price' => $value['price'] ?? 0,
                    'total' => $value['quantity'] * ($value['price'] ?? 0),
                    'order_id' => $order->id,
                ]);

                $items[] = [
                    'product_id' => $value['product_id'],
                    'quantity' => $value['quantity'],
                    'name' => $sku->name,
                    'brand' => $sku->product->name,
                    'category' => $sku->product->category->name,
                    'image' => $sku->image->path ?? null,
                    'package' => $sku->packaging,
                ];

                $sku->total_order += $value['quantity'];
                $sku->save();

                $total += $value['quantity'] * ($value['price'] ?? 0);
            }

            $order->payments()->create([
                'method' => "System",
                'date' => now(),
                'amount' => 0,
                'remaining' => $total,
                'customer' => $order->customer->store_name,
                'collector' => "System",
                'admin' => "System",
            ]);

            $order->items = $items;
            $order->save();

            return response()->json([
                'message' => 'Order created',
            ]);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validate = Validator::make($request->all(), [
            'note' => 'nullable|string',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'quantity' => 'nullable|array',
            'id' => 'required|array',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 422);
        }

        Log::alert(json_encode($request->all()));
        return DB::transaction(function () use ($request, $order) {
            $total = 0;
            foreach ($request->id as $key => $sku) {
                $item = $order->orderItems()->where('id', $sku)->first();
                if ($request->quantity[$key] > 0) {
                    if ($item) {
                        $item->update([
                            'returns' => $request->quantity[$key],
                        ]);
                    }
                }
                if ($item) {
                    $total += $item->quantity * $item->price;
                }
            }

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('public/orders');
                $fileUrl = asset('storage/' . str_replace('public/', '', $path));
            }

            if ($request->status == 'retur') {
                $order->status = 'process';
                $order->is_return = true;
            } else {
                $order->status = 'success';
                $order->tanggal_pengiriman = now();
            }

            $order->note = $request->note ?? $order->note;
            $order->file = $fileUrl ?? $order->file;
            $order->bukti_pengiriman = $fileUrl ?? $order->file;
            $order->save();

            return response()->json([
                'message' => 'Order updated',
                'data' => [
                    'file_url' => $fileUrl ?? null,
                    'note' => $order->note,
                    'status' => $order->status,
                ]
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    public function addPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'date' => 'required|date',
        ]);

        $order->payments()->create([
            'amount' => $request->amount,
            'date' => Carbon::parse($request->date)->toDateString() . ' ' . now()->toTimeString(),
            'remaining' => $order->payments->first()->remaining - $request->amount,
            'collector' => Auth::user()->name,
            'user_id' => $order->user_id,
            'customer_id' => $order->customer_id,
            'method' => $request->payment_method,
            'customer' => $order->customer->name,
            'file' => $request->file('image') ? $request->file('image')->store('payments', 'public') : null,
        ]);

        $notification = new NotificationService();

        $notification->sendPrivateNotification(
            'Pembayaran berhasil',
            "Pembayaran untuk order #{$order->id} telah berhasil ditambahkan. Jumlah: {$request->amount} {$request->payment_method}.",
            $order->user_id
        );

        return response()->json([
            'message' => 'Payment added successfully',
        ]);
    }
}
