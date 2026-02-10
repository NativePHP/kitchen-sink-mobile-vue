<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Native\Mobile\Facades\Dialog;
use Native\Mobile\Facades\File;

class StoreMediaController extends Controller
{
    public function storeAudio()
    {
        $path = Arr::get(request()->all(), 'payload.path');

        if(!$path){
            return response()->json([
                'status' => 'failed',
                'reason' => 'path is empty',
            ]);
        }

        $filename = 'audio/recording_' . time() . '_' . uniqid() . '.m4a';
        File::move($path, Storage::disk('public')->path($filename));
        Dialog::toast('Audio recorded successfully!');

        $url = Storage::disk('public')->url($filename);
        return response()->json([
            'url' => $url,
            'sharePath' => Storage::disk('public')->path($filename)
        ]);
    }

    public function storePhoto()
    {
        $path = Arr::get(request()->all(), 'payload.path');

        if(!$path){
            return response()->json([
               'status' => 'failed',
               'reason' => 'path is empty',
            ]);
        }

        $filename = 'photos/photo_' . time() . '.jpg';
        File::copy($path, Storage::disk('public')->path($filename));
        $url = Storage::disk('public')->url($filename);

        return response()->json([
            'url' => $url,
            'path' => Storage::disk('public')->path($filename),
            'originalPath' => $path
        ]);
    }

    public function storeGalleryPhoto()
    {
        $path = request('path');
        $filename = 'photos/photo_' . time() . '_' . uniqid() . '.jpg';

        File::move($path, Storage::disk('public')->path($filename));

        return response()->json([
            'url' => Storage::disk('public')->url($filename)
        ]);
    }

    public function storeGalleryMedia()
    {
        $files = Arr::get(request()->all(), 'payload.files');

        if (!$files || !is_array($files)) {
            return response()->json([
                'status' => 'failed',
                'reason' => 'files is empty or invalid',
            ]);
        }

        $storedMedia = [];

        foreach ($files as $file) {
            // Handle both string paths and file objects
            $filePath = is_array($file) ? ($file['path'] ?? null) : $file;

            if (!$filePath || !is_string($filePath)) {
                continue;
            }

            // Get extension from the original file path
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            // If no extension, try to detect from MIME type
            if (empty($extension) && file_exists($filePath)) {
                $mimeType = mime_content_type($filePath);

                if (str_starts_with($mimeType, 'video/')) {
                    $extension = 'mp4'; // Default video extension
                } elseif (str_starts_with($mimeType, 'image/')) {
                    // Try to get specific image extension from MIME
                    $extension = match($mimeType) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                        'image/heic', 'image/heif' => 'heic',
                        default => 'jpg',
                    };
                } else {
                    $extension = 'jpg'; // Fallback
                }
            }

            // Determine if it's a video or image based on extension
            $isVideo = preg_match('/^(mp4|mov|avi|webm|m4v|mkv)$/i', $extension);
            $directory = $isVideo ? 'videos' : 'gallery';
            $filename = $directory . '/' . time() . '_' . uniqid() . '.' . $extension;

            File::move($filePath, Storage::disk('public')->path($filename));

            $storedMedia[] = [
                'url' => Storage::disk('public')->url($filename),
                'path' => Storage::disk('public')->path($filename),
                'relative_path' => $filename,
                'type' => $isVideo ? 'video' : 'image'
            ];
        }

        return response()->json([
            'status' => 'success',
            'media' => $storedMedia
        ]);
    }

    public function deleteGalleryMedia()
    {
        $relativePath = Arr::get(request()->all(), 'data.path');
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);

            return response()->json([
                'status' => 'success',
                'message' => 'Media deleted successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Media not found: ' . $relativePath
        ], 404);
    }

    public function getMediaFromDirectory($directory)
    {
        $files = Storage::disk('public')->allFiles($directory);
        $media = [];
        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $fullPath = Storage::disk('public')->path($filePath);

            $media[] = [
                'name' => $filename,
                'url' => Storage::disk('public')->url($filePath),
                'path' => $fullPath,
                'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'modified' => file_exists($fullPath) ? filemtime($fullPath) : 0,
            ];
        }

        // Sort by modified time, newest first
        usort($media, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return response()->json($media);
    }

    public function deleteAudio($filename)
    {
        $filepath = 'audio/' . $filename;

        if (Storage::disk('public')->exists($filepath)) {
            Storage::disk('public')->delete($filepath);
            Dialog::toast('Recording deleted successfully!');

            return response()->json([
                'status' => 'success',
                'message' => 'Recording deleted successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Recording not found'
        ], 404);
    }

    public function storeVideo()
    {
        $path = Arr::get(request()->all(), 'payload.path');

        if (!$path) {
            return response()->json([
                'status' => 'failed',
                'reason' => 'path is empty',
            ]);
        }

        $filename = 'videos/video_' . time() . '_' . uniqid() . '.mp4';
        File::move($path, Storage::disk('public')->path($filename));
        Dialog::toast('Video recorded successfully!');

        return response()->json([
            'url' => Storage::disk('public')->url($filename),
            'path' => Storage::disk('public')->path($filename)
        ]);
    }

    public function deleteVideo($filename)
    {
        $filepath = 'videos/' . $filename;

        if (Storage::disk('public')->exists($filepath)) {
            Storage::disk('public')->delete($filepath);
            Dialog::toast('Video deleted successfully!');

            return response()->json([
                'status' => 'success',
                'message' => 'Video deleted successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Video not found'
        ], 404);
    }
}
