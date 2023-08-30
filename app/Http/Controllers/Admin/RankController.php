<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRankRequest;
use App\Http\Requests\UpdateRankRequest;
use App\Models\Rank;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Rank::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $ranks = QueryBuilder::for(Rank::class)
            ->select([
                'id',
                'name',
                'weight',
                'shortname',
                'total_score_needed',
                'total_play_time_needed',
                'created_at',
            ])
            ->withCount('players')
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields(['name', 'shortname', 'id', 'weight', 'total_score_needed', 'total_play_time_needed']))
            ])
            ->allowedSorts(['id', 'name', 'weight', 'shortname', 'total_score_needed', 'total_play_time_needed', 'created_at'])
            ->defaultSort('-weight', '-id')
            ->paginate($perPage)
            ->withQueryString();


        return Inertia::render('Admin/Rank/IndexRank', [
            'ranks' => $ranks,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize('create', Rank::class);

        return Inertia::render('Admin/Rank/CreateRank');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateRankRequest $request)
    {
        // Calculate in which Order this Rank should be
        $weight = $request->total_score_needed + $request->total_play_time_needed;

        $rank = Rank::create([
            'weight' => $weight,
            'name' => $request->name,
            'shortname' => $request->shortname,
            'description' => $request->descripion ?? null,
            'total_score_needed' => $request->total_score_needed ?? null,
            'total_play_time_needed' => $request->total_play_time_needed ?? null,
            'created_by' => $request->user()->id
        ]);

        // Upload the Photo
        $rank->addMediaFromRequest('photo')->toMediaCollection('rank');

        return redirect()->route('admin.rank.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('New Rank is created successfully')]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rank  $rank
     * @return \Inertia\Response
     */
    public function show(Rank $rank)
    {
        $this->authorize('view', $rank);

        return Inertia::render('Admin/Rank/ShowRank', [
            'rank' => $rank
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rank  $rank
     * @return \Inertia\Response
     */
    public function edit(Rank $rank)
    {
        $this->authorize('update', $rank);

        return Inertia::render('Admin/Rank/EditRank', [
            'rank' => $rank
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rank  $rank
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRankRequest $request, Rank $rank)
    {
        $this->authorize('update', $rank);

        // Update the Rank Detail
        $rank->name = $request->name;
        $rank->shortname = $request->shortname;
        $rank->total_score_needed = $request->total_score_needed;
        $rank->total_play_time_needed = $request->total_play_time_needed;
        $rank->description = $request->description ?? null;
        $rank->weight = $rank->total_score_needed + $rank->total_play_time_needed;
        $rank->updated_by = $request->user()->id;
        $rank->save();

        // If there is photo upload photo
        if ($request->photo) {
            $rank->addMediaFromRequest('photo')->toMediaCollection('rank');
        }

        // Redirect to listing page
        return redirect()->route('admin.rank.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Rank updated successfully')]]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rank  $rank
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Rank $rank)
    {
        $this->authorize('delete', $rank);

        $rank->delete();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Rank has been deleted permanently')]]);
    }

    public function resetRanks()
    {
        $this->authorize('create', Rank::class);

        Rank::all()->each->delete();
        $data = json_decode(file_get_contents(storage_path('seed') . "/ranks.json"), true);
        \DB::table('ranks')->insert($data);

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Rank Reset Successful')]]);
    }
}
