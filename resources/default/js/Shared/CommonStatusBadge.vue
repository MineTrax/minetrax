<script setup>
import Badge from"@/Components/Badge.vue";
import { computed } from"vue";
import { startCase } from"lodash";

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
});

const colorClass = computed(() => {
 switch (props.status) {
 case"pending":
 return"bg-amber-500/15 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-500/30 dark:border-amber-500/30";
 case"inprogress":
 case"running":
 return"bg-sky-500/15 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 border border-sky-500/30 dark:border-sky-500/30";
 case"approved":
 case"completed":
 case"green":
 return"bg-success/15 text-success border border-success/30";
 case"rejected":
 case"failed":
 case"ban":
 case"active":
 case"permanent":
 case"red":
 return"bg-destructive/15 text-destructive border border-destructive/30";
 case"onhold":
 case"deferred":
 return"bg-orange-500/15 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 border border-orange-500/30 dark:border-orange-500/30";
 case"warn":
 return"bg-yellow-500/15 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 border border-yellow-500/30 dark:border-yellow-500/30";
 case"kick":
 return"bg-slate-500/15 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 border border-slate-500/30 dark:border-slate-500/30";
 default:
 return"bg-muted-foreground/15 text-muted-foreground border border-muted-foreground/30";
 }
});
</script>

<template>
 <Badge :color-class="colorClass">
 {{ value ? value : startCase(status) }}
 </Badge>
</template>
