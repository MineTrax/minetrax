@component('mail::message')
# Hey {{ $name }}

<b>{{ $causer->name }}</b> commented on your post. Click below to view.

@component('mail::button', ['url' => route('post.show', $postId)])
View Comment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
