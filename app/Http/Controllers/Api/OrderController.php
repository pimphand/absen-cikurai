<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Service\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('debt-collector')){
            $data = $user->ordersDeptCollector()->when(request('search'), function ($query) {
                $query->whereHas('customer', function ($query) {
                    $query->where('name', 'like', '%' . request('search') . '%');
                });
            });
            return OrderResource::collection($data->paginate(10));
        }

        return  response()->json([
            'message' => 'terjadi kesalahan',
        ],400);
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
    public function store(Request $request)
    {
        //
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
        //
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
            'payment_method' => $request->payment_method,
            'date' => Carbon::parse($request->date)->toDateString() . ' ' . now()->toTimeString(),
            'remaining' => $order->payments->first()->remaining - $request->amount,
            'collector' => Auth::user()->name,
            'user_id' => $order->user_id,
            'customer_id' => $order->customer_id,
            'method' => $request->payment_method,
        ]);

        Log::alert('Payment added', [
            'order_id' => $order->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'date' => Carbon::parse($request->date)->toDateString() . ' ' . now()->toTimeString(),
        ]);

        $notification = new NotificationService();

        $message = [
            'title' => 'Pembayaran Diterima',
            'body' => 'Pembayaran sebesar ' . $request->amount . ' telah diterima untuk order ' . $order->id,
            'data' => [
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'date' => Carbon::parse($request->date)->toDateString() . ' ' . now()->toTimeString(),
            ],
        ];

        $notification->sendPrivateNotification(
            'Status Cuti Diperbarui',
            (string)$message,
            $order->user_id
        );

        return response()->json([
            'message' => 'Payment added successfully',
        ]);
    }
}
