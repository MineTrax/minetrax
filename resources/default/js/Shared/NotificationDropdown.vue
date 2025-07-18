<template>
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <button class="ml-4 relative hover:cursor-pointer">
                <BellIcon :class="[
                    'w-5 h-5 transition-all duration-300',
                    notifications && notifications.length > 0
                        ? 'text-destructive'
                        : 'text-foreground hover:text-foreground'
                ]" />
                <span class="sr-only">{{ __("Notifications") }}</span>

                <!-- Enhanced notification badge with glow and bounce -->
                <div v-if="notifications && notifications.length > 0"
                    class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-destructive-foreground bg-destructive border-2 border-background rounded-full -top-2 -end-2 cursor-pointer animate-bounce shadow-lg"
                    style="animation-duration: 2s; box-shadow: 0 0 10px hsl(var(--destructive) / 0.6);">
                    {{ notifications.length }}
                </div>

                <!-- Pulsing ring effect for extra attention -->
                <div v-if="notifications && notifications.length > 0"
                    class="absolute -inset-1 bg-destructive rounded-full opacity-20 animate-ping"
                    style="animation-duration: 3s;"></div>
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-[420px] max-w-md">
            <div class="p-4 pb-2">
                <DropdownMenuLabel class="flex justify-between items-center px-0 pb-2">
                    <div class="flex items-center gap-0.5">
                        <BellIcon class="w-5 h-5 text-foreground" />
                        <span class="text-sm font-medium text-foreground">
                            {{ __("Notifications") }}
                        </span>
                    </div>
                    <inertia-link as="button" :href="route('notification.mark-as-read')" method="post"
                        :preserve-state="false" class="text-xs text-primary hover:underline font-normal">
                        {{ __("Mark all as read") }}
                    </inertia-link>
                </DropdownMenuLabel>

                <!-- Loading Spinner -->
                <div v-if="loading" class="flex p-4 justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                </div>

                <error-message v-if="error">
                    {{ error }}
                </error-message>

                <div class="flex flex-col text-sm text-foreground mt-2 h-[450px] overflow-y-auto space-y-1">
                    <Notification v-for="notification in notifications" :key="notification.id"
                        :notification="notification" />
                    <div v-if="!loading && notifications.length <= 0" :key="999999999"
                        class="flex items-center justify-center italic text-muted-foreground p-4">
                        {{ __("No new notifications to show.") }}
                    </div>
                </div>

                <div class="flex justify-center text-primary text-xs mt-2 hover:underline cursor-pointer">
                    <inertia-link as="a" :href="route('notification.index')">
                        {{ __("View All") }}
                    </inertia-link>
                </div>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup>
import ErrorMessage from '@/Components/ErrorMessage.vue'
import Notification from '@/Components/Notification.vue'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import { BellIcon } from '@heroicons/vue/24/outline'
import { onMounted, ref } from 'vue'

const loading = ref(true)
const error = ref(null)
const notifications = ref([])

onMounted(() => {
    const routeToHit = route('notification.index', {
        'unread_only': true
    })

    axios.get(routeToHit)
        .then(response => {
            notifications.value = response.data.data
        })
        .catch(e => {
            console.log('Error fetching notifications: ', e)
            notifications.value = []
        })
        .finally(() => {
            loading.value = false
        })
})
</script>
