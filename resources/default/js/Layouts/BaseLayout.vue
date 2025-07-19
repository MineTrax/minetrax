<script setup>
import { ref } from 'vue'
import JetBanner from '@/Jetstream/Banner.vue';
import Toast from '@/Components/Toast.vue';
import Icon from '@/Components/Icon.vue';
import AppHead from '@/Components/AppHead.vue';
import CookieConsent from '@/Components/CookieConsent.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    hideFooter: {
        type: Boolean,
        default: false,
    }
})

const title = ref('MineTrax')
</script>

<template>
    <AppHead :title="title" />

    <div>
        <JetBanner />
        <Toast :toast="$page.props.toast" :popstate="$page.props.popstate" />

        <Link v-if="$page.props.isImpersonating" v-tippy :title="__('Leave Impersonation')" as="a"
            :href="route('admin.impersonate.leave')"
            class="fixed flex p-2 text-white bg-error-500 rounded-full bottom-4 right-4 hover:bg-error-700 z-50">
        <icon name="ban" class="w-8 h-8" />
        </Link>

        <div class="min-h-screen" :class="{ 'border-4 border-error-500': $page.props.isImpersonating }">
            <!-- Page Content -->
            <main class="min-h-[80vh]">
                <slot />
            </main>
        </div>
    </div>

    <CookieConsent />
</template>
