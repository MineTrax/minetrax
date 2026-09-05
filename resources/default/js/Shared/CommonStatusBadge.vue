<script setup>
import Badge from "@/Components/Badge.vue";
import { computed } from "vue";
import { startCase } from "lodash";

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: false,
        default: null,
    },
    // Overrides the tone the status would otherwise get. The same word means different things in
    // different places: an "active" ban is a warning, an "active" grant is the happy path.
    tone: {
        type: String,
        required: false,
        default: null,
    },
});

const TONE_CLASSES = {
    success: "bg-success/15 text-success border border-success/30",
    warning: "bg-amber-500/15 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-500/30 dark:border-amber-500/30",
    info: "bg-sky-500/15 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 border border-sky-500/30 dark:border-sky-500/30",
    danger: "bg-destructive/15 text-destructive border border-destructive/30",
    orange: "bg-orange-500/15 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 border border-orange-500/30 dark:border-orange-500/30",
    yellow: "bg-yellow-500/15 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 border border-yellow-500/30 dark:border-yellow-500/30",
    slate: "bg-slate-500/15 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 border border-slate-500/30 dark:border-slate-500/30",
    muted: "bg-muted-foreground/15 text-muted-foreground border border-muted-foreground/30",
};

const STATUS_TONES = {
    pending: "warning",
    inprogress: "info",
    running: "info",
    approved: "success",
    completed: "success",
    paid: "success",
    delivered: "success",
    green: "success",
    rejected: "danger",
    failed: "danger",
    ban: "danger",
    active: "danger",
    permanent: "danger",
    refunded: "danger",
    chargeback: "danger",
    red: "danger",
    onhold: "orange",
    deferred: "orange",
    partial: "orange",
    partially_refunded: "orange",
    warn: "yellow",
    kick: "slate",
    cancelled: "slate",
    expired: "slate",
    revoked: "slate",
};

const colorClass = computed(() => {
    const tone = props.tone ?? STATUS_TONES[props.status] ?? "muted";

    return TONE_CLASSES[tone] ?? TONE_CLASSES.muted;
});
</script>

<template>
  <Badge :color-class="colorClass">
    {{ value ? value : startCase(status) }}
  </Badge>
</template>
