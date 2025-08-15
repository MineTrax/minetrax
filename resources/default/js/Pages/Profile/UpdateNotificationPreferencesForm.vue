<template>
    <form @submit.prevent="updateNotificationPreference">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h3 class="text-xl font-semibold text-foreground mb-2">
                    {{ __("Notification Preferences") }}
                </h3>
                <p class="text-muted-foreground text-sm">
                    {{ __("Choose how you want to receive notifications for different events") }}
                </p>
            </div>

            <!-- Personal Notifications Section -->
            <div class="space-y-4">
                <div class="bg-card border border-border rounded-lg overflow-hidden">
                    <!-- Post Comments -->
                    <div class="p-4 hover:bg-muted/5 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-foreground mb-1">{{ __("Someone commented on my Post") }}</h5>
                                <p class="text-sm text-muted-foreground">{{ __("Get notified when someone comments on your posts") }}</p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Email</span>
                                    <Switch :model-value="isNotificationEnabled('comment_on_post', 'mail')" @update:model-value="toggleNotification('comment_on_post', 'mail')" name="comment_on_post_mail" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Discord</span>
                                    <Switch :model-value="isNotificationEnabled('comment_on_post', 'discord')" @update:model-value="toggleNotification('comment_on_post', 'discord')" name="comment_on_post_discord" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Likes -->
                    <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-foreground mb-1">{{ __("Someone liked my Post") }}</h5>
                                <p class="text-sm text-muted-foreground">{{ __("Get notified when someone likes your posts") }}</p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Email</span>
                                    <Switch :model-value="isNotificationEnabled('like_on_post', 'mail')" @update:model-value="toggleNotification('like_on_post', 'mail')" name="like_on_post_mail" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Discord</span>
                                    <Switch :model-value="isNotificationEnabled('like_on_post', 'discord')" @update:model-value="toggleNotification('like_on_post', 'discord')" name="like_on_post_discord" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Moderation Actions -->
                    <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-foreground mb-1">{{ __("I am muted by Staff") }}</h5>
                                <p class="text-sm text-muted-foreground">{{ __("Get notified when staff mutes your account") }}</p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Email</span>
                                    <Switch :model-value="isNotificationEnabled('you_are_muted', 'mail')" @update:model-value="toggleNotification('you_are_muted', 'mail')" name="you_are_muted_mail" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Discord</span>
                                    <Switch :model-value="isNotificationEnabled('you_are_muted', 'discord')" @update:model-value="toggleNotification('you_are_muted', 'discord')" name="you_are_muted_discord" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-foreground mb-1">{{ __("I am banned by Staff") }}</h5>
                                <p class="text-sm text-muted-foreground">{{ __("Get notified when staff bans your account") }}</p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Email</span>
                                    <Switch :model-value="isNotificationEnabled('you_are_banned', 'mail')" @update:model-value="toggleNotification('you_are_banned', 'mail')" name="you_are_banned_mail" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Discord</span>
                                    <Switch :model-value="isNotificationEnabled('you_are_banned', 'discord')" @update:model-value="toggleNotification('you_are_banned', 'discord')" name="you_are_banned_discord" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Updates -->
                    <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-foreground mb-1">{{ __("Application request status changed") }}</h5>
                                <p class="text-sm text-muted-foreground">{{ __("Get notified when your application status is updated") }}</p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Email</span>
                                    <Switch
                                        :model-value="isNotificationEnabled('recruitment_submission_status_changed', 'mail')"
                                        @update:model-value="toggleNotification('recruitment_submission_status_changed', 'mail')"
                                        name="recruitment_submission_status_changed_mail"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Discord</span>
                                    <Switch
                                        :model-value="isNotificationEnabled('recruitment_submission_status_changed', 'discord')"
                                        @update:model-value="toggleNotification('recruitment_submission_status_changed', 'discord')"
                                        name="recruitment_submission_status_changed_discord"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-foreground mb-1">{{ __("New message in application request") }}</h5>
                                <p class="text-sm text-muted-foreground">{{ __("Get notified when someone comments on your application") }}</p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Email</span>
                                    <Switch
                                        :model-value="isNotificationEnabled('recruitment_submission_comment_created', 'mail')"
                                        @update:model-value="toggleNotification('recruitment_submission_comment_created', 'mail')"
                                        name="recruitment_submission_comment_created_mail"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Discord</span>
                                    <Switch
                                        :model-value="isNotificationEnabled('recruitment_submission_comment_created', 'discord')"
                                        @update:model-value="toggleNotification('recruitment_submission_comment_created', 'discord')"
                                        name="recruitment_submission_comment_created_discord"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Notifications Section -->
            <template v-if="user.is_staff">
                <div class="pt-2">
                    <div class="space-y-4">
                        <div class="mb-4">
                            <h4 class="text-lg font-semibold text-foreground mb-1">{{ __("Staff Notifications") }}</h4>
                            <p class="text-sm text-muted-foreground">{{ __("Administrative notifications for staff members") }}</p>
                        </div>

                        <div class="bg-card border border-border rounded-lg overflow-hidden">
                            <!-- Application Received -->
                            <div class="p-4 hover:bg-muted/5 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="font-medium text-foreground mb-1">{{ __("Application request received") }}</h5>
                                        <p class="text-sm text-muted-foreground">{{ __("Get notified when new applications are submitted") }}</p>
                                    </div>
                                    <div class="flex items-center gap-4 ml-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-muted-foreground">Email</span>
                                            <Switch
                                                :model-value="isNotificationEnabled('recruitment_submission_created', 'mail')"
                                                @update:model-value="toggleNotification('recruitment_submission_created', 'mail')"
                                                name="recruitment_submission_created_mail"
                                            />
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-muted-foreground">Discord</span>
                                            <Switch
                                                :model-value="isNotificationEnabled('recruitment_submission_created', 'discord')"
                                                @update:model-value="toggleNotification('recruitment_submission_created', 'discord')"
                                                name="recruitment_submission_created_discord"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Form Submission -->
                            <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="font-medium text-foreground mb-1">{{ __("Custom form submission received") }}</h5>
                                        <p class="text-sm text-muted-foreground">{{ __("Get notified when users submit custom forms") }}</p>
                                    </div>
                                    <div class="flex items-center gap-4 ml-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-muted-foreground">Email</span>
                                            <Switch
                                                :model-value="isNotificationEnabled('custom_form_submission_created', 'mail')"
                                                @update:model-value="toggleNotification('custom_form_submission_created', 'mail')"
                                                name="custom_form_submission_created_mail"
                                            />
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-muted-foreground">Discord</span>
                                            <Switch
                                                :model-value="isNotificationEnabled('custom_form_submission_created', 'discord')"
                                                @update:model-value="toggleNotification('custom_form_submission_created', 'discord')"
                                                name="custom_form_submission_created_discord"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- News Comments -->
                            <div class="p-4 border-t border-border hover:bg-muted/5 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="font-medium text-foreground mb-1">{{ __("Someone commented on News") }}</h5>
                                        <p class="text-sm text-muted-foreground">{{ __("Get notified when users comment on news posts") }}</p>
                                    </div>
                                    <div class="flex items-center gap-4 ml-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-muted-foreground">Email</span>
                                            <Switch
                                                :model-value="isNotificationEnabled('news_commented_by_user', 'mail')"
                                                @update:model-value="toggleNotification('news_commented_by_user', 'mail')"
                                                name="news_commented_by_user_mail"
                                            />
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-muted-foreground">Discord</span>
                                            <Switch
                                                :model-value="isNotificationEnabled('news_commented_by_user', 'discord')"
                                                @update:model-value="toggleNotification('news_commented_by_user', 'discord')"
                                                name="news_commented_by_user_discord"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end pt-6 gap-4 border-t border-border">
            <jet-action-message :on="form.recentlySuccessful" class="mr-3 text-green-600 dark:text-green-400">
                {{ __("Saved.") }}
            </jet-action-message>

            <LoadingButton
                :loading="form.processing"
                class="bg-primary hover:bg-primary/90 text-primary-foreground px-6 py-2 rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                {{ __("Save Preferences") }}
            </LoadingButton>
        </div>
    </form>
</template>

<script setup>
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import Switch from "@/Components/ui/switch/Switch.vue";

import { useForm } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

function notificationValueOrDefault(key) {
    // Default
    const defaultPref = ["database", "mail", "discord"];
    if (!props.user?.settings?.notifications) {
        return defaultPref;
    }

    if (props.user.settings.notifications[key] === undefined) {
        return defaultPref;
    }

    return props.user.settings?.notifications[key];
}

const form = useForm({
    like_on_post: notificationValueOrDefault("like_on_post"),
    comment_on_post: notificationValueOrDefault("comment_on_post"),
    you_are_muted: notificationValueOrDefault("you_are_muted"),
    you_are_banned: notificationValueOrDefault("you_are_banned"),
    recruitment_submission_comment_created: notificationValueOrDefault("recruitment_submission_comment_created"),
    recruitment_submission_status_changed: notificationValueOrDefault("recruitment_submission_status_changed"),
    // Staff
    custom_form_submission_created: notificationValueOrDefault("custom_form_submission_created"),
    news_commented_by_user: notificationValueOrDefault("news_commented_by_user"),
    recruitment_submission_created: notificationValueOrDefault("recruitment_submission_created"),
});

// Helper methods to handle switch state
const isNotificationEnabled = (notificationType, channel) => {
    return form[notificationType]?.includes(channel) || false;
};

const toggleNotification = (notificationType, channel) => {
    const currentValues = form[notificationType] || [];
    const hasValue = currentValues.includes(channel);

    if (hasValue) {
        form[notificationType] = currentValues.filter((v) => v !== channel);
    } else {
        form[notificationType] = [...currentValues, channel];
    }
};

const updateNotificationPreference = () => {
    form.put(route("auth.put-notification-preferences"), {
        errorBag: "updateNotificationPreference",
        preserveScroll: true,
    });
};
</script>
