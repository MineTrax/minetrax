@component('mail::message')
# Hey {{ $name }}

<b>{{ $causer->name  }}</b> liked your post. Click below for more information.

@component('mail::button', ['url' => route('post.show', $postId)])
View Post
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
