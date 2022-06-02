<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(): \Inertia\Response
    {
        $this->authorize('viewAny', News::class);

        $newslist = News::orderByDesc('id')
            ->select([
            'id',
            'title',
            'slug',
            'published_at',
            'is_pinned',
            'type',
            'created_at',
        ])->paginate(10);

        return Inertia::render('Admin/News/IndexNews', [
            'newslist' => $newslist
        ]);
    }

    public function create(): \Inertia\Response
    {
        $this->authorize('create', News::class);

        return Inertia::render('Admin/News/CreateNews');
    }

    public function store(CreateNewsRequest $request): \Illuminate\Http\RedirectResponse
    {
        $news = News::create([
            'title' => $request->title,
            'slug' => \Str::slug($request->title.' '.now()->timestamp),
            'type' => $request->type,
            'body' => $request->body,
            'published_at' => $request->is_published ? now() : null,
            'is_pinned' => $request->is_pinned,
            'created_by' => $request->user()->id
        ]);

        // Upload the Photo if Have
        if ($request->hasFile('photo')) {
            $news->addMediaFromRequest('photo')->toMediaCollection('news');
        }

        return redirect()->route('admin.news.show', $news->id)
            ->with(['toast' => ['type' => 'success', 'title' => 'Created Successfully', 'body' => 'News is created successfully']]);
    }

    public function show(News $news): \Inertia\Response
    {
        $this->authorize('view', $news);

        return Inertia::render('Admin/News/ShowNews', [
            'news' => $news->append(['body_html', 'time_to_read'])->load('creator:id,name,username,profile_photo_path')
        ]);
    }

    public function edit(News $news): \Inertia\Response
    {
        $this->authorize('update', $news);

        return Inertia::render('Admin/News/EditNews', [
            'news' => $news
        ]);
    }

    public function update(UpdateNewsRequest $request, News $news): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $news);

        // Update the Rank Detail
        $news->slug = $request->title == $news->title ? $news->slug : \Str::slug($request->title.' '.now()->timestamp);
        $news->title = $request->title;
        $news->body = $request->body;
        if ($request->is_published && !$news->published_at) {
            $news->published_at = now();
        } elseif (!$request->is_published && $news->published_at) {
            $news->published_at = null;
        }
        $news->type = $request->type;
        $news->is_pinned = $request->is_pinned;
        $news->updated_by = $request->user()->id;
        $news->save();

        // If there is photo upload photo
        if ($request->photo) {
            $news->addMediaFromRequest('photo')->toMediaCollection('news');
        }

        // Redirect to listing page
        return redirect()->route('news.show', $news->slug)
            ->with(['toast' => ['type' => 'success', 'title' => 'Updated Successfully', 'body' => 'News updated successfully']]);
    }

    public function destroy(News $news): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $news);

        $news->delete();
        return redirect()->route('admin.news.index')
            ->with(['toast' => ['type' => 'success', 'title' => 'Deleted Successfully', 'body' => 'News has been deleted permanently']]);
    }
}
