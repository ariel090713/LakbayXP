<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'type' => ['nullable', 'string', 'in:chat,general,avatar,cover'],
        ]);

        $type = $request->input('type', 'general');
        $path = $request->file('file')->store("uploads/{$type}", 's3');

        return response()->json([
            'path' => $path,
            'url' => Storage::disk('s3')->url($path),
        ], 201);
    }
}
