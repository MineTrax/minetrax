<script setup>
import { ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import Multiselect from "vue-multiselect";
import XInput from "@/Components/Form/XInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import AlertCard from "@/Components/AlertCard.vue";
import { ArrowPathIcon, CheckCircleIcon } from "@heroicons/vue/24/solid";
import { useHelpers } from "@/Composables/useHelpers";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";

const { __ } = useTranslations();
const { generateRandomString, secondsToHMS } = useHelpers();

const props = defineProps({
    uuid: {
        type: String,
        required: false,
    },
    players: {
        type: Array,
        required: true,
    },
    cooldown: {
        type: Number,
    },
    cannotPlayerPasswordReset: {
        type: Boolean,
    },
});

let selectedPlayer = ref(props.players[0]);
const found = props.players.find((player) => player.uuid === props.uuid);
if (found) {
    selectedPlayer.value = found;
}

const form = useForm({
    player_uuid: null,
    new_password: null,
    new_password_confirmation: null,
    reason: null,
});

const submitForm = () => {
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route("reset-player-password.update"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const formDisabled = computed(() => {
    return form.processing || !selectedPlayer.value || props.cannotPlayerPasswordReset || props.cooldown > 0;
});

const success = computed(() => {
    return usePage().props?.toast?.type === "success";
});
</script>

<template>
    <AppLayout>
        <AppHead :title="__('Reset Player Password')" />
        <div class="max-w-4xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 v-if="selectedPlayer" class="text-lg md:text-2xl font-bold text-foreground">
                        {{
                            __("Reset password for :username", {
                                username: selectedPlayer.username,
                            })
                        }}
                    </h2>
                    <h2 v-else class="text-lg italic md:text-2xl font-bold text-foreground">
                        {{ __("No Linked Players") }}
                    </h2>
                    <p v-if="selectedPlayer" class="text-sm text-muted-foreground mt-1">{{ __("Player Uuid") }}: {{ selectedPlayer.uuid }}</p>
                </div>

                <Multiselect
                    id="username"
                    v-model="selectedPlayer"
                    class="w-full md:w-1/3 rounded-md shadow-sm focus:ring-ring focus:border-input sm:text-sm"
                    :options="players"
                    :multiple="false"
                    :placeholder="__('Search') + '...'"
                    label="username"
                    :allow-empty="false"
                    :deselect-label="__('Can\'t remove')"
                    track-by="id"
                />
            </div>

            <AlertCard v-if="success" text-color="text-success-800 dark:text-success-500" border-color="border-success-500">
                <template #icon>
                    <CheckCircleIcon class="fill-current h-6 w-6 text-success-500 mr-4" />
                </template>
                <h2 class="text-xl">
                    {{ __("Password changed successfully!") }}
                </h2>
                <template #body>
                    <p class="text-sm">
                        {{ __("Your player password has been changed successfully. You can now join the server with new password.") }}
                    </p>
                </template>
            </AlertCard>

            <AlertCard v-if="!selectedPlayer" text-color="text-error-800 dark:text-error-500" border-color="border-error-500">
                {{ __("No linked players found. Please link a player to your account.") }}
            </AlertCard>

            <AlertCard v-if="cannotPlayerPasswordReset" text-color="text-error-800 dark:text-error-500" border-color="border-error-500">
                {{ __("Your are not allowed to reset your player passwords.") }}

                <template #body>
                    <p class="text-sm">
                        {{ __("Site administrator has explicitly disabled this feature for your role. Contact for more information.") }}
                    </p>
                </template>
            </AlertCard>

            <Card class="shadow-sm">
                <CardContent class="p-6">
                    <form class="space-y-6" @submit.prevent="submitForm">
                        <div class="space-y-4">
                            <div class="relative">
                                <x-input
                                    id="new_password"
                                    v-model="form.new_password"
                                    :disabled="formDisabled"
                                    :label="__('New Password')"
                                    :error="form.errors.new_password"
                                    type="text"
                                    name="new_password"
                                    help-error-flex="flex-row"
                                    class="pr-12"
                                />
                                <Button
                                    v-tippy
                                    :title="__('Generate Secure Password')"
                                    :disabled="formDisabled"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="absolute right-0 top-[1.85rem] h-9 w-9 p-0"
                                    @click="form.new_password = generateRandomString(16)"
                                >
                                    <ArrowPathIcon class="w-5 h-5" />
                                </Button>
                            </div>

                            <div>
                                <x-input
                                    id="reason"
                                    v-model="form.reason"
                                    :disabled="formDisabled"
                                    :label="__('Reason')"
                                    :error="form.errors.reason"
                                    type="text"
                                    name="reason"
                                    help-error-flex="flex-row"
                                    :help="__('Small reason why are you changing the password?')"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <LoadingButton :loading="form.processing" :disabled="formDisabled" type="submit">
                                {{ __("Reset Player Password") }}
                            </LoadingButton>

                            <p v-if="props.cooldown > 0" class="text-sm text-orange-600 dark:text-orange-400">
                                {{
                                    __("You are on a cooldown because you have recently changed your password. Refresh the page and try again after :cooldown.", {
                                        cooldown: secondsToHMS(props.cooldown, true),
                                    })
                                }}
                            </p>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
