<template>
    <AppLayout>
        <AppHead :title="__('Your Profile Settings')" />

        <template #header>
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __("Profile") }}
            </h2>
        </template>

        <div>
            <AppBreadcrumb class="max-w-screen-xl mx-auto" :items="breadcrumbItems" />
            <div class="max-w-screen-xl mx-auto py-4 px-2 sm:py-6 sm:px-4 lg:px-8">
                <Tabs default-value="profile" orientation="vertical" class="w-full">
                    <!-- Mobile: Stack vertically, Desktop: Side by side -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Tab Navigation -->
                        <div class="w-full md:w-64 md:flex-shrink-0 order-1">
                            <!-- Vertical tab navigation for all screen sizes -->
                            <TabsList class="flex flex-col h-fit w-full space-y-2 md:p-2 border shadow">
                                <TabsTrigger
                                    v-if="pageProps.jetstream.canUpdateProfileInformation"
                                    value="profile"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10"
                                >
                                    {{ __("Profile") }}
                                </TabsTrigger>
                                <TabsTrigger value="notifications" class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Notifications") }}
                                </TabsTrigger>
                                <TabsTrigger value="social-accounts" class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Social Accounts") }}
                                </TabsTrigger>
                                <TabsTrigger
                                    v-if="pageProps.jetstream.canUpdatePassword"
                                    value="password"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10"
                                >
                                    {{ __("Change Password") }}
                                </TabsTrigger>
                                <TabsTrigger
                                    v-if="pageProps.jetstream.canManageTwoFactorAuthentication"
                                    value="two-factor"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10"
                                >
                                    {{ __("Two Factor") }}
                                </TabsTrigger>
                                <TabsTrigger value="sessions" class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                                    {{ __("Sessions") }}
                                </TabsTrigger>
                                <TabsTrigger
                                    v-if="pageProps.jetstream.hasAccountDeletionFeatures"
                                    value="delete-account"
                                    class="w-full justify-start py-2 md:px-4 md:py-3 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10"
                                >
                                    {{ __("Delete Account") }}
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <!-- Tab Content -->
                        <div class="flex-1 order-2 min-w-0">
                            <TabsContent v-if="pageProps.jetstream.canUpdateProfileInformation" value="profile" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <UpdateProfileInformationForm :user="pageProps.auth.user" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="notifications" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <UpdateNotificationPreferencesForm :user="pageProps.auth.user" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="social-accounts" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <LinkedSocialAccountsForm />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.canUpdatePassword" value="password" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <UpdatePasswordForm />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.canManageTwoFactorAuthentication" value="two-factor" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <TwoFactorAuthenticationForm :requires-confirmation="true" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="sessions" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent v-if="pageProps.jetstream.hasAccountDeletionFeatures" value="delete-account" class="mt-0">
                                <Card class="shadow-sm">
                                    <CardContent class="pt-4">
                                        <DeleteUserForm />
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </div>
                    </div>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { Card, CardContent } from "@/Components/ui/card";
import { usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "./DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "./LogoutOtherBrowserSessionsForm.vue";
import TwoFactorAuthenticationForm from "./TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "./UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./UpdateProfileInformationForm.vue";
import UpdateNotificationPreferencesForm from "@/Pages/Profile/UpdateNotificationPreferencesForm.vue";
import LinkedSocialAccountsForm from "@/Pages/Profile/LinkedSocialAccountsForm.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { computed } from "vue";

const { __ } = useTranslations();
const page = usePage();
const pageProps = page.props;

defineProps({
    sessions: {
        type: Array,
        required: true,
    },
});

// Breadcrumb data stored in JSON format
const breadcrumbItems = computed(() => [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Edit Profile'),
        current: true
    }
]);
</script>
