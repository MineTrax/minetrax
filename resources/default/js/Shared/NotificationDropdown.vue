<template>
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <button
            @click="error ? fetchNotifications() : null"
            class="ml-4 relative hover:cursor-pointer">
                <BellIcon :class="[
                    'w-5 h-5 transition-all duration-300',
                    notifications && notifications.length > 0
                        ? 'text-destructive'
                        : 'text-foreground hover:text-foreground'
                ]" />
                <span class="sr-only">{{ __("Notifications") }}</span>

                <!-- Enhanced notification badge with glow and bounce -->
                <div v-if="unreadCount > 0"
                    class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-destructive-foreground bg-destructive border-2 border-background rounded-full -top-2 -end-2 cursor-pointer animate-bounce shadow-lg"
                    style="animation-duration: 2s; box-shadow: 0 0 10px hsl(var(--destructive) / 0.6);">
                    {{ unreadCount }}
                </div>

                <!-- Pulsing ring effect for extra attention -->
                <div v-if="notifications && notifications.length > 0"
                    class="absolute -inset-1 bg-destructive rounded-full opacity-20 animate-ping"
                    style="animation-duration: 3s;"></div>
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-[420px] max-w-md p-0">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <div class="flex items-center gap-2">
                    <BellIcon class="w-5 h-5 text-foreground" />
                    <h2 class="font-semibold text-foreground">
                        {{ __("Unread Notifications") }}
                    </h2>
                </div>
                <inertia-link v-if="unreadCount > 0" as="button" :href="route('notification.mark-as-read')"
                    method="post"
                    class="text-sm text-primary hover:text-primary/80 font-medium transition-colors flex items-center"
                    :preserve-state="false">
                    <CheckCheckIcon class="w-4 h-4 mr-1" />
                    {{ __("Mark all read") }}
                </inertia-link>
            </div>

            <!-- Content -->
            <div class="max-h-[500px] overflow-y-auto">
                <!-- Loading Spinner -->
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                </div>

                <!-- Error Message -->
                <div v-else-if="error" class="p-4 text-center text-destructive">
                    {{ error }}
                </div>

                <!-- Notifications List -->
                <div v-else-if="notifications && notifications.length > 0">
                    <Notification v-for="notification in notifications" :key="notification.id"
                        :notification="notification" @marked-as-read="handleNotificationRead" />
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-12 px-4">
                    <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                        <BellIcon class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <p class="text-muted-foreground text-center">
                        {{ __("No new notifications") }}
                    </p>
                    <p class="text-muted-foreground text-sm text-center mt-1">
                        {{ __("You're all caught up!") }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t">
                <inertia-link :href="route('notification.index')"
                    class="block w-full text-center text-sm text-primary hover:text-primary/80 font-medium transition-colors">
                    {{ __("View all notifications") }}
                </inertia-link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup>
import Notification from '@/Components/Notification.vue'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import { BellIcon } from '@heroicons/vue/24/outline'
import { CheckCheckIcon } from 'lucide-vue-next'
import { onMounted, ref } from 'vue'

const loading = ref(true)
const error = ref(null)
const notifications = ref([])
const unreadCount = ref(null)

onMounted(() => {
    fetchNotifications()
})

const fetchNotifications = () => {
    loading.value = true
    error.value = null

    const routeToHit = route('notification.index', {
        'unread_only': true
    })

    axios.get(routeToHit)
        .then(response => {
            notifications.value = response.data.data
            unreadCount.value = response.data.total
        })
        .catch(e => {
            console.error('Error fetching notifications: ', e)
            error.value = 'Failed to load notifications'
            notifications.value = []
        })
        .finally(() => {
            loading.value = false
        })
}

const handleNotificationRead = (notificationId) => {
    // Remove the notification from the list when marked as read
    notifications.value = notifications.value.filter(n => n.id !== notificationId)
    unreadCount.value = unreadCount.value - 1

    if (notifications.value.length === 0) {
        fetchNotifications()
    }
}
</script>
