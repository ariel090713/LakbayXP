<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;

class BadgeController extends Controller
{
    public function index(): JsonResponse
    {
        $badges = Badge::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $badges]);
    }
}
