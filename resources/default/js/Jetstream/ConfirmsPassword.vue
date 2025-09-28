<script setup>
import { ref, reactive, nextTick } from "vue";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/Components/ui/dialog";
import XInput from "@/Components/Form/XInput.vue";
import LoadingButton from "@/Components/LoadingButton.vue";

const emit = defineEmits(["confirmed"]);

defineProps({
    title: {
        type: String,
        default: "Confirm Password",
    },
    content: {
        type: String,
        default: "For your security, please confirm your password to continue.",
    },
    button: {
        type: String,
        default: "Confirm",
    },
});

const confirmingPassword = ref(false);

const form = reactive({
    password: "",
    error: "",
    processing: false,
});

const passwordInput = ref(null);

const startConfirmingPassword = () => {
    axios.get(route("password.confirmation")).then((response) => {
        if (response.data.confirmed) {
            emit("confirmed");
        } else {
            confirmingPassword.value = true;

            setTimeout(() => passwordInput.value.focus(), 250);
        }
    });
};

const confirmPassword = () => {
    form.processing = true;

    axios
        .post(route("password.confirm"), {
            password: form.password,
        })
        .then(() => {
            form.processing = false;

            closeModal();
            nextTick().then(() => emit("confirmed"));
        })
        .catch((error) => {
            form.processing = false;
            form.error = error.response.data.errors.password[0];
            passwordInput.value.focus();
        });
};

const closeModal = () => {
    confirmingPassword.value = false;
    form.password = "";
    form.error = "";
};
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <Dialog :open="confirmingPassword" @update:open="(value) => !value && closeModal()">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-foreground">
                        {{ title }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        {{ content }}
                    </p>

                    <XInput ref="passwordInput" v-model="form.password" type="password" :label="__('Password')" :error="form.error" :placeholder="__('Password')" @keyup.enter="confirmPassword" />

                    <p class="text-xs text-muted-foreground italic">
                        {{ __("Continue with empty password if you have no password.") }}
                    </p>
                </div>

                <DialogFooter class="flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <Button variant="outline" @click="closeModal">
                        {{ __("Cancel") }}
                    </Button>

                    <LoadingButton :loading="form.processing" @click="confirmPassword">
                        {{ button }}
                    </LoadingButton>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </span>
</template>
