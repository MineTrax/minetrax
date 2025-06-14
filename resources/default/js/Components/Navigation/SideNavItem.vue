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
      'hover:bg-surface-100 dark:hover:bg-surface-900',
      item.active ? 'text-secondary-800 font-semibold dark:text-secondary-200' : 'text-secondary-600 dark:text-secondary-400 font-medium'
    ]"
    :href="item.href"
    :target="item.newtab ? '_blank' : null"
  >
    <component
      :is="item.icon"
      v-if="item.icon"
      :class="[
        'w-6 h-6 shrink-0 mr-2 group-hover:text-secondary-600 dark:group-hover:text-secondary-400',
        item.active ? 'text-secondary-600 dark:text-secondary-400' : 'text-secondary-400 dark:text-secondary-600'
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
        'hover:bg-surface-100 dark:hover:bg-surface-900',
        open ? 'font-semibold text-secondary-800 dark:text-secondary-200' : 'text-secondary-600 dark:text-secondary-400 font-medium'
      ]"
    >
      <component
        :is="item.icon"
        v-if="item.icon"
        :class="[
          'w-6 h-6 shrink-0 mr-2 group-hover:text-secondary-600 dark:group-hover:text-secondary-400',
          open ? 'text-secondary-600 dark:text-secondary-400' : 'text-secondary-400 dark:text-secondary-600'
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
          open? '-rotate-180 text-secondary-600 dark:text-secondary-400': 'text-secondary-400 dark:text-secondary-600'
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


