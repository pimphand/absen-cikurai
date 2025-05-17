<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenInRequest;
use App\Models\Absen;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'message' => 'Absen list',
            'data' => Auth::user()->attendance()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbsenInRequest $request)
    {
        $user = Auth::user();

        if ($user->is_active === 0) {
            return response()->json(['message' => 'User is not active'], 400);
        }

        $attendanceDate = now()->toDateString();

        $attendance = Absen::where('user_id', $user->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

       if (env('APP_DEBUG') === false) {
           if ($attendance) {
               return response()->json(['message' => 'You have already checked in for today'], 400);
           }
       }

        $checkInTime = now();
        $officeStartTime = Carbon::createFromTime(9, 0, 0); // Jam masuk kantor, misalnya 09:00 pagi
        $status = 'Sudah Absen';

        if ($checkInTime->greaterThan($officeStartTime)) {
            $status = 'Terlambat';
        }

        $user->attendance()->create([
            'attendance_date' => $attendanceDate,
            'check_in' => $checkInTime->toTimeString(),
            'latitude_check_in' => $request->latitude_check_in,
            'longitude_check_in' => $request->longitude_check_in,
            'status_check_in' => $status,
            'photo_check_in' => $request->file('photo_check_in')->store('checkin', 'public'),
        ]);

        return response()->json([
            'message' => 'Absen berhasil',
        ]);
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
