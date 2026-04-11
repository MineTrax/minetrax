@if ($errors->any())
 <div {{ $attributes }}>
 <div class="font-medium text-destructive">{{ __('Whoops! Something went wrong.') }}</div>

 <ul class="mt-3 list-disc list-inside text-sm text-destructive">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
@endif
