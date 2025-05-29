<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customers = Customer::query();

        if ($request->has('search')) {
            $customers->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_blacklist')) {
            $customers->where('is_blacklist', $request->is_blacklist);
        }

        if (!$request->has('is_blacklist')) {
            $customers->where('user_id', auth()->user()->id);
        }

        return CustomerResource::collection($customers->paginate($request->per_page));
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
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
