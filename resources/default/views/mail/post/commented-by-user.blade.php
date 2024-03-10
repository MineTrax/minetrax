@component('mail::message')
# {{ __("Hey :name", ['name' => $name]) }}

<b>{{ $causer->name }}</b> {{ __("commented on your post. Click below to view.") }}

@component('mail::button', ['url' => route('post.show', $postId)])
{{ __("View Comment") }}
@endcomponent

{{ __("Thanks") }},<br>
{{ config('app.name') }}
@endcomponent
