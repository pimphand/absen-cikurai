<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'is_folow_up',
        'items',
        'data',
        'customer_id',
        'status',
        'user_id',
        'driver_id',
        'date_delivery',
        'note',
        'file',
        'discount',
        'invoice_date',
        'is_return',
        'surat_jalan',
        'invoice',
        'bukti_pengiriman',
        'tanggal_pengiriman',
        'paid',
        'collector_id',
    ];

    protected $casts = [
        'is_folow_up' => 'boolean',
        'data' => 'array',
        'items' => 'array',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('date', 'desc');
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class)->orderBy('date', 'desc');
    }
}
