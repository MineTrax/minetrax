@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-foreground']) }}>
    {{ $value ?? $slot }}
</label>
