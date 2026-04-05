<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Place;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalPlaces' => Place::count(),
            'totalEvents' => Event::count(),
            'totalUsers' => User::where('role', UserRole::User)->count(),
            'totalOrganizers' => User::where('role', UserRole::Organizer)->count(),
        ]);
    }
}
