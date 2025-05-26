<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = QueryBuilder::for(Brand::query())
            ->allowedFilters([
                allowedFilter::scope('search'),
            ])
            ->allowedSorts(['name'])
            ->defaultSort('name')
            ->paginate(15)
            ->appends(request()->query())
            ->withQueryString();

        return BrandResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request): \Illuminate\Http\JsonResponse
    {
        Brand::create($request->validated());
        return response()->json([
            'message' => 'Brand created successfully',
        ], 201);
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
    public function update(UpdateBrandRequest $request, Brand $brand): \Illuminate\Http\JsonResponse
    {
        $brand->update(array_merge($request->validated(),[
            'logo' => $request->file('logo') ? $request->file('logo')->store('brands', 'public') : $brand->logo,
        ]));

        return response()->json([
            'message' => 'Brand updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        Brand::destroy($id);
        return response()->json([
            'message' => 'Brand deleted successfully',
        ], 200);
    }
}
