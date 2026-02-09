@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-foreground focus:border-primary focus:ring-3 focus:ring-indigo-200/50 rounded-md shadow-sm']) !!}>
