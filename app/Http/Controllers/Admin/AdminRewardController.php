<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Services\RewardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminRewardController extends Controller
{
    public function __construct(
        protected RewardService $rewardService,
    ) {}

    public function index(): View
    {
        $rewards = Reward::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.rewards.index', compact('rewards'));
    }

    public function create(): View
    {
        return view('admin.rewards.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:rewards,slug'],
            'description' => ['nullable', 'string'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = Storage::disk('s3')->putFile('reward-images', $request->file('image'), 'public');
        }

        unset($validated['image']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = $request->user()->id;

        Reward::create($validated);

        return redirect()->route('admin.rewards.index')->with('success', 'Reward created.');
    }

    public function edit(Reward $reward): View
    {
        return view('admin.rewards.edit', compact('reward'));
    }

    public function update(Request $request, Reward $reward): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:rewards,slug,' . $reward->id],
            'description' => ['nullable', 'string'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = Storage::disk('s3')->putFile('reward-images', $request->file('image'), 'public');
        }

        unset($validated['image']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $reward->update($validated);

        return redirect()->route('admin.rewards.index')->with('success', 'Reward updated.');
    }

    public function destroy(Reward $reward): RedirectResponse
    {
        $reward->update(['is_active' => false]);

        return redirect()->route('admin.rewards.index')->with('success', 'Reward deactivated.');
    }

    /**
     * List all redemptions for admin review.
     */
    public function redemptions(): View
    {
        $redemptions = RewardRedemption::with(['user', 'reward'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.rewards.redemptions', compact('redemptions'));
    }

    public function approveRedemption(RewardRedemption $redemption): RedirectResponse
    {
        $this->rewardService->approve($redemption);

        return redirect()->route('admin.rewards.redemptions')->with('success', 'Redemption approved.');
    }

    public function rejectRedemption(Request $request, RewardRedemption $redemption): RedirectResponse
    {
        $this->rewardService->reject($redemption, $request->input('admin_notes'));

        return redirect()->route('admin.rewards.redemptions')->with('success', 'Redemption rejected. Points refunded.');
    }

    public function claimRedemption(RewardRedemption $redemption): RedirectResponse
    {
        $this->rewardService->markClaimed($redemption);

        return redirect()->route('admin.rewards.redemptions')->with('success', 'Redemption marked as claimed.');
    }
}
