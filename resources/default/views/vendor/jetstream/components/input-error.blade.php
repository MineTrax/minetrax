@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm text-error-600']) }}>{{ $message }}</p>
@enderror
