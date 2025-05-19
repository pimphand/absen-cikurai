<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Models\Leave;
use App\Service\NotificationService;
use Illuminate\Container\Attributes\Auth;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaves = Auth::user()->leaves()->paginate(10);
        return response()->json([
            'leaves' => $leaves,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeaveRequest $request)
    {
        $leave = Leave::create(array_merge($request->validated(), [
            'user_id' => auth()->id(),
            'attachment' => $request->file('attachment') ? $request->file('attachment')->store('attachments') : null,
        ]));

        return response()->json([
            'message' => 'Leave request created successfully.',
            'leave' => $leave,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeaveRequest $request, Leave $leave)
    {
        $leave->update([
            'status' => $request->status,
        ]);
        $leave->save();

        // Ensure we have a valid user_id
        if ($leave->user_id) {
            // Send a private notification
            $notification = new NotificationService();
            $notification->sendPrivateNotification('Leave Request Updated', 'Your leave request has been updated.', $leave->user_id);

            // Send a public notification
            $notification->send('notification', 'leaves', [
                'title' => 'Leave Request Updated',
                'content' => 'A leave request has been updated.',
            ]);
        }

        return response()->json([
            'message' => 'Leave request updated successfully.',
            'leave' => $leave,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave)
    {
        //
    }
}
