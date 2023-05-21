<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostCommentedByUser;
use App\Notifications\PostLikedByUser;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function __construct(GeneralSettings $settings)
    {
        if (!$settings->enable_status_feed) {
            abort(403);
        }
    }

    public function index()
    {
        $posts = null;
        if (auth()->user()) {
            $posts = Post::with([
                'loveReactant.reactions' => function ($q) {
                    $q->where('reacter_id', auth()->user()->id);
                },
                'loveReactant.reactionTotal',
                'user:id,name,username,profile_photo_path,verified_at,settings'
            ])
                ->withCount('comments')
                ->orderByDesc('id')
                ->cursorPaginate(5);
        } else {
            $posts = Post::with([
                'loveReactant.reactionTotal',
                'user:id,name,username,profile_photo_path,verified_at,settings'
            ])
                ->withCount('comments')
                ->orderByDesc('id')
                ->cursorPaginate(5);
        }
        return $posts;
    }

    public function indexForUser(User $user)
    {
        $posts = null;
        if (auth()->user()) {
            $posts = $user->posts()->with([
                'loveReactant.reactions' => function ($q) {
                    $q->where('reacter_id', auth()->user()->id);
                },
                'loveReactant.reactionTotal',
                'user:id,name,username,profile_photo_path,verified_at,settings'
            ])
                ->withCount('comments')
                ->orderByDesc('id')
                ->cursorPaginate(5);
        } else {
            $posts = $user->posts()->with([
                'loveReactant.reactionTotal',
                'user:id,name,username,profile_photo_path,verified_at,settings'
            ])
                ->withCount('comments')
                ->orderByDesc('id')
                ->cursorPaginate(5);
        }
        return $posts;
    }

    public function show(Post $post)
    {
        if(auth()->user()) {
           $post->load([
               'loveReactant.reactions' => function ($q) {
                   $q->where('reacter_id', auth()->user()->id);
               },
               'loveReactant.reactionTotal',
               'user:id,name,username,profile_photo_path,verified_at,settings',
           ])->loadCount('comments');
        } else {
            $post->load([
                'loveReactant.reactionTotal',
                'user:id,name,username,profile_photo_path,verified_at,settings'
            ])->loadCount('comments');
        }

        return Inertia::render('Post/ShowPost', [
            'post' => $post
        ]);
    }

    public function store(CreatePostRequest $request)
    {
        $post = $request->user()->posts()->create([
            'body' => $request->body
        ]);

        // @TODO: This may create some vulnerability if people can upload some executable file, please check
        if ($request->hasFile('media')) {
                $post->addAllMediaFromRequest()->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('posts');
                });
        }

        if ($request->wantsJson()) {
            $post->load([
                'media',
                'loveReactant.reactions' => function ($q) {
                    $q->where('reacter_id', auth()->user()->id);
                },
                'loveReactant.reactionTotal',
                'user:id,name,username,profile_photo_path,verified_at,settings'])
                ->loadCount('comments');

            return response()->json([
                'status' => 'ok',
                'data' => $post
            ]);
        }

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Post Successful'), 'body' => __('Post has been created successfully')]]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();
        return redirect()->route('home')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Post has been deleted successfully')]]);
    }

    public function likePost(Request $request, Post $post): \Illuminate\Http\JsonResponse
    {
        try {
            $reacterFacade = $request->user()->viaLoveReacter();
            if ($reacterFacade->hasNotReactedTo($post, 'Like')) {
                $reacterFacade->reactTo($post, 'Like');

                // Send notification only different user
                if ($post->user->id != $request->user()->id) {
                    $post->user->notify(new PostLikedByUser($post->id, $request->user()));
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $exception) {
            \Log::error($exception);
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function unlikePost(Request $request, Post $post): \Illuminate\Http\JsonResponse
    {
        try {
            $reacterFacade = $request->user()->viaLoveReacter();
            if ($reacterFacade->hasReactedTo($post, 'Like')) {
                $reacterFacade->unreactTo($post, 'Like');
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $exception) {
            \Log::error($exception);
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function indexComment(Post $post)
    {
        $comments = $post->comments()
            ->with('commentator:id,username,name,profile_photo_path,verified_at,settings')
            ->orderByDesc('id')
            ->cursorPaginate(5);

        return $comments;
    }

    public function postComment(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'required|max:250'
        ]);

        $comment = $post->comment($request->comment);

        if ($post->user->id != $request->user()->id) {
            $post->user->notify(new PostCommentedByUser($post->id, $comment->id, $request->user()));
        }

        return response()->json([
            'status' => 'ok',
            'data' => $comment->load('commentator:id,username,name,profile_photo_path,verified_at,settings')
        ]);
    }

    public function deleteComment(Post $post, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Comment has been deleted successfully')]]);
    }
}
