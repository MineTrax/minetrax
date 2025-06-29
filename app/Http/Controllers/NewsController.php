<?php

namespace App\Http\Controllers;

use App\Events\NewsCommentCreated;
use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with('creator:id,name,username,profile_photo_path')
            ->whereNotNull('published_at')
            ->withCount('comments')
            ->orderBy('published_at', 'desc')
            ->simplePaginate(5);

        $news->each(function ($n) {
            $converter = new GithubFlavoredMarkdownConverter();
            $strippedBody = \Str::words($n->body, 250);
            $n->body_html_small = $converter->convertToHtml($strippedBody)->getContent();
        });

        if ($request->wantsJson()) {
            return response()->json($news);
        }

        return Inertia::render('News/IndexNews', [
            'newses' => $news
        ]);
    }

    public function show(News $news): \Inertia\Response
    {
        if (!$news->published_at) {
            $this->authorize('view', $news);
        }

        $newslist = News::orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->whereNotNull('published_at')
            ->limit(5)
            ->select(['id', 'title', 'published_at', 'type', 'slug', 'body'])
            ->get()
            ->makeHidden('body');

        return Inertia::render('News/ShowNews', [
            'newslist' => $newslist,
            'news' => $news->append(['body_html'])->load('creator:id,name,username,profile_photo_path')
        ]);
    }

    public function indexComment(News $news)
    {
        $comments = $news->comments()
            ->with('commentator:id,username,name,profile_photo_path,verified_at,settings')
            ->orderByDesc('id')
            ->cursorPaginate(5);

        return $comments;
    }

    public function postComment(Request $request, News $news)
    {
        $request->validate([
            'comment' => 'required|max:250'
        ]);

        if (!$news->is_commentable) {
            return response()->json([
                'status' => 'error',
                'message' => __('Comments are not allowed for this news')
            ], 403);
        }

        $comment = $news->comment($request->comment);

        // Fire event
        NewsCommentCreated::dispatch($comment, $news, $request->user());

        return response()->json([
            'status' => 'ok',
            'data' => $comment->load('commentator:id,username,name,profile_photo_path,verified_at,settings')
        ]);
    }

    public function deleteComment(News $news, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Comment has been deleted successfully')]]);
    }
}
