<template>
    <form @submit.prevent="updatePassword">
        <div class="grid grid-cols-6 gap-6">
            <div v-if="showCurrentPasswordConfirm" class="col-span-6 sm:col-span-4">
                <XInput
                    id="current_password"
                    ref="current_password"
                    v-model="form.current_password"
                    :label="__('Current Password')"
                    :error="form.errors.current_password"
                    :required="true"
                    autocomplete="current-password"
                    type="password"
                    name="current_password"
                />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <XInput id="password" ref="password" v-model="form.password" :label="__('New Password')" :error="form.errors.password" :required="true" autocomplete="new-password" type="password" name="password" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <XInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :label="__('Confirm Password')"
                    :error="form.errors.password_confirmation"
                    :required="true"
                    autocomplete="new-password"
                    type="password"
                    name="password_confirmation"
                />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <PasswordStrengthMeter :value="form.password" />
            </div>
        </div>

        <div class="flex items-center justify-end pt-6 gap-4">
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                {{ __("Saved.") }}
            </JetActionMessage>

            <LoadingButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" :loading="form.processing">
                {{ __("Change Password") }}
            </LoadingButton>
        </div>
    </form>
</template>

<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import XInput from "@/Components/Form/XInput.vue";
import PasswordStrengthMeter from "@/Components/PasswordStrengthMeter.vue";
import { useForm } from "@inertiajs/vue3";

// Template refs
const password = ref(null);
const current_password = ref(null);

// Form state
const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// Page props
const page = usePage();

// Computed properties
const showCurrentPasswordConfirm = computed(() => {
    return page.props.authHasPassword ?? true;
});

// Methods
const updatePassword = () => {
    form.put(route("user-password.update"), {
        errorBag: "updatePassword",
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                password.value.focus();
            }

            if (form.errors.current_password) {
                form.reset("current_password");
                current_password.value.focus();
            }
        },
    });
};
</script>
