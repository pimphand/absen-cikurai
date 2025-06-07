<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkuResource;
use App\Models\Sku;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sku = Sku::with('image')
            ->whereHas('image')
            ->when($request->home, function ($query) {
                return $query->whereHas('product', function ($query) {
                    return $query->whereNotNull('file');
                });
            })
            ->when($request->name, function ($query, $name) {
                return $query->where('name', 'like', '%'.$name.'%');
            })
            ->when($request->brand, function ($query, $brand) {
                return $query->whereHas('product', function ($query) use ($brand) {
                    return $query->where('name', 'like', '%'.$brand.'%');
                });
            })
            ->orderBy('total_order', 'desc')
            ->paginate(12);

        return SkuResource::collection($sku);
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
    public function show(string $id)
    {
        $sku = Sku::find($id)->load(['product', 'product.category', 'image']);
        $recomended = Sku::with('image')
            ->whereHas('image')
            ->where('id', '!=', $id)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return SkuResource::make($sku)->additional([
            'recomended' => SkuResource::collection($recomended),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
