<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbsenResource;
use App\Models\Absen;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class AbsenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $absen = QueryBuilder::for(Absen::class)
            ->with('user:id,name')
            ->with('user.roles')
            ->allowedFilters(['user.name', 'status', 'user.roles.name'])
            ->allowedSorts(['created_at'])
            ->paginate(25);
        return AbsenResource::collection($absen);
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
    public function show(Absen $absen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Absen $absen)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Absen $absen)
    {
        //
    }
}
