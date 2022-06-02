@component('mail::message')
# Hey {{ $name }}

You are muted by <b>{{ $causer->name }}</b>.
Please contact any staff member if you think this was a mistake.

@component('mail::button', ['url' => route('staff.index')])
Contact Staff
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
