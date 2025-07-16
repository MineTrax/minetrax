<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import JetResponsiveNavLink from "@/Jetstream/ResponsiveNavLink.vue";
import Search from "@/Shared/Search.vue";
import ColorThemeToggle from "@/Components/ColorThemeToggle.vue";
import NavDynamicItem from "@/Components/Navigation/NavDynamicItem.vue";
import NavDynamicItemResponsive from "@/Components/Navigation/NavDynamicItemResponsive.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import {
    Sheet,
    SheetContent,
    SheetTrigger,
} from "@/Components/ui/sheet";
import { MenuIcon } from "lucide-vue-next";

// Composables
const { isStaff } = useAuthorizable();
const page = usePage();

// Reactive data
const leftItems = ref(window._customnav.data?.left ?? []);
const middleItems = ref(window._customnav.data?.middle ?? []);
const rightItems = ref(window._customnav.data?.right ?? []);

// Computed properties
const canShowAdminSidebar = computed(() => {
    return isStaff(page.props.auth.user);
});

const isStickyHeader = computed(() => {
    return page.props.generalSettings.enable_sticky_header_menu;
});

// Methods
const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <nav class="bg-sidebar shadow z-40 w-full" :class="{
        'sticky top-0': isStickyHeader,
    }">
        <!-- Primary Navigation Menu -->
        <div class="px-4 mx-auto max-w-11xl md:px-6 lg:px-8">
            <div class="flex justify-between font-semibold h-14">
                <div class="left-menu-items flex space-x-4">
                    <NavDynamicItem v-for="item in leftItems" :key="item.key"
                        :can-show-admin-sidebar="canShowAdminSidebar" :item="item" @logout="logout" />
                </div>

                <div class="middle-menu-items flex space-x-4">
                    <!--              Add middle items here-->
                    <NavDynamicItem v-for="item in middleItems" :key="item.key"
                        :can-show-admin-sidebar="canShowAdminSidebar" :item="item" @logout="logout" />
                </div>

                <div class="right-menu-items flex space-x-4">
                    <NavDynamicItem v-for="item in rightItems" :key="item.key"
                        :can-show-admin-sidebar="canShowAdminSidebar" :item="item" @logout="logout" />
                </div>

                <!-- Mobile Menu -->
                <div class="flex items-center -mr-2 md:hidden">
                    <color-theme-toggle class="flex items-center justify-center space-x-8 md:ml-8 md:hidden" />

                    <Sheet>
                        <SheetTrigger>
                            <MenuIcon class="w-6 h-6" />
                        </SheetTrigger>
                        <SheetContent class="bg-sidebar overflow-y-auto">
                            <div class="mt-4">
                                <div class="flex mb-2">
                                    <search class="inline-block md:hidden" />
                                </div>

                                <NavDynamicItemResponsive v-for="item in leftItems" :key="item.key"
                                    :can-show-admin-sidebar="canShowAdminSidebar
                                        " :item="item" @logout="logout" />

                                <NavDynamicItemResponsive v-for="item in middleItems" :key="item.key"
                                    :can-show-admin-sidebar="canShowAdminSidebar
                                        " :item="item" @logout="logout" />

                                <NavDynamicItemResponsive v-for="item in rightItems" :key="item.key"
                                    :can-show-admin-sidebar="canShowAdminSidebar
                                        " :item="item" @logout="logout" />

                                <div v-if="$page.props.auth.user" class="pt-4 mt-4 pb-1 border-t border-border">
                                    <inertia-link as="a" :href="route(
                                        'user.public.get',
                                        $page.props.auth.user.username
                                    )
                                        " class="flex items-center px-4">
                                        <div v-if="
                                            $page.props.jetstream
                                                .managesProfilePhotos
                                        " class="flex-shrink-0 mr-3">
                                            <img class="object-cover w-10 h-10 rounded-full" :src="$page.props.auth.user
                                                .profile_photo_url
                                                " :alt="$page.props.auth.user.name
                                                    " />
                                        </div>

                                        <div>
                                            <div class="text-base font-medium text-foreground dark:text-foreground">
                                                {{ $page.props.auth.user.name }}
                                            </div>
                                            <div class="text-sm font-medium text-foreground">
                                                {{
                                                    $page.props.auth.user.email
                                                }}
                                            </div>
                                        </div>
                                    </inertia-link>

                                    <div class="mt-3 space-y-1">
                                        <jet-responsive-nav-link :href="route('profile.show')" :active="route().current('profile.show')
                                            ">
                                            {{ __("Edit Profile") }}
                                        </jet-responsive-nav-link>

                                        <jet-responsive-nav-link :href="route('linked-player.list')" :active="route().current(
                                            'linked-player.list'
                                        )
                                            ">
                                            {{ __("Linked Players") }}
                                        </jet-responsive-nav-link>

                                        <jet-responsive-nav-link :href="route(
                                            'recruitment-submission.index'
                                        )
                                            " :active="route().current(
                                                'recruitment-submission.index'
                                            )
                                                ">
                                            {{ __("My Applications") }}
                                        </jet-responsive-nav-link>

                                        <jet-responsive-nav-link v-if="
                                            $page.props?.pluginSettings
                                                ?.playerPasswordResetEnabled
                                        " :href="route(
                                            'reset-player-password.show'
                                        )
                                            " :active="route().current(
                                                    'reset-player-password.show'
                                                )
                                                    ">
                                            {{ __("Reset Player Password") }}
                                        </jet-responsive-nav-link>

                                        <jet-responsive-nav-link v-if="
                                            $page.props
                                                .playerSkinChangerEnabled
                                        " :href="route('change-player-skin.show')
                                            " :active="route().current(
                                                    'change-player-skin.show'
                                                )
                                                    ">
                                            {{ __("Change Player Skin") }}
                                        </jet-responsive-nav-link>

                                        <jet-responsive-nav-link :href="route('notification.index')" :active="route().current(
                                            'notification.index'
                                        )
                                            ">
                                            {{ __("Notifications") }}
                                        </jet-responsive-nav-link>

                                        <jet-responsive-nav-link v-if="
                                            $page.props.jetstream
                                                .hasApiFeatures
                                        " :href="route('api-tokens.index')" :active="route().current(
                                            'api-tokens.index'
                                        )
                                            ">
                                            API Tokens
                                        </jet-responsive-nav-link>

                                        <!-- Authentication -->
                                        <form method="POST" @submit.prevent="logout">
                                            <jet-responsive-nav-link as="button">
                                                {{ __("Logout") }}
                                            </jet-responsive-nav-link>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
                <!-- Mobile Menu End -->
            </div>
        </div>
    </nav>
</template>
