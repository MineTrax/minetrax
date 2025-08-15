<template>
    <form @submit.prevent="updateNotificationPreference">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-6">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th scope="col" class="text-lg text-left font-semibold text-foreground dark:text-foreground">
                                {{ __("Notify me when") }}
                            </th>
                            <th class="font-semibold text-foreground dark:text-foreground text-sm">
                                {{ __("Email") }}
                            </th>
                            <th class="font-semibold text-foreground dark:text-foreground text-sm">
                                {{ __("Discord") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-foreground dark:text-foreground text-sm leading-6">
                        <tr class="mt-5">
                            <td>{{ __("Someone commented on my Post") }}</td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('comment_on_post', 'mail')" @update:model-value="toggleNotification('comment_on_post', 'mail')" name="comment_on_post_mail" />
                            </td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('comment_on_post', 'discord')" @update:model-value="toggleNotification('comment_on_post', 'discord')" name="comment_on_post_discord" />
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("Someone liked my Post") }}</td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('like_on_post', 'mail')" @update:model-value="toggleNotification('like_on_post', 'mail')" name="like_on_post_mail" />
                            </td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('like_on_post', 'discord')" @update:model-value="toggleNotification('like_on_post', 'discord')" name="like_on_post_discord" />
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("I am muted by Staff") }}</td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('you_are_muted', 'mail')" @update:model-value="toggleNotification('you_are_muted', 'mail')" name="you_are_muted_mail" />
                            </td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('you_are_muted', 'discord')" @update:model-value="toggleNotification('you_are_muted', 'discord')" name="you_are_muted_discord" />
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("I am banned by Staff") }}</td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('you_are_banned', 'mail')" @update:model-value="toggleNotification('you_are_banned', 'mail')" name="you_are_banned_mail" />
                            </td>
                            <td class="text-center">
                                <Switch :model-value="isNotificationEnabled('you_are_banned', 'discord')" @update:model-value="toggleNotification('you_are_banned', 'discord')" name="you_are_banned_discord" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __("Application request status changed") }}
                            </td>
                            <td class="text-center">
                                <Switch
                                    :model-value="isNotificationEnabled('recruitment_submission_status_changed', 'mail')"
                                    @update:model-value="toggleNotification('recruitment_submission_status_changed', 'mail')"
                                    name="recruitment_submission_status_changed_mail"
                                />
                            </td>
                            <td class="text-center">
                                <Switch
                                    :model-value="isNotificationEnabled('recruitment_submission_status_changed', 'discord')"
                                    @update:model-value="toggleNotification('recruitment_submission_status_changed', 'discord')"
                                    name="recruitment_submission_status_changed_discord"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __("New message in application request") }}
                            </td>
                            <td class="text-center">
                                <Switch
                                    :model-value="isNotificationEnabled('recruitment_submission_comment_created', 'mail')"
                                    @update:model-value="toggleNotification('recruitment_submission_comment_created', 'mail')"
                                    name="recruitment_submission_comment_created_mail"
                                />
                            </td>
                            <td class="text-center">
                                <Switch
                                    :model-value="isNotificationEnabled('recruitment_submission_comment_created', 'discord')"
                                    @update:model-value="toggleNotification('recruitment_submission_comment_created', 'discord')"
                                    name="recruitment_submission_comment_created_discord"
                                />
                            </td>
                        </tr>
                        <template v-if="user.is_staff">
                            <tr class="flex mt-2">
                                <span class="font-bold dark:text-foreground text-foreground">
                                    {{ __("For staff members") }}
                                </span>
                            </tr>
                            <tr>
                                <td>
                                    {{ __("Application request received") }}
                                </td>
                                <td class="text-center">
                                    <Switch
                                        :model-value="isNotificationEnabled('recruitment_submission_created', 'mail')"
                                        @update:model-value="toggleNotification('recruitment_submission_created', 'mail')"
                                        name="recruitment_submission_created_mail"
                                    />
                                </td>
                                <td class="text-center">
                                    <Switch
                                        :model-value="isNotificationEnabled('recruitment_submission_created', 'discord')"
                                        @update:model-value="toggleNotification('recruitment_submission_created', 'discord')"
                                        name="recruitment_submission_created_discord"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ __("Custom form submission received") }}
                                </td>
                                <td class="text-center">
                                    <Switch
                                        :model-value="isNotificationEnabled('custom_form_submission_created', 'mail')"
                                        @update:model-value="toggleNotification('custom_form_submission_created', 'mail')"
                                        name="custom_form_submission_created_mail"
                                    />
                                </td>
                                <td class="text-center">
                                    <Switch
                                        :model-value="isNotificationEnabled('custom_form_submission_created', 'discord')"
                                        @update:model-value="toggleNotification('custom_form_submission_created', 'discord')"
                                        name="custom_form_submission_created_discord"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __("Someone commented on News") }}</td>
                                <td class="text-center">
                                    <Switch
                                        :model-value="isNotificationEnabled('news_commented_by_user', 'mail')"
                                        @update:model-value="toggleNotification('news_commented_by_user', 'mail')"
                                        name="news_commented_by_user_mail"
                                    />
                                </td>
                                <td class="text-center">
                                    <Switch
                                        :model-value="isNotificationEnabled('news_commented_by_user', 'discord')"
                                        @update:model-value="toggleNotification('news_commented_by_user', 'discord')"
                                        name="news_commented_by_user_discord"
                                    />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end pt-6 gap-4">
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                {{ __("Saved.") }}
            </jet-action-message>

            <LoadingButton :loading="form.processing">
                {{ __("Save") }}
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
