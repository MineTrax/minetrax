<template>
  <span
    class="inline-flex items-center gap-1 rounded-full border font-medium"
    :class="[sizeClass, colorClass]"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 16 16"
      fill="currentColor"
      class="shrink-0"
      :class="iconSizeClass"
    >
      <!-- General: newspaper -->
      <path
        v-if="value === 0"
        fill-rule="evenodd"
        d="M10 3a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h8a2 2 0 0 1-2-2V3ZM4 4h4v2H4V4Zm4 3.5H4V9h4V7.5ZM4 10.5h4V12H4v-1.5Z"
        clip-rule="evenodd"
      />
      <path
        v-if="value === 0"
        d="M13 13a1 1 0 0 1-1-1V9a1 1 0 1 1 2 0v3a1 1 0 0 1-1 1Z"
      />
      <!-- Announcement: megaphone -->
      <path
        v-if="value === 1"
        d="M13.407 2.59a.75.75 0 0 0-1.464.326c.863 3.878.863 7.29 0 11.168a.75.75 0 0 0 1.464.326c.916-4.116.916-7.703 0-11.82Z"
      />
      <path
        v-if="value === 1"
        fill-rule="evenodd"
        d="M4.348 5.888A18.9 18.9 0 0 0 9.5 4.865V11.134a18.88 18.88 0 0 0-5.152-1.022l-.063-.005A2.252 2.252 0 0 1 2 7.888v.264A2.25 2.25 0 0 1 4.285 5.893l.063-.005ZM2 12.658a1 1 0 0 1 .879-.99l.26.032c.58.076 1.155.176 1.723.3l-.2 1.24a.75.75 0 0 1-.834.607L2.5 13.688a.75.75 0 0 1-.5-.709v-.32Z"
        clip-rule="evenodd"
      />
      <!-- Event: calendar -->
      <path
        v-if="value === 2"
        fill-rule="evenodd"
        d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 7a1 1 0 0 0-1 1v4.5a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1h-7Z"
        clip-rule="evenodd"
      />
    </svg>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    type: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: "default",
        validator: (v) => ["sm", "default"].includes(v),
    },
});

const value = computed(() => props.type.value);
const label = computed(() => props.type.key);

const sizeClass = computed(() => {
    return props.size === "sm"
        ? "px-1.5 py-px text-[0.65rem]"
        : "px-2.5 py-0.5 text-xs";
});

const iconSizeClass = computed(() => {
    return props.size === "sm" ? "h-2.5 w-2.5" : "h-3 w-3";
});

const colorClass = computed(() => {
    switch (value.value) {
    case 1:
        return "bg-amber-500/10 text-amber-600 border-amber-500/20 dark:text-amber-400";
    case 2:
        return "bg-emerald-500/10 text-emerald-600 border-emerald-500/20 dark:text-emerald-400";
    default:
        return "bg-sky-500/10 text-sky-600 border-sky-500/20 dark:text-sky-400";
    }
});
</script>
