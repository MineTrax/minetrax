<template>
    <NavigationMenuTrigger>
        {{ __(title) }}
    </NavigationMenuTrigger>
    <NavigationMenuContent>
        <NavigationMenuLink as-child>
            <ul class="min-w-[250px] m-3 p-2 border border-border rounded">
                <jet-dropdown-link v-for="item in items" :key="item.key" class="text-sm whitespace-nowrap"
                    :href="route(item.route, item.route_params ?? null)" :open-in-new-tab="item.is_open_in_new_tab">
                    {{ __(item.title) }}
                </jet-dropdown-link>
            </ul>
        </NavigationMenuLink>
    </NavigationMenuContent>
</template>

<script setup>
import {
    NavigationMenuContent,
    NavigationMenuLink,
    NavigationMenuTrigger
} from '@/Components/ui/navigation-menu';
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    items: {
        type: Array,
        required: true
    }
});
</script>
