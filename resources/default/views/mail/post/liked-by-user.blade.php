@component('mail::message')
# {{ __("Hey :name", ['name' => $name]) }}

<b>{{ $causer->name  }}</b> {{ __("liked your post. Click below for more information.") }}

@component('mail::button', ['url' => route('post.show', $postId)])
{{ __("View Post") }}
@endcomponent

{{ __("Thanks") }},<br>
{{ config('app.name') }}
@endcomponent
