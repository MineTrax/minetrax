@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-secondary-700']) }}>
    {{ $value ?? $slot }}
</label>
