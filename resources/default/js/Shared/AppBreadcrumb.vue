<script setup>
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb'
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { cn } from "@/lib/utils";

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => []
    },
    class: { type: null, required: false },
    breadcrumbClass: { type: null, required: false },
})

const page = usePage();
const isEnabled = true; // page.props.generalSettings.enable_breadcrumb;

/**
 * Breadcrumb items structure:
 * [
 *   { text: 'Home', url: '/', current: false },
 *   { text: 'News', url: '/news', current: false },
 *   { text: 'Article Title', current: true }
 * ]
 */
</script>

<template>
    <div :class="cn('mt-6', props.class)">
        <Breadcrumb v-if="isEnabled" :class="cn('mx-auto px-3 md:px-10 max-w-screen-2xl', props.breadcrumbClass)">
            <BreadcrumbList>
                <template v-for="(item, index) in items" :key="index">
                    <BreadcrumbItem>
                        <BreadcrumbLink v-if="!item.current && item.url" :as="Link" :href="item.url">
                            {{ item.text }}
                        </BreadcrumbLink>
                        <BreadcrumbPage v-else-if="item.current">
                            {{ item.text }}
                        </BreadcrumbPage>
                        <span v-else class="font-normal text-muted-foreground transition-colors hover:text-foreground">
                             {{ item.text }}
                        </span>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator v-if="index < items.length - 1" />
                </template>
            </BreadcrumbList>
        </Breadcrumb>
    </div>
</template>
