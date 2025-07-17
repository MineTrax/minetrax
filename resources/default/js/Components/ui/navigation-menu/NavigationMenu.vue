<script setup>
import { reactiveOmit } from "@vueuse/core";
import { NavigationMenuRoot, useForwardPropsEmits } from "reka-ui";
import { cn } from "@/lib/utils";
import NavigationMenuViewport from "./NavigationMenuViewport.vue";

const props = defineProps({
  modelValue: { type: String, required: false },
  defaultValue: { type: String, required: false },
  dir: { type: String, required: false },
  orientation: { type: String, required: false },
  delayDuration: { type: Number, required: false },
  skipDelayDuration: { type: Number, required: false },
  disableClickTrigger: { type: Boolean, required: false },
  disableHoverTrigger: { type: Boolean, required: false },
  disablePointerLeaveClose: { type: Boolean, required: false },
  unmountOnHide: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const emits = defineEmits(["update:modelValue"]);

const delegatedProps = reactiveOmit(props, "class");

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <NavigationMenuRoot
    v-bind="forwarded"
    :class="
      cn(
        'relative z-10 flex flex-1 items-center justify-center',
        props.class,
      )
    "
  >
    <slot />
    <NavigationMenuViewport class="NavigationMenuViewport" />
  </NavigationMenuRoot>
</template>

<style>
.NavigationMenuViewport {
  left: var(--reka-navigation-menu-viewport-left);
}
</style>
