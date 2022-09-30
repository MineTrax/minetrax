<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBadgeRequest;
use App\Http\Requests\UpdateBadgeRequest;
use App\Models\Badge;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BadgeController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Badge::class);

        $badges = Badge::orderBy('sort_order')
            ->latest()
            ->withCount('users')
            ->paginate(10);

        return Inertia::render('Admin/Badge/IndexBadge', [
            'badges' => $badges
        ]);
    }

    public function create()
    {
        $this->authorize('create', Badge::class);

        return Inertia::render('Admin/Badge/CreateBadge');
    }

    public function store(CreateBadgeRequest $request)
    {
        $rank = Badge::create([
            'sort_order' => $request->sort_order,
            'name' => $request->name,
            'shortname' => $request->shortname,
            'is_sticky' => $request->is_sticky,
            'created_by' => $request->user()->id
        ]);

        // Upload the Photo
        $rank->addMediaFromRequest('photo')->toMediaCollection('badge');

        return redirect()->route('admin.badge.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('New Badge is created successfully')]]);
    }

    public function edit(Badge $badge)
    {
        $this->authorize('update', $badge);

        return Inertia::render('Admin/Badge/EditBadge', [
            'badge' => $badge
        ]);
    }

    public function update(UpdateBadgeRequest $request, Badge $badge)
    {
        $this->authorize('update', $badge);

        $badge->name = $request->name;
        $badge->shortname = $request->shortname;
        $badge->is_sticky = $request->is_sticky;
        $badge->sort_order = $request->sort_order;
        $badge->save();

        // If there is photo upload photo
        if ($request->photo) {
            $badge->addMediaFromRequest('photo')->toMediaCollection('badge');
        }

        // Redirect to listing page
        return redirect()->route('admin.badge.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Badge updated successfully')]]);
    }

    public function destroy(Badge $badge)
    {
        $this->authorize('delete', $badge);

        $badge->delete();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Badge has been deleted permanently')]]);
    }
}
