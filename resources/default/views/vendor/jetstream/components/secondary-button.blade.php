<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-secondary-300 rounded-md font-semibold text-xs text-secondary-700 uppercase tracking-widest shadow-sm hover:text-secondary-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-secondary-800 active:bg-secondary-50 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
