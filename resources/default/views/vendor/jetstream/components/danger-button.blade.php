<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-error-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-error-500 focus:outline-none focus:border-error-700 focus:ring focus:ring-red-200 active:bg-error-600 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
