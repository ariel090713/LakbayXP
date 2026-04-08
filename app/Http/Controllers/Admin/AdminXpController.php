<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\XpHistory;
use App\Services\XpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminXpController extends Controller
{
    public function __construct(protected XpService $xpService) {}

    public function index(Request $request): View
    {
        $query = User::where('role', 'user')->withCount(['unlockedPlaces', 'badges']);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $users = $query->orderByDesc('xp')->paginate(20)->withQueryString();

        $recentGrants = XpHistory::where('source', 'admin')
            ->with(['user:id,name,username', 'grantedBy:id,name'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $categories = collect(\App\Enums\PlaceCategory::cases())->map(function ($c) {
            return ['value' => $c->value, 'label' => str_replace('_', ' ', ucfirst($c->value))];
        });

        return view('admin.xp.index', compact('users', 'recentGrants', 'categories'));
    }

    public function grant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:10000'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $this->xpService->adminGrantXp(
            admin: $request->user(),
            user: $user,
            amount: $validated['amount'],
            description: $validated['description'],
            category: $validated['category'] ?: null,
        );

        // Notify user
        app(\App\Services\NotificationService::class)->notifyXpGranted($user, $validated['amount'], $validated['description']);

        return redirect()->route('admin.xp.index')
            ->with('success', "Granted {$validated['amount']} XP to {$user->name}.");
    }
}
