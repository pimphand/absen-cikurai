<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = QueryBuilder::for(Category::class)
            ->allowedFilters(['name'])
            ->withCount(['products'])
            ->allowedSorts(['name'])
            ->paginate(10);
        return CategoryResource::collection($categories)
            ->additional([
                'meta' => [
                    'message' => 'Categories retrieved successfully',
                ],
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->only(['name', 'description']));

        return (new CategoryResource($category))
            ->additional([
                'meta' => [
                    'message' => 'Category updated successfully',
                ],
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($request->only(['name', 'description']));

        return (new CategoryResource($category))
            ->additional([
                'meta' => [
                    'message' => 'Category updated successfully',
                ],
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Category cannot be deleted because it has associated products',
                'data' => $category
            ], 422);
        }
        $category->delete();
        return response()->json([
            'message' => 'Category deleted successfully',
            'data' => $category
        ], 204);
    }
}
