<template>
    <NavigationMenuTrigger>
        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex items-center text-sm font-medium">
            {{ $page.props.auth.user.name }}
            <img class="h-8 w-8 ml-2 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                :alt="$page.props.auth.user.name">
        </button>

        <button v-else type="button" class="flex items-center text-sm font-medium">
            {{ $page.props.auth.user.name }}
            <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </NavigationMenuTrigger>
    <NavigationMenuContent>
        <NavigationMenuLink as-child>
            <ul class="min-w-[250px] m-3 p-2 border border-border rounded">
                <div v-if="canShowAdminSidebar" class="block px-4 py-2 text-xs text-muted-foreground">
                    {{ __("Staff") }}
                </div>
                <jet-dropdown-link v-if="canShowAdminSidebar" class="text-sm" :href="route('admin.dashboard')">
                    {{ __("Admin Section") }}
                </jet-dropdown-link>

                <div v-if="canShowAdminSidebar" class="border-t border-border my-2" />

                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-muted-foreground">
                    {{ __("Manage Account") }}
                </div>

                <jet-dropdown-link class="text-sm" :href="route('user.public.get', $page.props.auth.user.username)">
                    {{ __("Public Profile") }}
                </jet-dropdown-link>

                <jet-dropdown-link :href="route('profile.show')">
                    {{ __("Edit Profile") }}
                </jet-dropdown-link>

                <jet-dropdown-link :href="route('recruitment-submission.index')">
                    {{ __("My Applications") }}
                </jet-dropdown-link>

                <jet-dropdown-link :href="route('linked-player.list')">
                    {{ __("Linked Players") }}
                </jet-dropdown-link>

                <jet-dropdown-link v-if="$page.props?.pluginSettings?.playerPasswordResetEnabled"
                    :href="route('reset-player-password.show')">
                    {{ __("Reset Player Password") }}
                </jet-dropdown-link>

                <jet-dropdown-link v-if="$page.props.playerSkinChangerEnabled" :href="route('change-player-skin.show')">
                    {{ __("Change Player Skin") }}
                </jet-dropdown-link>

                <div class="border-t border-border my-2" />

                <!-- Authentication -->
                <form @submit.prevent="emit('logout')">
                    <jet-dropdown-link as="button" btn-class="font-semibold">
                        {{ __("Logout") }}
                    </jet-dropdown-link>
                </form>
            </ul>
        </NavigationMenuLink>
    </NavigationMenuContent>
</template>

<script setup>
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';
import {
    NavigationMenuContent,
    NavigationMenuLink,
    NavigationMenuTrigger
} from '@/Components/ui/navigation-menu';

const emit = defineEmits(['logout']);

defineProps({
    canShowAdminSidebar: {
        type: Boolean,
        default: false,
    },
});
</script>
