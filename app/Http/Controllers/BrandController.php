<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Models\Brand;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = QueryBuilder::for(Brand::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['created_at'])
            ->paginate(10);

        return BrandResource::collection($brands)
            ->additional([
                'meta' => [
                    'message' => 'Brands retrieved successfully',
                ],
            ]);
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
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        //
    }
}
