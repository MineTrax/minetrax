<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePollRequest;
use App\Models\Poll;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PollController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Poll::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $polls = QueryBuilder::for(Poll::class)
            ->with(['options', 'creator:id,name,username,profile_photo_path'])
            ->allowedFilters([
                'id',
                'question',
                'is_closed',
                'started_at',
                'closed_at',
                'created_at',
                'created_by',
                'updated_by',
                AllowedFilter::custom('q', new FilterMultipleFields(['question', 'id'])),
            ])
            ->allowedSorts(['id', 'question', 'is_closed', 'started_at', 'closed_at', 'created_at', 'created_by', 'updated_by'])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Poll/IndexPoll', [
            'polls' => $polls,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Poll::class);

        return Inertia::render('Admin/Poll/CreatePoll');
    }

    public function store(CreatePollRequest $request)
    {
        // Open Transaction

        // Create Poll
        $poll = Poll::create([
            'question' => $request->question,
            'started_at' => $request->started_at ?? now(),
            'closed_at' => $request->closed_at,
            'created_by' => $request->user()->id,
        ]);

        // Create Options
        $poll->options()->createMany($request->options);

        // Commit transaction

        return redirect()->route('admin.poll.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Poll is created successfully')]]);
    }

    public function lock(Request $request, Poll $poll)
    {
        $this->authorize('update', Poll::class);

        $poll->is_closed = true;
        $poll->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Locked Successfully'), 'body' => __('Poll is locked successfully')]]);
    }

    public function unlock(Request $request, Poll $poll)
    {
        $this->authorize('update', Poll::class);

        $poll->is_closed = false;
        $poll->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Unlock Successfully'), 'body' => __('Poll is unlocked successfully')]]);
    }

    public function destroy(Poll $poll)
    {
        $this->authorize('delete', $poll);

        $poll->delete();

        return redirect()->route('admin.poll.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Poll has been deleted permanently')]]);
    }
}
