<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrganizerController extends Controller
{
    /**
     * Display a listing of organizers.
     */
    public function index(Request $request): View
    {
        $organizers = User::where('role', UserRole::Organizer)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.organizers.index', compact('organizers'));
    }

    /**
     * Verify an organizer.
     */
    public function verify(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Organizer) {
            return redirect()->route('admin.organizers.index')
                ->with('error', 'Only organizer accounts can be verified.');
        }

        $user->update(['is_verified_organizer' => true]);

        return redirect()->route('admin.organizers.index')
            ->with('success', "{$user->name} has been verified as an organizer.");
    }
}
