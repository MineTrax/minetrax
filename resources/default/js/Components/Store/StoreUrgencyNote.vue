<script setup>
import { computed, onUnmounted, ref } from "vue";
import { useTranslations } from "@/Composables/useTranslations";
import { FlameIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

/**
 * Scarcity and deadline, as one line of warning-coloured text.
 *
 * Both facts were already on the wire and neither was ever shown: a package limited to twenty
 * lifetime sales looked identical to an unlimited one until the moment it flipped to "Out of
 * Stock", and one that stops selling on Sunday gave no hint of it.
 *
 * A line rather than chips. The card already carries a discount tag, a featured tag and a price;
 * two more pills turn the warning into just another attribute, and a row of eight pills is read
 * as decoration and skipped entirely.
 */
const stockLeft = computed(() => props.storePackage.stock_remaining ?? null);

const now = ref(Date.now());
let timer = null;

const endsAt = computed(() => (props.storePackage.available_until
    ? new Date(props.storePackage.available_until).getTime()
    : null));

// Only counted down when the end is close enough to matter. A package that closes in three months
// gets no ticking clock — that is a fact, not a deadline, and a permanent countdown on every card
// teaches shoppers to ignore all of them.
const COUNTDOWN_WINDOW_MS = 7 * 24 * 60 * 60 * 1000;

const msRemaining = computed(() => (endsAt.value === null ? null : endsAt.value - now.value));

const showCountdown = computed(
    () => msRemaining.value !== null && msRemaining.value > 0 && msRemaining.value <= COUNTDOWN_WINDOW_MS
);

// Only a package with an end date gets a timer at all, and it ticks every half minute rather than
// every second: a grid of twenty cards would otherwise carry twenty intervals, nineteen of them
// recomputing a number that never changes.
if (typeof window !== "undefined" && endsAt.value !== null) {
    timer = setInterval(() => {
        now.value = Date.now();
    }, 30000);
}

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
});

const countdownLabel = computed(() => {
    const remaining = msRemaining.value;

    if (remaining === null || remaining <= 0) {
        return null;
    }

    const minutes = Math.floor(remaining / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days >= 1) {
        return days === 1 ? __("ends in 1 day") : __("ends in :count days", { count: days });
    }
    if (hours >= 1) {
        return hours === 1 ? __("ends in 1 hour") : __("ends in :count hours", { count: hours });
    }
    return __("ends in :count min", { count: Math.max(1, minutes) });
});

// Joined into one sentence rather than stacked, so a package that is both nearly gone and nearly
// over still costs a single line.
const parts = computed(() => {
    const list = [];

    if (stockLeft.value !== null) {
        list.push(stockLeft.value === 1
            ? __("only 1 left")
            : __("only :count left", { count: stockLeft.value }));
    }

    if (showCountdown.value) {
        list.push(countdownLabel.value);
    }

    return list;
});
</script>

<template>
  <p
    v-if="parts.length"
    class="flex items-center gap-1.5 text-xs font-medium text-orange-600 dark:text-orange-400"
  >
    <FlameIcon class="w-3.5 h-3.5 shrink-0" />
    <span class="first-letter:uppercase">{{ parts.join(" · ") }}</span>
  </p>
</template>
