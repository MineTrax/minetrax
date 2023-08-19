<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBadgeRequest;
use App\Http\Requests\UpdateBadgeRequest;
use App\Models\Badge;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BadgeController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Badge::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $badges = QueryBuilder::for(Badge::class)->withCount('users')
            ->allowedFilters([
                'id',
                'name',
                'shortname',
                'is_sticky',
                'sort_order',
                'created_at',
                'updated_at',
                AllowedFilter::custom('q', new FilterMultipleFields(['name', 'shortname', 'id'])),
            ])
            ->allowedSorts(['id', 'name', 'created_at', 'updated_at', 'shortname', 'is_sticky', 'sort_order'])
            ->defaultSort('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Badge/IndexBadge', [
            'badges' => $badges,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
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
            'created_by' => $request->user()->id,
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
            'badge' => $badge,
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
