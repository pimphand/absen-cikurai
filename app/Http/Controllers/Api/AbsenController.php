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
     * Add watermark to an image
     *
     * @param string $imagePath Path to the image
     * @param string $text Text to add as watermark
     * @return void
     */
    private function addWatermark($imagePath, $text)
    {
        // Get image information
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Create image resource based on mime type
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return; // Unsupported image type
        }

        // Enable alpha blending
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // Create a semi-transparent background for the text
        $bgColor = imagecolorallocatealpha($image, 0, 0, 0, 50); // Black with 70% opacity (more opaque)
        $textColor = imagecolorallocate($image, 255, 255, 0); // Yellow text for better visibility

        // Calculate text dimensions
        $lines = explode("\n", $text);
        $lineCount = count($lines);
        $lineHeight = 30; // Increased line height
        $padding = 10;
        $totalHeight = ($lineCount * $lineHeight) + (2 * $padding);

        // Draw semi-transparent background
        $x = 20; // X position
        $y = 20; // Y position
        $bgWidth = $width - 40; // Width of background
        imagefilledrectangle($image, $x, $y, $x + $bgWidth, $y + $totalHeight, $bgColor);

        // Add watermark text with larger font size
        $fontSize = 5; // Increased font size (5 is the largest for imagestring)
        $textY = $y + $padding;

        foreach ($lines as $line) {
            // Draw the text with a bold effect (draw multiple times with slight offsets)
            // Create a black outline/shadow effect
            $shadowColor = imagecolorallocate($image, 0, 0, 0); // Black for shadow
            imagestring($image, $fontSize, $x + $padding - 1, $textY - 1, $line, $shadowColor);
            imagestring($image, $fontSize, $x + $padding + 1, $textY - 1, $line, $shadowColor);
            imagestring($image, $fontSize, $x + $padding - 1, $textY + 1, $line, $shadowColor);
            imagestring($image, $fontSize, $x + $padding + 1, $textY + 1, $line, $shadowColor);

            // Draw the main text
            imagestring($image, $fontSize, $x + $padding, $textY, $line, $textColor);

            // Draw the text again slightly offset for a bolder effect
            imagestring($image, $fontSize, $x + $padding + 1, $textY, $line, $textColor);
            imagestring($image, $fontSize, $x + $padding, $textY + 1, $line, $textColor);

            $textY += $lineHeight;
        }

        // Save the image with watermark
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($image, $imagePath, 90); // 90 is quality
                break;
            case 'image/png':
                imagepng($image, $imagePath, 9); // 9 is compression level
                break;
            case 'image/gif':
                imagegif($image, $imagePath);
                break;
        }

        // Free memory
        imagedestroy($image);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'message' => 'Absen list',
            'data' => Auth::user()->attendance()->sortBy('created_at','desc')->paginate(10),
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

        // Get the uploaded image
        $image = $request->file('photo_check_in');
        $imagePath = $image->store('checkin', 'public');
        $fullPath = storage_path('app/public/' . $imagePath);

        // Add watermark to the image
        try {
            $watermarkText = "CMI\nLat: " . $request->latitude_check_in .
                "\nLong: " . $request->longitude_check_in .
                "\nJarak: " . $request->jarak . "M";

            $this->addWatermark($fullPath, $watermarkText);

            // Verify the file exists after watermarking
            if (!file_exists($fullPath)) {
                \Log::error('Watermarked image file does not exist: ' . $fullPath);
            }
        } catch (\Exception $e) {
            \Log::error('Error applying watermark: ' . $e->getMessage());
        }

        $user->attendance()->create([
            'attendance_date' => $attendanceDate,
            'check_in' => $checkInTime->toTimeString(),
            'latitude_check_in' => $request->latitude_check_in,
            'longitude_check_in' => $request->longitude_check_in,
            'status_check_in' => $status,
            'photo_check_in' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Absen berhasil',
            'watermark_applied' => true,
            'image_path' => $imagePath
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
