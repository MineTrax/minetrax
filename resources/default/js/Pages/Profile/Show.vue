<template>
    <app-layout>
        <app-head :title="__('Your Profile Settings')" />

        <template #header>
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __("Profile") }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-4 px-2 sm:py-6 sm:px-4 lg:py-10 lg:px-8">
                <Tabs default-value="profile" orientation="vertical" class="w-full">
                    <!-- Mobile: Stack vertically, Desktop: Side by side -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Tab Navigation -->
                        <div class="w-full md:w-64 md:flex-shrink-0 order-1">
                            <!-- Vertical tab navigation for all screen sizes -->
                            <TabsList class="flex flex-col h-fit w-full space-y-2 md:p-2 border shadow">
                                <TabsTrigger v-if="pageProps.jetstream.canUpdateProfileInformation" value="profile"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Profile") }}
                                </TabsTrigger>
                                <TabsTrigger value="notifications"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Notifications") }}
                                </TabsTrigger>
                                <TabsTrigger value="social-accounts"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Social Accounts") }}
                                </TabsTrigger>
                                <TabsTrigger v-if="pageProps.jetstream.canUpdatePassword" value="password"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Change Password") }}
                                </TabsTrigger>
                                <TabsTrigger v-if="pageProps.jetstream.canManageTwoFactorAuthentication"
                                    value="two-factor"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Two Factor") }}
                                </TabsTrigger>
                                <TabsTrigger value="sessions"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Sessions") }}
                                </TabsTrigger>
                                <TabsTrigger v-if="pageProps.jetstream.hasAccountDeletionFeatures"
                                    value="delete-account"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Delete Account") }}
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <!-- Tab Content -->
                        <div class="flex-1 order-2 min-w-0">
                            <TabsContent v-if="pageProps.jetstream.canUpdateProfileInformation" value="profile"
                                class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <update-profile-information-form :user="pageProps.auth.user" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="notifications" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <update-notification-preferences-form :user="pageProps.auth.user" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="social-accounts" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <linked-social-accounts-form />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.canUpdatePassword" value="password" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <update-password-form />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.canManageTwoFactorAuthentication" value="two-factor"
                                class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <two-factor-authentication-form :requires-confirmation="true" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="sessions" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <logout-other-browser-sessions-form :sessions="sessions" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.hasAccountDeletionFeatures" value="delete-account"
                                class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <delete-user-form />
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </div>
                    </div>
                </Tabs>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { usePage } from '@inertiajs/vue3'
import { useTranslations } from '@/Composables/useTranslations'
import AppLayout from '@/Layouts/AppLayout.vue'
import DeleteUserForm from './DeleteUserForm.vue'
import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm.vue'
import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm.vue'
import UpdatePasswordForm from './UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue'
import UpdateNotificationPreferencesForm from '@/Pages/Profile/UpdateNotificationPreferencesForm.vue'
import LinkedSocialAccountsForm from '@/Pages/Profile/LinkedSocialAccountsForm.vue'

const { __ } = useTranslations()
const page = usePage()
const pageProps = page.props

defineProps({
    sessions: {
        type: Array,
        required: true,
    },
})
</script>
