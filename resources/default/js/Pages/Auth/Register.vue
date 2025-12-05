<template>
    <AppLayout>
        <AppHead :title="__('Register')" />

        <JetAuthenticationCard>
            <form v-if="!$page.props.disableEmailPasswordAuth" class="mt-5" @submit.prevent="submit">
                <div>
                    <XInput id="name" v-model="form.name" :label="__('Full Name')" :error="form.errors.name" autocomplete="name" :autofocus="true" :required="true" type="text" name="name" />
                </div>

                <div class="mt-4">
                    <XInput id="email" v-model="form.email" :label="__('Email Address')" :error="form.errors.email" :required="true" type="email" name="email" />
                </div>

                <div class="mt-4">
                    <XInput id="username" v-model="form.username" :label="__('Username')" :error="form.errors.username" :required="true" type="text" name="username" />
                </div>

                <div class="mt-4">
                    <XInput id="password" v-model="form.password" :label="__('Password')" :error="form.errors.password" :required="true" autocomplete="new-password" type="password" name="password" />
                </div>

                <div class="mt-4">
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

                <PasswordStrengthMeter class="mt-2" :value="form.password" />

                <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                    <JetLabel for="terms">
                        <div class="flex items-center">
                            <JetCheckbox id="terms" v-model="form.terms" name="terms" />

                            <div class="ml-2">
                                {{ __("I agree to the ") }} <a target="_blank" :href="route('terms.show')" class="underline text-sm text-foreground hover:text-foreground">{{ __("Terms of Service") }}</a
                                >&nbsp;{{ __("and") }}&nbsp;<a target="_blank" :href="route('policy.show')" class="underline text-sm text-foreground hover:text-foreground">{{ __("Privacy Policy") }}</a>
                            </div>
                        </div>
                    </JetLabel>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <InertiaLink :href="route('login')" class="underline text-sm text-foreground hover:text-foreground dark:text-foreground dark:hover:text-foreground">
                        {{ __("Already registered?") }}
                    </InertiaLink>

                    <LoadingButton
                        :loading="form.processing"
                        class="ml-4"
                    >
                        {{ __("Register") }}
                    </LoadingButton>
                </div>
            </form>
            <SocialAuthButtons />
        </JetAuthenticationCard>
    </AppLayout>
</template>

<script setup>
import JetAuthenticationCard from "@/Jetstream/AuthenticationCard.vue";
import JetCheckbox from "@/Jetstream/Checkbox.vue";
import JetLabel from "@/Jetstream/Label.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SocialAuthButtons from "@/Components/SocialAuthButtons.vue";
import XInput from "@/Components/Form/XInput.vue";
import PasswordStrengthMeter from "@/Components/PasswordStrengthMeter.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    username: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>
