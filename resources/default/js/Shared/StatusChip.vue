<script setup>
import { Badge } from "@/Components/ui/badge";
import { LoaderCircleIcon } from "lucide-vue-next";

// A status pill with a leading icon, or a spinner while something is still happening. The tone
// names a meaning rather than a colour, so a caller says "warning" and the theme decides.
defineProps({
    // success | warning | danger | info | muted
    tone: { type: String, default: "muted" },
    // A lucide component. Ignored while `loading` is set, which takes the icon's place.
    icon: { type: [Object, Function], default: null },
    loading: { type: Boolean, default: false },
    // Used when no slot content is given.
    label: { type: String, default: "" },
});
</script>

<template>
  <Badge
    :variant="tone"
    class="gap-1.5 rounded-full font-medium whitespace-nowrap"
  >
    <LoaderCircleIcon
      v-if="loading"
      class="h-3.5 w-3.5 shrink-0 animate-spin"
      aria-hidden="true"
    />
    <component
      :is="icon"
      v-else-if="icon"
      class="h-3.5 w-3.5 shrink-0"
      aria-hidden="true"
    />
    <slot>{{ label }}</slot>
  </Badge>
</template>
