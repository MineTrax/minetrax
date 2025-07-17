<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: String,
    as: String,
    btnClass: String,
    openInNewTab: {
        type: Boolean,
        default: false,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    return props.active
        ? 'block px-4 py-2 text-sm leading-5 text-accent-foreground bg-accent/50 hover:bg-accent hover:text-accent-foreground focus:outline-none focus:bg-accent focus:text-accent-foreground transition duration-150 ease-in-out'
        : 'block px-4 py-2 text-sm leading-5 text-foreground hover:bg-accent hover:text-accent-foreground focus:outline-none focus:bg-accent focus:text-accent-foreground transition duration-150 ease-in-out';
});

const buttonClasses = computed(() => {
    return props.active
        ? 'block w-full px-4 py-2 text-sm leading-5 text-accent-foreground text-left bg-accent/50 hover:bg-accent hover:text-accent-foreground focus:outline-none focus:bg-accent focus:text-accent-foreground transition duration-150 ease-in-out'
        : 'block w-full px-4 py-2 text-sm leading-5 text-foreground text-left hover:bg-accent hover:text-accent-foreground focus:outline-none focus:bg-accent focus:text-accent-foreground transition duration-150 ease-in-out';
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" type="submit" :class="[buttonClasses, btnClass]">
            <slot />
        </button>

        <Link v-else-if="as != 'button' && !openInNewTab" :href="href" :class="[classes, btnClass]" prefetch>
        <slot />
        </Link>
        <a v-else target="_blank" :href="href" :class="[classes, btnClass]">
            <slot />
        </a>
    </div>
</template>
