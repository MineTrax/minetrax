<script setup>
import Multiselect from 'vue-multiselect';
import XInput from '@/Components/Form/XInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import LoadingButton from '@/Components/LoadingButton.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { computed } from 'vue';

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

const tabList = ['Upload Skin', 'From URL', 'From Username', 'Reset'];

const file = ref(null);
const form = useForm({
    action_type: 'upload', // 'upload', 'url', 'username', 'reset'
    player_uuid: null,
    // upload type
    skin_type: 'steve',
    file: null,
    // name type
    username: '',
    // url type
    url: '',
});

const submitUploadSkinForm = () => {
    form.action_type = 'upload';
    form.file = file.value.files[0];
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route('change-player-skin.update'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const submitUrlSkinForm = () => {
    form.action_type = 'url';
    form.username = null;
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route('change-player-skin.update'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const submitUsernameSkinForm = () => {
    form.action_type = 'username';
    form.url = null;
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route('change-player-skin.update'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const submitResetSkinForm = () => {
    form.action_type = 'reset';
    form.url = null;
    form.username = null;
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route('change-player-skin.update'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const validSkinUrlSampleList = [
    'https://namemc.com/skin/a569a3e7aad87b3a',
    'https://minesk.in/7a8d3a710c5b440a875d9b6fb4d7d9a3',
    'http://novask.in/6673493202.png',
    'http://textures.minecraft.net/texture/63741c4509672cc31e43750d5223d4b3099f851e8039651550e98719692dd028',
];

const formDisabled = computed(() => {
    return !props.hasServersWithFeature || form.processing || !selectedPlayer.value;
});

</script>

<template>
  <AppLayout>
    <AppHead :title="__('Change Player Skin')" />
    <div class="max-w-6xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <h2
          v-if="selectedPlayer"
          class="text-lg mb-2 md:mb-0 md:text-2xl font-bold text-gray-700 dark:text-gray-200"
        >
          {{
            __("Change skin for :username", {
              username: selectedPlayer.username,
            })
          }}
        </h2>
        <h2
          v-else
          class="text-lg italic mb-2 md:mb-0 md:text-2xl font-bold text-gray-500 dark:text-gray-500"
        >
          {{ __("No Linked Players") }}
        </h2>

        <Multiselect
          id="country"
          v-model="selectedPlayer"
          class="w-full md:w-1/3 bg-gray-300 border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm dark:bg-gray-900"
          :options="players"
          :multiple="false"
          :placeholder="__('Search') + '...'"
          label="username"
          :allow-empty="false"
          :deselect-label="__('Can\'t remove')"
          track-by="id"
        />
      </div>

      <p
        v-if="selectedPlayer"
        class="text-xs text-gray-500 dark:text-gray-500"
      >
        {{ __("Player Uuid") }}: {{ selectedPlayer.uuid }}
      </p>

      <AlertCard
        v-if="!hasServersWithFeature"
        text-color="text-red-800 dark:text-red-500"
        border-color="border-red-500"
      >
        <p class="text-sm">
          {{ __("Oh Jeez! No server support changing of skin yet!") }}
        </p>
        <template #body>
          <p class="text-gray-500 dark:text-gray-400">
            {{
              __(
                "This feature is not enabled in any of the servers. Below form will be disabled. "
              )
            }}
          </p>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __("Please check back later or contact the server administrator.") }}
          </p>
        </template>
      </AlertCard>

      <AlertCard
        v-if="cooldown"
        text-color="text-yellow-800 dark:text-yellow-500"
        border-color="border-yellow-500"
      >
        {{ __("You are on a cooldown! Please wait for :cooldown seconds before you can try again.", {
          cooldown: cooldown
        }) }}
      </AlertCard>

      <AlertCard
        v-if="!selectedPlayer"
        text-color="text-red-800 dark:text-red-500"
        border-color="border-red-500"
      >
        {{ __("No linked players found. Please link a player to your account.") }}
      </AlertCard>

      <TabGroup
        as="div"
        class="md:flex"
      >
        <TabList
          as="ul"
          class="mb-4 space-y-4 text-sm font-medium text-gray-500 flex-column space-y dark:text-gray-400 md:me-4 md:mb-0"
        >
          <Tab
            v-for="(tab, index) in tabList"
            :key="index"
            v-slot="{ selected }"
            as="li"
            class="focus:outline-none whitespace-nowrap"
          >
            <a
              href="#"
              :class="{
                'inline-flex items-center w-full px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white':
                  !selected,
                'inline-flex items-center w-full px-4 py-3 text-white bg-gray-500 rounded-lg active dark:bg-gray-600':
                  selected,
              }"
              aria-current="page"
            >{{ tab }}</a>
          </Tab>
        </TabList>
        <TabPanels
          as="div"
          class="w-full"
        >
          <TabPanel
            as="div"
            class="w-full p-6 text-gray-500 rounded-lg bg-gray-50 text-medium dark:text-gray-400 dark:bg-gray-800"
          >
            <h3
              class="mb-2 text-lg font-bold text-gray-900 dark:text-white"
            >
              {{ __("Change Skin") }} - {{ __("Upload Skin") }}
            </h3>

            <div class="flex flex-col md:flex-row justify-between pr-4">
              <div>
                <p class="mb-2 text-sm">
                  {{ __("Please upload a valid skin image. ⚠️") }}
                </p>
                <p class="text-sm">
                  {{ __("Refer to the provided sample guide image to understand which image should be upload.") }}
                  <br>
                  {{ __("A valid skin image should look like this: ") }}
                  <a
                    class="text-blue-500 dark:text-blue-400 hover:underline"
                    href="https://mc-heads.net/skin/xinecraft.png"
                    target="_blank"
                  >https://mc-heads.net/skin/xinecraft.png</a>
                </p>
              </div>
              <img
                src="/images/valid-skin-format.png"
                alt="Skin Sample"
                class="w48 h-40"
              >
            </div>

            <form
              class="w-full border-t mt-4 border-gray-300 dark:border-gray-700"
              @submit.prevent="submitUploadSkinForm"
            >
              <div class="col-span-6 sm:col-span-6 pt-4 space-y-3">
                <p class="text-sm text-gray-700 font-bold dark:text-gray-300">
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
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        name="skin_type"
                      >
                    </div>
                    <div class="ml-2 text-sm">
                      <label
                        for="skin_type_steve"
                        class="font-medium text-gray-900 dark:text-gray-300"
                      >{{ __("Steve") }}</label>
                    </div>
                  </div>
                  <div class="flex">
                    <div class="flex items-center h-5">
                      <input
                        id="skin_type_alex"
                        v-model="form.skin_type"
                        type="radio"
                        value="alex"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        name="skin_type"
                      >
                    </div>
                    <div class="ml-2 text-sm">
                      <label
                        for="skin_type_alex"
                        class="font-medium text-gray-900 dark:text-gray-300"
                      >{{ __("Alex") }}</label>
                    </div>
                  </div>
                </div>
              </div>


              <div class="col-span-6 sm:col-span-6 pt-4 space-y-3">
                <p class="text-sm text-gray-700 font-bold dark:text-gray-300">
                  {{ __("Skin File") }}
                </p>

                <div class="flex gap-6">
                  <input
                    id="file"
                    ref="file"
                    accept=".png"
                    type="file"
                    class="block p-2 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                    required
                  >
                </div>
                <p
                  v-if="form.errors.file"
                  class="text-xs text-red-500"
                >
                  {{ form.errors.file }}
                </p>
              </div>

              <div class="mt-6">
                <LoadingButton
                  :loading="form.processing"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                  :disabled="formDisabled"
                  type="submit"
                >
                  {{ __("Change Skin") }}
                </LoadingButton>
              </div>
            </form>
          </TabPanel>
          <TabPanel
            as="div"
            class="w-full p-6 text-gray-500 rounded-lg bg-gray-50 text-medium dark:text-gray-400 dark:bg-gray-800"
          >
            <h3
              class="mb-2 text-lg font-bold text-gray-900 dark:text-white"
            >
              {{ __("Change Skin") }} - {{ __("From URL") }}
            </h3>
            <div class="text-sm">
              <p class="mb-2">
                {{ __("Change your skin by providing a valid skin URL. You can find skin from namemc.com, mineskin.org and other skin websites.") }}
              </p>
              <p>
                {{ __("Here are some examples of valid skin url:") }}
                <ul class="list-disc list-inside">
                  <li
                    v-for="url in validSkinUrlSampleList"
                    :key="url"
                    class="list-item break-all"
                  >
                    {{ url }}
                  </li>
                </ul>
              </p>
            </div>

            <form
              class="w-full mt-4"
              @submit.prevent="submitUrlSkinForm"
            >
              <div class="col-span-6 sm:col-span-6">
                <x-input
                  id="url"
                  v-model="form.url"
                  :label="__('Skin URL')"
                  :error="form.errors.url"
                  type="text"
                  name="url"
                  help-error-flex="flex-row"
                />
              </div>

              <div class="mt-6">
                <LoadingButton
                  :loading="form.processing"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                  :disabled="formDisabled"
                  type="submit"
                >
                  {{ __("Change Skin") }}
                </LoadingButton>
              </div>
            </form>
          </TabPanel>
          <TabPanel
            as="div"
            class="w-full p-6 text-gray-500 rounded-lg bg-gray-50 text-medium dark:text-gray-400 dark:bg-gray-800"
          >
            <h3
              class="mb-2 text-lg font-bold text-gray-900 dark:text-white"
            >
              {{ __("Change Skin") }} - {{ __("From Username") }}
            </h3>
            <p class="mb-2 text-sm">
              {{ __("Change your skin by providing a valid Minecraft username (Premium account).") }}
            </p>
            <p class="text-sm">
              {{ __("The skin will be fetched from Mojang server.") }}
            </p>


            <form
              class="w-full mt-4"
              @submit.prevent="submitUsernameSkinForm"
            >
              <div class="col-span-6 sm:col-span-6">
                <x-input
                  id="username"
                  v-model="form.username"
                  :label="__('Eg: xinecraft')"
                  :error="form.errors.username"
                  type="text"
                  name="username"
                  help-error-flex="flex-row"
                />
              </div>

              <div class="mt-6">
                <LoadingButton
                  :loading="form.processing"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                  :disabled="formDisabled"
                  type="submit"
                >
                  {{ __("Change Skin") }}
                </LoadingButton>
              </div>
            </form>
          </TabPanel>
          <TabPanel
            as="div"
            class="w-full p-6 text-gray-500 rounded-lg bg-gray-50 text-medium dark:text-gray-400 dark:bg-gray-800"
          >
            <h3
              class="mb-2 text-lg font-bold text-gray-900 dark:text-white"
            >
              {{ __("Change Skin") }} - {{ __("Reset") }}
            </h3>
            <p class="mb-2 text-sm">
              {{ __("Reset your skin to default Minecraft skin. If you have premium minecraft account it will reset to your premium skin.") }}
            </p>

            <form
              class="w-full mt-4"
              @submit.prevent="submitResetSkinForm"
            >
              <div class="mt-6">
                <LoadingButton
                  :loading="form.processing"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                  :disabled="formDisabled"
                  type="submit"
                >
                  {{ __("Reset to Default Skin") }}
                </LoadingButton>
              </div>
            </form>
          </TabPanel>
        </TabPanels>
      </TabGroup>
    </div>
  </AppLayout>
</template>
