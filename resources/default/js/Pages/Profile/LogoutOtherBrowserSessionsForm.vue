<template>
    <div>
        <div class="max-w-xl text-sm text-muted-foreground">
            {{
                __(
                    "If necessary, you may logout of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password."
                )
            }}
        </div>

        <!-- Other Browser Sessions -->
        <div v-if="sessions.length > 0" class="mt-5 space-y-6">
            <div v-for="(session, i) in sessions" :key="i" class="flex items-center">
                <div>
                    <svg v-if="session.agent.is_desktop" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8 text-muted-foreground">
                        <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="w-8 h-8 text-muted-foreground"
                    >
                        <path d="M0 0h24v24H0z" stroke="none" />
                        <rect x="7" y="4" width="10" height="16" rx="1" />
                        <path d="M11 5h2M12 17v.01" />
                    </svg>
                </div>

                <div class="ml-3">
                    <div class="text-sm font-medium text-foreground">{{ session.agent.platform }} - {{ session.agent.browser }}</div>

                    <div class="text-xs text-muted-foreground">
                        {{ session.ip_address }},

                        <span v-if="session.is_current_device" class="text-emerald-600 font-semibold">
                            {{ __("This device") }}
                        </span>
                        <span v-else> {{ __("Last active") }}&nbsp;{{ session.last_active }} </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-5 gap-3">
            <LoadingButton @click="confirmLogout">
                {{ __("Logout Other Browser Sessions") }}
            </LoadingButton>

            <p v-if="form.recentlySuccessful" class="text-sm text-success-600 font-medium">{{ __("Done") }}.</p>
        </div>

        <!-- Logout Other Devices Confirmation Modal -->
        <Dialog :open="confirmingLogout" @update:open="(value) => !value && closeModal()">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-foreground">
                        {{ __("Logout Other Browser Sessions") }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        {{ __("Please enter your password to confirm you would like to logout of your other browser sessions across all of your devices.") }}
                    </p>

                    <XInput ref="password" v-model="form.password" type="password" :label="__('Password')" :error="form.errors.password" :placeholder="__('Password')" @keyup.enter="logoutOtherBrowserSessions" />
                </div>

                <DialogFooter class="flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <Button variant="outline" @click="closeModal">
                        {{ __("Nevermind") }}
                    </Button>

                    <LoadingButton :loading="form.processing" @click="logoutOtherBrowserSessions">
                        {{ __("Logout Other Browser Sessions") }}
                    </LoadingButton>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref } from "vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/Components/ui/dialog";
import XInput from "@/Components/Form/XInput.vue";
import { useForm } from "@inertiajs/vue3";

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const password = ref(null);

const form = useForm({
    password: "",
});

const confirmLogout = () => {
    confirmingLogout.value = true;
    setTimeout(() => password.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route("other-browser-sessions.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => password.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;
    form.reset();
};
</script>
