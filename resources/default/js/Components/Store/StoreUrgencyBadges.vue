<script setup>
import { computed, onUnmounted, ref } from "vue";
import { useTranslations } from "@/Composables/useTranslations";
import { AlarmClockIcon, FlameIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
    // The package page has room for full-size badges; a card in a grid does not.
    size: {
        type: String,
        default: "sm",
    },
});

/**
 * Both facts were already on the wire and neither was ever shown: a package limited to twenty
 * lifetime sales looked identical to an unlimited one until the moment it flipped to "Out of
 * Stock", and one that stops selling on Sunday gave no hint of it. A deadline a shopper cannot
 * see cannot bring the decision forward.
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
        return days === 1 ? __("Ends in 1 day") : __("Ends in :count days", { count: days });
    }
    if (hours >= 1) {
        return hours === 1 ? __("Ends in 1 hour") : __("Ends in :count hours", { count: hours });
    }
    return __("Ends in :count min", { count: Math.max(1, minutes) });
});

const badgeClass = computed(() => (props.size === "lg"
    ? "inline-flex items-center gap-1 px-3 py-1 text-sm font-medium rounded-lg"
    : "inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded"));

const iconClass = computed(() => (props.size === "lg" ? "w-4 h-4" : "w-3 h-3"));

const hasAny = computed(() => stockLeft.value !== null || showCountdown.value);
</script>

<template>
  <div
    v-if="hasAny"
    class="flex flex-wrap gap-2"
  >
    <span
      v-if="stockLeft !== null"
      :class="[badgeClass, 'bg-orange-500/10 text-orange-600 dark:text-orange-400']"
    >
      <FlameIcon :class="iconClass" />
      {{ stockLeft === 1 ? __("Only 1 left") : __("Only :count left", { count: stockLeft }) }}
    </span>

    <span
      v-if="showCountdown"
      :class="[badgeClass, 'bg-destructive/10 text-destructive']"
    >
      <AlarmClockIcon :class="iconClass" />
      {{ countdownLabel }}
    </span>
  </div>
</template>
