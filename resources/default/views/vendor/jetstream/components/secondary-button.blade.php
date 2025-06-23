<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-foreground rounded-md font-semibold text-xs text-foreground uppercase tracking-widest shadow-sm hover:text-foreground focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-foreground active:bg-foreground disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
