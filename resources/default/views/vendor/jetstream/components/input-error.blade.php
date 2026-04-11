@props(['for'])

@error($for)
 <p {{ $attributes->merge(['class'=>'text-sm text-destructive']) }}>{{ $message }}</p>
@enderror
