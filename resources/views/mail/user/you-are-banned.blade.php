@component('mail::message')
# Hey {{ $name }}

You are banned by <b>{{ $causer->name }}</b>.
Contact any staff member if you think this was a mistake.

@component('mail::button', ['url' => route('staff.index')])
Contact Staff
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
