<button {{ $attributes->merge(['type'=>'button','class'=>'inline-flex items-center justify-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 focus:outline-hidden focus:border-destructive focus:ring focus:ring-destructive/30 active:bg-destructive/80 disabled:opacity-25 transition']) }}>
 {{ $slot }}
</button>
