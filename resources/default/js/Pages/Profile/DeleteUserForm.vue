<template>
    <div>
        <div class="max-w-xl text-sm text-muted-foreground">
            {{ __("Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.") }}
        </div>

        <div class="flex items-center justify-end mt-5">
            <Button variant="destructive" @click="confirmUserDeletion">
                {{ __("Delete Account") }}
            </Button>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <Dialog :open="confirmingUserDeletion" @update:open="(value) => !value && closeModal()">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-foreground">
                        {{ __("Delete Account") }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        {{
                            __(
                                "Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
                            )
                        }}
                    </p>

                    <XInput ref="password" v-model="form.password" type="password" :label="__('Password')" :error="form.errors.password" :placeholder="__('Password')" @keyup.enter="deleteUser" />
                </div>

                <DialogFooter class="flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <Button variant="outline" @click="closeModal">
                        {{ __("Nevermind") }}
                    </Button>

                    <LoadingButton variant="destructive" :loading="form.processing" @click="deleteUser">
                        {{ __("Delete Account") }}
                    </LoadingButton>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/Components/ui/dialog";
import XInput from "@/Components/Form/XInput.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import { useForm } from "@inertiajs/vue3";

const confirmingUserDeletion = ref(false);
const password = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => password.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route("current-user.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => password.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>
