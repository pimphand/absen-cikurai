<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|numeric|digits_between:10,13|unique:customers,phone',
            'address' => 'required|',
            //            'owner_address' => 'required',
            'store_name' => 'required',
            'store_photo' => 'required',
            'owner_photo' => 'nullable',
            'identity' => 'nullable',
            'npwp' => 'nullable',
            'others' => 'nullable',
            'city' => 'required',
            'state' => 'required',
        ]);

        Auth::user()->customers()->create(array_merge($validated->validated(), [
            'store_photo' => asset('storage') . '/' . $request->file('store_photo')->store('customer/store_photo', 'public'),
            'owner_photo' => asset('storage') . '/' . $request->file('owner_photo')->store('customer/owner_photo', 'public'),
        ]));

        return response()->json(['message' => 'Customer created'], 201);
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
    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|numeric|digits_between:10,13|unique:customers,phone,' . $id,
            'address' => 'required|',
            //            'owner_address' => 'required',
            'store_name' => 'required',
            'store_photo' => 'nullable',
            'owner_photo' => 'nullable',
            'identity' => 'nullable',
            'npwp' => 'nullable',
            'others' => 'nullable',
            'city' => 'required',
            'state' => 'required',
        ]);
        $customer = Auth::user()->customers()->findOrFail($id);
        $customer->update(array_merge($validated->validated(), [
            'store_photo' => $request->file('store_photo') ? asset('storage') . '/' . $request->file('store_photo')->store('customer/store_photo', 'public') : $customer->store_photo,
            'owner_photo' => $request->file('owner_photo') ? asset('storage') . '/' . $request->file('owner_photo')->store('customer/owner_photo', 'public') : $customer->owner_photo,
        ]));

        return response()->json(['message' => 'Customer updated']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
