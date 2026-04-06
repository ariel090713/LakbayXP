<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerOnboardingController extends Controller
{
    public function show(Request $request): View
    {
        return view('organizer.onboarding', ['user' => $request->user()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organizer_type' => ['required', 'in:solo,agency,organization'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'organizer_bio' => ['required', 'string', 'max:500'],
            'specialties' => ['required', 'array', 'min:1'],
            'specialties.*' => ['string'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_website' => ['nullable', 'url', 'max:255'],
        ]);

        $request->user()->update([
            'organizer_type' => $validated['organizer_type'],
            'organization_name' => $validated['organization_name'],
            'phone' => $validated['phone'],
            'organizer_bio' => $validated['organizer_bio'],
            'specialties' => $validated['specialties'],
            'social_links' => [
                'facebook' => $validated['social_facebook'] ?? null,
                'instagram' => $validated['social_instagram'] ?? null,
                'website' => $validated['social_website'] ?? null,
            ],
            'onboarding_completed' => true,
        ]);

        return redirect()->route('organizer.dashboard')->with('success', 'Profile setup complete! Welcome to LakbayXP.');
    }
}
