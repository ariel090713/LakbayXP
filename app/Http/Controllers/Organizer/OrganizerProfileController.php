<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('organizer.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organizer_type' => ['required', 'in:solo,agency,organization'],
            'phone' => ['nullable', 'string', 'max:20'],
            'organizer_bio' => ['nullable', 'string', 'max:500'],
            'specialties' => ['nullable', 'array'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_website' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $path = Storage::disk()->putFile('avatars', $request->file('avatar'));
            if ($path) $user->avatar_path = $path;
        }

        $user->update([
            'name' => $validated['name'],
            'organization_name' => $validated['organization_name'],
            'organizer_type' => $validated['organizer_type'],
            'phone' => $validated['phone'],
            'organizer_bio' => $validated['organizer_bio'],
            'specialties' => $validated['specialties'] ?? [],
            'social_links' => [
                'facebook' => $validated['social_facebook'] ?? null,
                'instagram' => $validated['social_instagram'] ?? null,
                'website' => $validated['social_website'] ?? null,
            ],
        ]);

        return redirect()->route('organizer.profile')->with('success', 'Profile updated.');
    }
}
