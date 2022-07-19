@component('mail::message')
# {{ __("Hey :name", ['name' => $name]) }}

{{ __("You are banned by") }} <b>{{ $causer->name }}</b>.
{{ __("Please contact any staff member if you think this was a mistake.") }}

@component('mail::button', ['url' => route('staff.index')])
{{ __("Contact Staff") }}
@endcomponent

{{ __("Thanks") }},<br>
{{ config('app.name') }}
@endcomponent
