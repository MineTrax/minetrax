<template>
    <app-layout>
        <app-head :title="__('Your Profile Settings')" />

        <template #header>
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __("Profile") }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8">
                <Tabs default-value="profile" orientation="vertical" class="w-full">
                    <div class="flex gap-4">
                        <!-- Tab Navigation (Left Side) -->
                        <div class="w-64 flex-shrink-0">
                            <TabsList class="flex flex-col p-2 border shadow">
                                <TabsTrigger v-if="pageProps.jetstream.canUpdateProfileInformation" value="profile"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Profile") }}
                                </TabsTrigger>
                                <TabsTrigger value="notifications"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Notifications") }}
                                </TabsTrigger>
                                <TabsTrigger value="socialaccounts"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Social Accounts") }}
                                </TabsTrigger>
                                <TabsTrigger v-if="pageProps.jetstream.canUpdatePassword" value="updatepassword"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Change Password") }}
                                </TabsTrigger>
                                <TabsTrigger v-if="pageProps.jetstream.canManageTwoFactorAuthentication"
                                    value="twofactor"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Two Factor") }}
                                </TabsTrigger>
                                <TabsTrigger value="browsersessions"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Browser Sessions") }}
                                </TabsTrigger>
                                <TabsTrigger v-if="pageProps.jetstream.hasAccountDeletionFeatures"
                                    value="deleteaccount"
                                    class="w-full justify-start px-4 py-2 text-left font-medium rounded">
                                    {{ __("Delete Account") }}
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <!-- Tab Content (Right Side) -->
                        <div class="flex-1">
                            <TabsContent v-if="pageProps.jetstream.canUpdateProfileInformation" value="profile"
                                class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
                                        <update-profile-information-form :user="pageProps.auth.user" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="notifications" class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
                                        <update-notification-preferences-form :user="pageProps.auth.user" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="socialaccounts" class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
                                        <linked-social-accounts-form />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.canUpdatePassword" value="updatepassword" class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
                                        <update-password-form />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.canManageTwoFactorAuthentication" value="twofactor"
                                class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
                                        <two-factor-authentication-form :requires-confirmation="true" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="browsersessions" class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
                                        <logout-other-browser-sessions-form :sessions="sessions" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.hasAccountDeletionFeatures" value="deleteaccount"
                                class="mt-0">
                                <Card class="pt-6">
                                    <CardContent>
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
