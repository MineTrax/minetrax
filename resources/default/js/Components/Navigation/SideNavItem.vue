<script setup>
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
} from '@headlessui/vue';
import {ChevronDownIcon} from '@heroicons/vue/20/solid';
import {computed} from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    collapsed: Boolean,
});

const hasActiveChild = computed(() => {
    function hasActiveItem(items) {
        return items.some(item => item.active || hasActiveItem(item.children));
    }
    return hasActiveItem(props.item.children);
});

</script>

<template>
  <component
    :is="item.newtab ? 'a' : Link"
    v-if="!item.children.length && item.visible"
    :class="[
      'group flex w-full items-center rounded-md py-2 px-3 text-sm font-medium',
      'hover:bg-accent hover:text-accent-foreground',
      item.active ? 'text-primary font-semibold' : 'text-sidebar-foreground font-medium'
    ]"
    :href="item.href"
    :target="item.newtab ? '_blank' : null"
  >
    <component
      :is="item.icon"
      v-if="item.icon"
      :class="[
        'w-6 h-6 shrink-0 mr-2',
        item.active ? 'text-primary' : 'text-sidebar-foreground'
      ]"
    />
    <span v-if="!collapsed">{{ __(item.label) }}</span>
  </component>

  <Disclosure
    v-else-if="item.children.length && item.visible"
    v-slot="{open}"
    :default-open="hasActiveChild"
  >
    <DisclosureButton
      :class="[
        'group text-left flex w-full items-center rounded-md py-2 px-3 text-sm',
        'hover:bg-accent hover:text-accent-foreground',
        open ? 'font-semibold text-primary' : 'text-sidebar-foreground font-medium'
      ]"
    >
      <component
        :is="item.icon"
        v-if="item.icon"
        :class="[
          'w-6 h-6 shrink-0 mr-2',
          open ? 'text-primary' : 'text-sidebar-foreground'
        ]"
      />
      <span
        v-if="!collapsed"
        class="flex-1"
      >{{ __(item.label) }}</span>

      <ChevronDownIcon
        v-if="!collapsed"
        :class="[
          'w-6 h-6 shrink-0',
          open? '-rotate-180 text-primary': 'text-sidebar-foreground'
        ]"
      />
    </DisclosureButton>
    <DisclosurePanel
      v-if="!collapsed"
      class="ml-4"
    >
      <SideNavItem
        v-for="child in item.children"
        :key="child.label"
        :item="child"
      />
    </DisclosurePanel>
  </Disclosure>
</template>


