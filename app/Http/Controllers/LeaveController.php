<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Models\Leave;
use App\Models\Notification;
use App\Service\NotificationService;
use Illuminate\Support\Facades\Auth;

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
            'user_id' => Auth::id(),
            'attachment' => $request->hasFile('attachment') ? $request->file('attachment')->store('attachments') : null,
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
    public function update(UpdateLeaveRequest $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->update([
            'status' => $request->input('status'),
        ]);

        // Send notification to the leave requester
        if ($leave->user_id) {
            $notification = new NotificationService();
            $status = $leave->status;
            $message = match ($status) {
                'approved' => 'Pengajuan cuti Anda telah disetujui oleh manajer.',
                'rejected' => 'Pengajuan cuti Anda telah ditolak oleh manajer.',
                default => 'Status pengajuan cuti Anda telah diperbarui.'
            };

            $notification->sendPrivateNotification(
                'Status Cuti Diperbarui',
                $message,
                $leave->user_id
            );

            // Save notification to database
            Notification::create([
                'user_id' => $leave->user_id,
                'type' => 'leave_status',
                'title' => 'Status Cuti Diperbarui',
                'message' => $message,
                'url' => '/leaves/' . $leave->id,
                'is_read' => 0,
                'notifiable_type' => Leave::class,
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
