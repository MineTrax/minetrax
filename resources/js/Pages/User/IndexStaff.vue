<template>
  <app-layout>
    <app-head
      :title="__('Staff Members')"
    />

    <div class="px-2 py-4 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex flex-col md:space-x-4 md:flex-row">
        <div class="flex-grow">
          <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
              <div class="shadow space-y-10 bg-white dark:bg-cool-gray-800 overflow-hidden border-b border-gray-200 dark:border-gray-800 rounded p-4">
                <div
                  v-for="role in rolesWithUsers"
                  :key="role.id"
                >
                  <div
                    id="title"
                    class="flex flex-col items-center"
                  >
                    <h1
                      class="text-lg font-extrabold text-light-blue-400"
                      :style="[role.color ? {color: role.color} : null]"
                    >
                      {{ role.display_name }}
                    </h1>
                    <img
                      :src="role.photo_url"
                      alt="Role Image"
                      class="max-h-32 mb-2"
                    >
                  </div>
                  <div
                    v-if="role.users.length > 0"
                    class="flex justify-center flex-wrap"
                  >
                    <inertia-link
                      v-for="user in role.users"
                      :key="user.id"
                      as="div"
                      :href="route('user.public.get', user.username)"
                      class="inline-flex mb-2 mx-2 p-2 rounded border hover:border-light-blue-400 dark:border-gray-700 dark:hover:border-light-blue-400 cursor-pointer"
                    >
                      <img
                        class="h-14 w-14"
                        :src="user.profile_photo_url"
                        alt="Profile Photo"
                      >
                      <div class="flex flex-col px-2">
                        <span class="font-semibold dark:text-gray-200">{{ user.name }}</span>
                        <span class="text-gray-600 dark:text-gray-400">@{{ user.username }}</span>
                      </div>
                    </inertia-link>
                  </div>
                  <div
                    v-else
                    class="flex text-gray-600 dark:text-gray-400 italic justify-center"
                  >
                    {{ __("No :role yet.", {role: role.display_name}) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="flex flex-col space-y-4 flex-none md:w-1/4 h-screen sticky top-5 mt-4 md:mt-0">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import ShoutBox from '@/Shared/ShoutBox';
import ServerStatusBox from '@/Shared/ServerStatusBox';

export default {

    components: {
        ServerStatusBox,
        AppLayout,
        ShoutBox,
    },
    props: {
        rolesWithUsers: Array
    },
};
</script>
