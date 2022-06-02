<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with('creator:id,name,username,profile_photo_path')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->simplePaginate(5);

        $news->each(function($n) {
            $n->append(['body_html', 'time_to_read']);
            $n->body_html_small = \Str::limit($n->body_html, 500);
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
            ->append('time_to_read')
            ->makeHidden('body');

        return Inertia::render('News/ShowNews', [
            'newslist' => $newslist,
            'news' => $news->append(['body_html', 'time_to_read'])->load('creator:id,name,username,profile_photo_path')
        ]);
    }
}
