<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(mixed $validated)
 */
class Brand extends Model
{
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'logo'
    ];

     /**
     * scope search
     */
    public function scopeSearch($query, $searchTerm){
        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }
        return $query;
    }
}
