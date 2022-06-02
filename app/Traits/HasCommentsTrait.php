<?php


namespace App\Traits;

use App\Contracts\Commentator;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

trait HasCommentsTrait
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function comment(string $comment)
    {
        return $this->commentAsUser(auth()->user(), $comment);
    }

    public function commentAsUser(?Model $user, string $comment)
    {
        $commentClass = Comment::class;

        $comment = new $commentClass([
            'comment' => $comment,
            'is_approved' => ($user instanceof Commentator) ? ! $user->needsCommentApproval($this) : false,
            'user_id' => is_null($user) ? null : $user->getKey(),
            'commentable_id' => $this->getKey(),
            'commentable_type' => get_class(),
        ]);

        return $this->comments()->save($comment);
    }
}
