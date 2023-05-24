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
  <Link
    v-if="!item.children.length && item.visible"
    :class="[
      'group flex w-full items-center rounded-md py-2 px-3 text-sm font-medium text-gray-600',
      'hover:bg-gray-100',
      item.active ? 'text-gray-800 font-semibold' : 'text-gray-600 font-medium'
    ]"
    :href="item.href"
  >
    <component
      :is="item.icon"
      v-if="item.icon"
      :class="[
        'w-6 h-6 shrink-0 mr-2 group-hover:text-gray-600',
        item.active ? 'text-gray-600' : 'text-gray-400'
      ]"
    />
    <span v-if="!collapsed">{{ item.label }}</span>
  </Link>

  <Disclosure
    v-else-if="item.children.length && item.visible"
    v-slot="{open}"
    :default-open="hasActiveChild"
  >
    <DisclosureButton
      :class="[
        'group text-left flex w-full items-center rounded-md py-2 px-3 text-sm',
        'hover:bg-gray-100',
        open ? 'font-semibold text-gray-800' : 'text-gray-600 font-medium'
      ]"
    >
      <component
        :is="item.icon"
        v-if="item.icon"
        :class="[
          'w-6 h-6 shrink-0 mr-2 group-hover:text-gray-600',
          open ? 'text-gray-600' : 'text-gray-400'
        ]"
      />
      <span
        v-if="!collapsed"
        class="flex-1"
      >{{ item.label }}</span>

      <ChevronDownIcon
        v-if="!collapsed"
        :class="[
          'w-6 h-6 shrink-0',
          open? '-rotate-180 text-gray-600': 'text-gray-400'
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


