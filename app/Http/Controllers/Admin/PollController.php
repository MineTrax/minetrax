<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePollRequest;
use App\Models\Poll;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PollController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Poll::class);

        $polls = Poll::with(['options', 'creator:id,name,username,profile_photo_path'])
            ->orderByDesc('id')->paginate(10);

        return Inertia::render('Admin/Poll/IndexPoll', [
            'polls' => $polls
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
            'created_by' => $request->user()->id
        ]);

        // Create Options
        $poll->options()->createMany($request->options);

        // Commit transaction

        return redirect()->route('admin.poll.index')
            ->with(['toast' => ['type' => 'success', 'title' => 'Created Successfully', 'body' => 'Poll is created successfully']]);
    }

    public function lock(Request $request, Poll $poll)
    {
        $this->authorize('update', Poll::class);

        $poll->is_closed = true;
        $poll->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => 'Locked Successfully', 'body' => 'Poll is locked successfully']]);
    }

    public function unlock(Request $request, Poll $poll)
    {
        $this->authorize('update', Poll::class);

        $poll->is_closed = false;
        $poll->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => 'Unlock Successfully', 'body' => 'Poll is unlocked successfully']]);
    }

    public function destroy(Poll $poll)
    {
        $this->authorize('delete', $poll);

        $poll->delete();
        return redirect()->route('admin.poll.index')
            ->with(['toast' => ['type' => 'success', 'title' => 'Deleted Successfully', 'body' => 'Poll has been deleted permanently']]);
    }
}
