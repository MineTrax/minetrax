<script setup>
// import LoadingButton from '@/Components/LoadingButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
});
</script>

<template>
  <AdminLayout>
    <app-head
      :title="__('Dangerzone')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full rounded">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-red-500 text-red-500 font-bold">
              <span>{{ __("Dangerzone") }}</span>
              <span class="text-sm float-right font-normal text-gray-500 italic">{{ __("Be cautious with your actions!") }}</span>
            </div>

            <div
              id="box-container"
              class="mt-5 md:mt-0 divide-y divide-gray-200 dark:divide-gray-700"
            >
              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Shouts") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all shouts from shoutbox.") }}
                  </p>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Shouts?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.shouts')"
                    method="delete"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Shouts") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Console Logs") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all console logs of all servers.") }}
                  </p>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Console Logs?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.consolelogs')"
                    method="delete"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Consolelogs") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Chat History") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all chat history for all servers.") }}
                  </p>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all recorded Chat History?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.chatlogs')"
                    method="delete"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Chat History") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Statistics") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all the recorded player stats for all servers. This doesn't include Intel(Analytics data).") }}
                  </p>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Player Statistics?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.playerstats')"
                    method="delete"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Player Stats") }}</span>
                  </InertiaLink>
                </div>
              </div>

              <div class="flex justify-between p-5">
                <div class="flex flex-col">
                  <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                    {{ __("Delete all Analytics Data") }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Permanently delete all the tracked Intel (Player & Server Analytics) data for all servers.") }}
                  </p>
                </div>
                <div>
                  <InertiaLink
                    v-tippy
                    v-confirm="{message: __('Are you sure you want to delete all Analytics/Intel data?')}"
                    as="button"
                    :href="route('admin.setting.danger.truncate.inteldata')"
                    method="delete"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <span>{{ __("Delete Intel Data") }}</span>
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
