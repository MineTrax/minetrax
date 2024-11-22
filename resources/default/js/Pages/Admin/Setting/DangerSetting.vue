<script setup>
// import LoadingButton from '@/Components/LoadingButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useHelpers } from '@/Composables/useHelpers';

const {formatTimeAgoToNow} = useHelpers();

defineProps({
    inProgressList: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <AdminLayout>
    <app-head
      :title="__('Dangerzone')"
    />

    <div class="flex max-w-6xl px-10 py-12 mx-auto">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="w-full bg-white rounded shadow dark:bg-cool-gray-800">
            <div class="px-6 py-4 font-bold text-red-500 border-b dark:border-gray-700 dark:text-red-500">
              <span>{{ __("Dangerzone") }}</span>
              <span class="float-right text-sm italic font-normal text-gray-500">{{ __("Be cautious with your actions!") }}</span>
            </div>

            <div
              id="box-container"
              class="mt-5 divide-y divide-gray-200 md:mt-0 dark:divide-gray-700"
            >
              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Shouts") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all shouts from shoutbox.") }}
                  </p>

                  <div
                    v-if="inProgressList?.truncateShouts"
                    class="text-sm mt-4 p-4 max-w-2xl border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                  >
                    <p>
                      {{
                        __("Action already in progress. Please wait before starting again.")
                      }}
                    </p>
                    <p>
                      {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateShouts) }) }}
                    </p>
                  </div>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Shouts?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.shouts')"
                    method="delete"
                    class="inline-flex items-center px-4 py-2 ml-4 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Shouts") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Console Logs") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all console logs of all servers.") }}
                  </p>

                  <div
                    v-if="inProgressList?.truncateConsolelogs"
                    class="text-sm mt-4 p-4 max-w-2xl border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                  >
                    <p>
                      {{
                        __("Action already in progress. Please wait before starting again.")
                      }}
                    </p>
                    <p>
                      {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateConsolelogs) }) }}
                    </p>
                  </div>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Console Logs?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.consolelogs')"
                    method="delete"
                    class="inline-flex items-center px-4 py-2 ml-4 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Consolelogs") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Chat History") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all chat history for all servers.") }}
                  </p>

                  <div
                    v-if="inProgressList?.truncateChatlogs"
                    class="text-sm mt-4 p-4 max-w-2xl border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                  >
                    <p>
                      {{
                        __("Action already in progress. Please wait before starting again.")
                      }}
                    </p>
                    <p>
                      {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateChatlogs) }) }}
                    </p>
                  </div>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all recorded Chat History?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.chatlogs')"
                    method="delete"
                    class="inline-flex items-center px-4 py-2 ml-4 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Chat History") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    {{ __("Delete Player Intel") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all player related stats for all servers. It will also unlink all players linked to user and then delete all player data.") }}
                  </p>

                  <div
                    v-if="inProgressList?.truncatePlayerIntelData"
                    class="text-sm mt-4 p-4 max-w-2xl border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                  >
                    <p>
                      {{
                        __("Action already in progress. Please wait before starting again.")
                      }}
                    </p>
                    <p>
                      {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncatePlayerIntelData) }) }}
                    </p>
                  </div>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Player Intel/Statistics?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.playerintel')"
                    method="delete"
                    class="inline-flex items-center px-4 py-2 ml-4 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Player Intel") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    {{ __("Delete Server Intel") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all the tracked Server Intel (Analytics) data for all servers. Eg: performance, activities data etc.") }}
                  </p>

                  <div
                    v-if="inProgressList?.truncateServerIntelData"
                    class="text-sm mt-4 p-4 max-w-2xl border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                  >
                    <p>
                      {{
                        __("Action already in progress. Please wait before starting again.")
                      }}
                    </p>
                    <p>
                      {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateServerIntelData) }) }}
                    </p>
                  </div>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Server Analytics/Intel data?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.serverintel')"
                    method="delete"
                    class="inline-flex items-center px-4 py-2 ml-4 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Server Intel") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                    {{ __("Delete Player Punishments (BanWarden)") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all player punishments related data.") }}
                  </p>

                  <div
                    v-if="inProgressList?.truncatePlayerPunishments"
                    class="text-sm mt-4 p-4 max-w-2xl border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                  >
                    <p>
                      {{
                        __("Action already in progress. Please wait before starting again.")
                      }}
                    </p>
                    <p>
                      {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncatePlayerPunishments) }) }}
                    </p>
                  </div>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Player Punishments?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.playerpunishments')"
                    method="delete"
                    class="inline-flex items-center px-4 py-2 ml-4 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Player Punishments") }}</span>
                  </InertiaLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
