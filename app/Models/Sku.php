<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';

    protected $primaryKeyType = 'string';

    protected $fillable = ['name', 'price', 'code', 'product_id', 'description', 'application', 'packaging', 'total_order'];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Image::class, 'imaginable');
    }

    public function image(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Image::class, 'imaginable');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->code = 'SKU-' . self::singkatan($model->name) . microtime(true);
        });
    }

    public static function singkatan($text): string
    {
        $words = explode(' ', trim($text)); // Hilangkan spasi ekstra di awal & akhir
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) { // Cek apakah kata tidak kosong
                $initials .= strtoupper($word[0]); // Ambil huruf pertama
            }
        }

        return $initials;
    }
}
