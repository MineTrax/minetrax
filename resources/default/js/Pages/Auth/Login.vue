<template>
    <AppLayout>
        <AppHead :title="__('Login')" />

        <JetAuthenticationCard>
            <div v-if="status" class="mb-4 font-medium text-sm text-success-600">
                {{ status }}
            </div>

            <form v-if="!$page.props.disableEmailPasswordAuth" class="mt-5" @submit.prevent="submit">
                <div>
                    <XInput id="email" v-model="form.email" :label="__('Email or Username')" :error="form.errors.email" :autofocus="true" :required="true" type="text" name="email" />
                </div>

                <div class="mt-4">
                    <XInput
                        id="password"
                        v-model="form.password"
                        :label="__('Password')"
                        :error="form.errors.password"
                        :autofocus="true"
                        :required="true"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                    />
                </div>

                <div class="mt-4 block">
                    <XSwitch id="remember" v-model="form.remember" :label="__('Remember me')" name="remember" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <InertiaLink v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-foreground hover:text-foreground dark:text-foreground dark:hover:text-foreground">
                        {{ __("Forgot your password?") }}
                    </InertiaLink>

                    <LoadingButton :loading="form.processing" class="ml-4">
                        {{ __("Login") }}
                    </LoadingButton>
                </div>
            </form>
            <SocialAuthButtons />
        </JetAuthenticationCard>
    </AppLayout>
</template>

<script setup>
import JetAuthenticationCard from "@/Jetstream/AuthenticationCard.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SocialAuthButtons from "@/Components/SocialAuthButtons.vue";
import XInput from "@/Components/Form/XInput.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import { useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>
