<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'owner_address',
        'store_name',
        'store_photo',
        'owner_photo',
        'identity',
        'npwp',
        'others',
        'user_id',
        'is_blacklist',
        'city',
        'state',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeIsBlock($query, $bool = false)
    {
        return $query->where('is_blacklist', $bool);
    }

    public  function orders()
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    public function order()
    {
        return $this->hasOne(Order::class)->orderBy('created_at', 'desc');
    }

    public function sales()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public static function data($request,$date = null)
    {
        $customers = Customer::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('state', 'like', "%{$request->search}%")
                ->orWhere('city', 'like', "%{$request->search}%")
                ->orWhere('address', 'like', "%{$request->search}%")
                ->orWhere('store_name', 'like', "%{$request->search}%");
        })
            ->when($request->sales,function ($query) use ($request){
                $query->where('user_id', $request->sales);
            })
            ->when($request->is_blacklist,function ($query) use ($request){
                $query->where('is_blacklist', $request->is_blacklist);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereBetween('valid_orders.invoice_date', [$this->formatDate($date)]);
            })
            ->with('order:id,created_at,customer_id')
            ->with('sales:id,name')
            ->leftJoin(DB::raw('(
            SELECT id, customer_id, invoice_date
            FROM orders
            WHERE status != "cancel"
        ) valid_orders'), 'customers.id', '=', 'valid_orders.customer_id')
            ->leftJoin(DB::raw('(
            SELECT p1.order_id, p1.amount, p1.remaining, p2.latest_date
            FROM payments p1
            JOIN (
                SELECT order_id, MAX(date) as latest_date
                FROM payments
                GROUP BY order_id
            ) p2 ON p1.order_id = p2.order_id AND p1.date = p2.latest_date
        ) latest_payments'), 'valid_orders.id', '=', 'latest_payments.order_id')
            ->leftJoin(DB::raw('(
            SELECT order_id, SUM(quantity * price) as total_value
            FROM order_items
            WHERE order_id IN (SELECT id FROM orders WHERE status != "cancel")
            GROUP BY order_id
        ) order_values'), 'valid_orders.id', '=', 'order_values.order_id')
            ->leftJoin(DB::raw('(
            SELECT customer_id, SUM(discount) as total_discount
            FROM orders
            WHERE status != "cancel" AND status = "success"
            GROUP BY customer_id
        ) order_discounts'), 'customers.id', '=', 'order_discounts.customer_id')
            ->select(
                'customers.id',
                'customers.user_id',
                'customers.name',
                'customers.phone',
                'customers.store_name',
                'customers.city',
                'customers.state',
                'customers.address',
                'customers.is_blacklist',
                'customers.created_at',
                'customers.updated_at',
                'customers.deleted_at',
                DB::raw('IFNULL(SUM(order_values.total_value), 0) as total_order_value'),
                DB::raw('IFNULL(MAX(latest_payments.remaining), 0) as total_remaining'),
                DB::raw('IFNULL(MAX(order_discounts.total_discount), 0) as total_discount'),
                DB::raw('MIN(valid_orders.invoice_date) as min_payment_due_date'), // Perbaikan di sini
                DB::raw('MAX(valid_orders.invoice_date) as payment_due_date'), // Perbaikan di sini
            )
            ->groupBy(
                'customers.id',
                'customers.user_id',
                'customers.name',
                'customers.phone',
                'customers.city',
                'customers.state',
                'customers.address',
                'customers.store_name',
                'customers.is_blacklist',
                'customers.created_at',
                'customers.updated_at',
                'customers.deleted_at'
            )
            ->paginate(10)
            ->appends($request->query());

        return $customers;
    }

    public function formatDate($request): array
    {
        if (!isset($request->date) || empty($request->date)) {
            $firstDay = now()->firstOfMonth()->startOfDay()->format('Y-m-d H:i:s');
            $lastDay = now()->lastOfMonth()->endOfDay()->format('Y-m-d H:i:s');
        } else {
            $date = explode(' - ', $request->date);

            $dateStart = DateTime::createFromFormat('d/m/Y', $date[0]) ?: now()->firstOfMonth();
            $dateEnd = DateTime::createFromFormat('d/m/Y', $date[1]) ?: now()->lastOfMonth();

            $firstDay = $dateStart->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $lastDay = $dateEnd->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        }

        return [$firstDay, $lastDay];
    }
}
