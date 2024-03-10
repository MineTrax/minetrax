<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';

defineProps({
    staffs: {
        type: Array,
        required: true,
    },
});
</script>

<template>
  <app-layout>
    <app-head :title="__('Staff Members')" />

    <div class="px-2 py-4 mx-auto md:py-12 md:px-10 max-w-7xl">
      <div class="flex flex-col md:space-x-4 md:flex-row">
        <div class="flex-grow">
          <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
            <div
              class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
            >
              <div
                class="p-4 space-y-10 overflow-hidden bg-white border-b border-gray-200 rounded shadow dark:bg-cool-gray-800 dark:border-gray-800"
              >
                <h2
                  class="mt-2 text-2xl font-bold text-center text-gray-700 dark:text-gray-300"
                >
                  {{ __("Meet the Team") }}
                </h2>

                <div
                  v-if="staffs.length > 0"
                  class="grid gap-4 grid:cols-1 md:grid-cols-3"
                >
                  <InertiaLink
                    v-for="staff in staffs"
                    :key="staff.id"
                    class="flex flex-col items-center cursor-pointer hover:opacity-50"
                    as="div"
                    :href="
                      route(
                        'user.public.get',
                        staff.username
                      )
                    "
                  >
                    <img
                      class="w-40 h-40 rounded-lg drop-shadow"
                      :src="staff.profile_photo_url"
                      alt="Profile Photo"
                    >
                    <div
                      class="flex flex-col items-center mt-2"
                    >
                      <h3
                        class="font-bold text-gray-800 dark:text-gray-300"
                      >
                        {{ staff.name }}
                      </h3>
                      <p
                        :style="[
                          staff.roles[0].color
                            ? {
                              color: staff
                                .roles[0]
                                .color,
                            }
                            : null,
                        ]"
                        class="text-sm text-gray-700 dark:text-gray-400"
                      >
                        {{
                          staff.roles[0].display_name
                        }}
                      </p>
                    </div>
                  </InertiaLink>
                </div>

                <div
                  v-else
                  class="flex justify-center italic text-gray-600 dark:text-gray-400"
                >
                  {{ __("No Staff Yet!") }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="sticky flex flex-col flex-none h-screen mt-4 space-y-4 md:w-1/4 top-5 md:mt-0"
        >
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>
