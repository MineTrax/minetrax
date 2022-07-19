<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PollController extends Controller
{
    public function index(Request $request)
    {
        $polls = Poll::latest()->select(['id', 'question', 'started_at', 'closed_at', 'is_closed'])->orderByDesc('id')->simplePaginate(5);
        $polls->each(fn($poll) => $poll->append('json_for_vue'));

        if ($request->wantsJson()) {
            return response()->json($polls);
        }

        return Inertia::render('Poll/IndexPoll', [
            'polls' => $polls,
        ]);
    }

    public function vote(Request $request, Poll $poll, PollOption $option)
    {
        // Check if option_id is of this poll
        if ($poll->id != $option->poll_id) {
            return response()->json([
                'message' => __('Invalid option')
            ], 422);
        }

        // vote for the poll
        $request->user()->voteForPollOption($option);

        // return
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Vote Successful')
            ]);
        }

        return redirect()->back();
    }
}
