<script setup>
import Multiselect from "vue-multiselect";
import XInput from "@/Components/Form/XInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/Components/ui/tabs";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import LoadingButton from "@/Components/LoadingButton.vue";
import AlertCard from "@/Components/AlertCard.vue";
import { computed } from "vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

const props = defineProps({
    uuid: {
        type: String,
        required: false,
    },
    players: {
        type: Array,
        required: true,
    },
    hasServersWithFeature: {
        type: Boolean,
        required: true,
    },
    cooldown: {
        type: Number,
    },
});

let selectedPlayer = ref(props.players[0]);
const found = props.players.find((player) => player.uuid === props.uuid);
if (found) {
    selectedPlayer.value = found;
}

const tabList = [
    { value: "upload", label: "Upload Skin" },
    { value: "url", label: "From URL" },
    { value: "username", label: "From Username" },
    { value: "reset", label: "Reset" },
];

const file = ref(null);
const form = useForm({
    action_type: "upload", // 'upload', 'url', 'username', 'reset'
    player_uuid: null,
    // upload type
    skin_type: "steve",
    file: null,
    // name type
    username: "",
    // url type
    url: "",
});

const submitUploadSkinForm = () => {
    form.action_type = "upload";
    form.file = file.value.files[0];
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route("change-player-skin.update"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const submitUrlSkinForm = () => {
    form.action_type = "url";
    form.username = null;
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route("change-player-skin.update"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const submitUsernameSkinForm = () => {
    form.action_type = "username";
    form.url = null;
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route("change-player-skin.update"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const submitResetSkinForm = () => {
    form.action_type = "reset";
    form.url = null;
    form.username = null;
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route("change-player-skin.update"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const validSkinUrlSampleList = [
    "https://namemc.com/skin/a569a3e7aad87b3a",
    "https://minesk.in/7a8d3a710c5b440a875d9b6fb4d7d9a3",
    "http://novask.in/6673493202.png",
    "http://textures.minecraft.net/texture/63741c4509672cc31e43750d5223d4b3099f851e8039651550e98719692dd028",
];

const formDisabled = computed(() => {
    return !props.hasServersWithFeature || form.processing || !selectedPlayer.value;
});

// Breadcrumb data stored in JSON format
const breadcrumbItems = computed(() => [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Change Player Skin'),
        current: true
    }
]);
</script>

<template>
    <AppLayout>
        <AppHead :title="__('Change Player Skin')" />
        <AppBreadcrumb class="max-w-screen-xl mx-auto" :items="breadcrumbItems" />
        <div class="max-w-screen-xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <h2 v-if="selectedPlayer" class="text-lg mb-2 md:mb-0 md:text-2xl font-bold text-foreground">
                    {{
                        __("Change skin for :username", {
                            username: selectedPlayer.username,
                        })
                    }}
                </h2>
                <h2 v-else class="text-lg italic mb-2 md:mb-0 md:text-2xl font-bold text-foreground">
                    {{ __("No Linked Players") }}
                </h2>

                <Multiselect
                    id="country"
                    v-model="selectedPlayer"
                    class="w-full md:w-1/3 bg-background border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="players"
                    :multiple="false"
                    :placeholder="__('Search') + '...'"
                    label="username"
                    :allow-empty="false"
                    :deselect-label="__('Can\'t remove')"
                    track-by="id"
                />
            </div>

            <p v-if="selectedPlayer" class="text-xs text-muted-foreground">{{ __("Player Uuid") }}: {{ selectedPlayer.uuid }}</p>

            <AlertCard v-if="!hasServersWithFeature" text-color="text-error-800" border-color="border-error-500">
                <p class="text-sm">
                    {{ __("Oh Jeez! No server support changing of skin yet!") }}
                </p>
                <template #body>
                    <p class="text-foreground">
                        {{ __("This feature is not enabled in any of the servers. Below form will be disabled. ") }}
                    </p>
                    <p class="text-sm text-foreground">
                        {{ __("Please check back later or contact the server administrator.") }}
                    </p>
                </template>
            </AlertCard>

            <AlertCard v-if="cooldown" text-color="text-warning-800" border-color="border-warning-500">
                {{
                    __("You are on a cooldown! Please wait for :cooldown seconds before you can try again.", {
                        cooldown: cooldown,
                    })
                }}
            </AlertCard>

            <AlertCard v-if="!selectedPlayer" text-color="text-error-800" border-color="border-error-500">
                {{ __("No linked players found. Please link a player to your account.") }}
            </AlertCard>

            <Tabs default-value="upload" class="w-full">
                <div class="flex flex-col md:flex-row gap-4">
                    <TabsList class="flex flex-col h-fit w-full md:w-64 space-y-2 p-2 border shadow">
                        <TabsTrigger v-for="tab in tabList" :key="tab.value" :value="tab.value" class="w-full justify-start py-2 px-4 text-left font-medium rounded-lg transition-all duration-200 hover:bg-accent/10">
                            {{ __(tab.label) }}
                        </TabsTrigger>
                    </TabsList>

                    <div class="flex-1 min-w-0">
                        <TabsContent value="upload" class="mt-0">
                            <Card class="shadow-sm">
                                <CardHeader>
                                    <CardTitle>{{ __("Upload Skin") }}</CardTitle>
                                    <CardDescription>
                                        {{ __("Please upload a valid skin image. ⚠️") }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex flex-col md:flex-row justify-between gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-muted-foreground mb-2">
                                                {{ __("Refer to the provided sample guide image to understand which image should be upload.") }}
                                            </p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ __("A valid skin image should look like this: ") }}
                                                <a class="text-primary hover:underline" href="https://mc-heads.net/skin/xinecraft.png" target="_blank">https://mc-heads.net/skin/xinecraft.png</a>
                                            </p>
                                        </div>
                                        <img src="/images/valid-skin-format.png" alt="Skin Sample" class="w-48 h-40" />
                                    </div>

                                    <form class="w-full border-t pt-4 border-border" @submit.prevent="submitUploadSkinForm">
                                        <div class="col-span-6 sm:col-span-6 space-y-3">
                                            <p class="text-sm text-foreground font-bold">
                                                {{ __("Skin Type") }}
                                            </p>

                                            <div class="flex gap-6">
                                                <div class="flex">
                                                    <div class="flex items-center h-5">
                                                        <input
                                                            id="skin_type_steve"
                                                            v-model="form.skin_type"
                                                            type="radio"
                                                            value="steve"
                                                            class="w-4 h-4 text-primary bg-background border-input focus:ring-primary focus:ring-2"
                                                            name="skin_type"
                                                        />
                                                    </div>
                                                    <div class="ml-2 text-sm">
                                                        <label for="skin_type_steve" class="font-medium text-foreground">{{ __("Steve") }}</label>
                                                    </div>
                                                </div>
                                                <div class="flex">
                                                    <div class="flex items-center h-5">
                                                        <input
                                                            id="skin_type_alex"
                                                            v-model="form.skin_type"
                                                            type="radio"
                                                            value="alex"
                                                            class="w-4 h-4 text-primary bg-background border-input focus:ring-primary focus:ring-2"
                                                            name="skin_type"
                                                        />
                                                    </div>
                                                    <div class="ml-2 text-sm">
                                                        <label for="skin_type_alex" class="font-medium text-foreground">{{ __("Alex") }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-span-6 sm:col-span-6 pt-4 space-y-3">
                                            <p class="text-sm text-foreground font-bold">
                                                {{ __("Skin File") }}
                                            </p>

                                            <div class="flex gap-6">
                                                <input
                                                    id="file"
                                                    ref="file"
                                                    accept=".png"
                                                    type="file"
                                                    class="block p-2 w-full text-sm text-foreground border border-input rounded-lg cursor-pointer bg-background focus:outline-none placeholder:text-muted-foreground"
                                                    required
                                                />
                                            </div>
                                            <p v-if="form.errors.file" class="text-xs text-error-500">
                                                {{ form.errors.file }}
                                            </p>
                                        </div>

                                        <div class="mt-6">
                                            <LoadingButton
                                                :loading="form.processing"
                                                :disabled="formDisabled"
                                                type="submit"
                                            >
                                                {{ __("Change Skin") }}
                                            </LoadingButton>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="url" class="mt-0">
                            <Card class="shadow-sm">
                                <CardHeader>
                                    <CardTitle>{{ __("From URL") }}</CardTitle>
                                    <CardDescription>
                                        {{ __("Change your skin by providing a valid skin URL. You can find skin from namemc.com, mineskin.org and other skin websites.") }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-sm mb-4">
                                        <div>
                                            <p class="text-muted-foreground mb-2">{{ __("Here are some examples of valid skin url:") }}</p>
                                            <ul class="list-disc list-inside text-muted-foreground">
                                                <li v-for="url in validSkinUrlSampleList" :key="url" class="list-item break-all">
                                                    {{ url }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <form class="w-full" @submit.prevent="submitUrlSkinForm">
                                        <div class="col-span-6 sm:col-span-6">
                                            <x-input id="url" v-model="form.url" :label="__('Skin URL')" :error="form.errors.url" type="text" name="url" help-error-flex="flex-row" />
                                        </div>

                                        <div class="mt-6">
                                            <LoadingButton
                                                :loading="form.processing"
                                                :disabled="formDisabled"
                                                type="submit"
                                            >
                                                {{ __("Change Skin") }}
                                            </LoadingButton>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="username" class="mt-0">
                            <Card class="shadow-sm">
                                <CardHeader>
                                    <CardTitle>{{ __("From Username") }}</CardTitle>
                                    <CardDescription>
                                        {{ __("Change your skin by providing a valid Minecraft username (Premium account).") }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <p class="text-sm text-muted-foreground mb-4">
                                        {{ __("The skin will be fetched from Mojang server.") }}
                                    </p>

                                    <form class="w-full" @submit.prevent="submitUsernameSkinForm">
                                        <div class="col-span-6 sm:col-span-6">
                                            <x-input id="username" v-model="form.username" :label="__('Eg: xinecraft')" :error="form.errors.username" type="text" name="username" help-error-flex="flex-row" />
                                        </div>

                                        <div class="mt-6">
                                            <LoadingButton
                                                :loading="form.processing"
                                                :disabled="formDisabled"
                                                type="submit"
                                            >
                                                {{ __("Change Skin") }}
                                            </LoadingButton>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="reset" class="mt-0">
                            <Card class="shadow-sm">
                                <CardHeader>
                                    <CardTitle>{{ __("Reset") }}</CardTitle>
                                    <CardDescription>
                                        {{ __("Reset your skin to default Minecraft skin. If you have premium minecraft account it will reset to your premium skin.") }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form class="w-full" @submit.prevent="submitResetSkinForm">
                                        <div class="mt-6">
                                            <LoadingButton
                                                :loading="form.processing"
                                                variant="destructive"
                                                :disabled="formDisabled"
                                                type="submit"
                                            >
                                                {{ __("Reset to Default Skin") }}
                                            </LoadingButton>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </div>
                </div>
            </Tabs>
        </div>
    </AppLayout>
</template>
