<script setup>
import { NavigationMenuLink, useForwardPropsEmits } from "reka-ui";

const props = defineProps({
  active: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
});
const emits = defineEmits(["select"]);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <NavigationMenuLink v-bind="forwarded">
    <slot />
  </NavigationMenuLink>
</template>
