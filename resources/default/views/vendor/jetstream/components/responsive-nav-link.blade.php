@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block pl-3 pr-4 py-2 border-l-4 border-primary text-base font-medium text-primary bg-primary focus:outline-none focus:text-primary focus:bg-primary focus:border-primary transition'
            : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-foreground hover:text-foreground hover:bg-foreground hover:border-foreground focus:outline-none focus:text-foreground focus:bg-foreground focus:border-foreground transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
