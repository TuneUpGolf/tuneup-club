<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VideoUploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        if ($request->isMethod('get')) {
            $identifier = $request->input('resumableIdentifier');
            $chunkNumber = $request->input('resumableChunkNumber');
            $tempPath = "temp/{$identifier}/chunk{$chunkNumber}";

            if (Storage::disk('public')->exists($tempPath)) {
                return response()->json(['success' => true], 200);
            }
            return response()->json(['success' => false], 404);
        }

        $file = $request->file('file');
        $identifier = $request->input('resumableIdentifier');
        $chunkNumber = $request->input('resumableChunkNumber');

        $tempPath = "temp/{$identifier}/";
        Storage::disk('public')->putFileAs($tempPath, $file, "chunk{$chunkNumber}");

        return response()->json(['success' => true]);
    }


    public function finalizeUpload(Request $request)
    {
        $fileId = $request->input('fileId');
        $originalFileName = $request->input('fileName'); // e.g., "4.jpg"

        $tempDir = "temp/{$fileId}/";

        // Get file extension (like jpg, mp4, etc.)
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        // Generate a random unique new file name
        $randomFileName = uniqid() . '.' . $extension;

        // Save final file path
        $finalPath = "videos/{$randomFileName}";

        // Get all chunks
        $chunks = Storage::disk('public')->files($tempDir);
        if (empty($chunks)) {
            return response()->json(['success' => false, 'message' => 'No chunks found'], 400);
        }

        // Sort chunks numerically
        usort($chunks, function ($a, $b) {
            preg_match('/chunk(\d+)/', $a, $aNum);
            preg_match('/chunk(\d+)/', $b, $bNum);
            return ($aNum[1] ?? 0) <=> ($bNum[1] ?? 0);
        });

        // Ensure videos directory exists
        Storage::disk('public')->makeDirectory('videos');

        // Merge chunks
        $fileResource = fopen(Storage::disk('public')->path($finalPath), 'wb');
        foreach ($chunks as $chunk) {
            $chunkContent = Storage::disk('public')->get($chunk);
            fwrite($fileResource, $chunkContent);
        }
        fclose($fileResource);

        // Delete temp folder after merge
        Storage::disk('public')->deleteDirectory($tempDir);


        // Return success response with random file path
        return response()->json([
            'success'   => true,
            'message'   => 'Video uploaded successfully',
            'filePath'  => $finalPath,
            'fileName'  => $randomFileName,
            'publicUrl' => asset("storage/{$finalPath}")
        ]);
    }
}
