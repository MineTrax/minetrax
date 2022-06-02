<template>
  <div>
    <div class="p-3 bg-white dark:bg-cool-gray-800 rounded shadow space-y-4">
      <h3 class="text-gray-800 font-extrabold dark:text-gray-200">
        Shout Box
      </h3>

      <div class="flex-col space-y-4 max-h-96 overflow-auto hide-scrollbar">
        <!--Loading-->
        <div
          v-if="loading"
          class="space-y-4"
        >
          <div class="max-w-sm w-full mx-auto">
            <div class="animate-pulse flex space-x-4">
              <div class="rounded-full bg-gray-300 dark:bg-cool-gray-700 h-8 w-8" />
              <div class="flex-1 space-y-1 py-1">
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
              </div>
            </div>
          </div>
          <div class="max-w-sm w-full mx-auto">
            <div class="animate-pulse flex space-x-4">
              <div class="rounded-full bg-gray-300 dark:bg-cool-gray-700 h-8 w-8" />
              <div class="flex-1 space-y-1 py-1">
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
              </div>
            </div>
          </div>
        </div>

        <div
          v-for="shout in shouts"
          :key="shout.id"
          class="chat-message"
        >
          <!-- Message of others start -->
          <div
            v-if="!$page.props.user || shout.user_id !== $page.props.user.id"
            class="flex"
          >
            <div class="space-y-2 text-sm max-w-xs mx-2 order-2 items-start">
              <div
                class="px-4 py-2 flex flex-col rounded-2xl inline-block rounded-tl-lg bg-gray-100 text-gray-700 dark:bg-cool-gray-600 dark:bg-opacity-25 dark:text-gray-200"
              >
                <div>
                  <inertia-link
                    as="span"
                    :href="route('user.public.get', shout.user.username)"
                    class="cursor-pointer hover:underline font-semibold"
                    :style="[shout.user.roles[0].color ? {color: shout.user.roles[0].color} : null]"
                  >
                    {{ shout.user.username }}
                  </inertia-link>
                  <span
                    v-tippy
                    class="ml-1 text-gray-500 dark:text-gray-400 text-xs focus:outline-none"
                    :title="format(new Date(shout.created_at), 'E, do MMM yyyy, h:mm aaa')"
                  >
                    {{ formatDistanceToNowStrict(new Date(shout.created_at)) }}
                  </span>
                </div>
                <span class="text-justify">
                  {{ shout.message }}
                </span>
              </div>
            </div>
            <img
              :src="shout.user.profile_photo_url"
              alt="My profile"
              class="w-8 h-8 rounded-full order-1 mt-2"
            >
            <inertia-link
              v-if="$page.props.user && shout.permissions.delete"
              class="focus:outline-none order-3"
              :preserve-state="false"
              as="button"
              method="delete"
              :href="route('shout.delete', shout.id)"
            >
              <icon
                class="text-gray-200 dark:text-gray-500 w-4 h-4 hover:text-red-400 dark:hover:text-red-500"
                name="trash"
              />
            </inertia-link>
          </div>
          <!-- other end -->

          <!-- Message of self start -->
          <div
            v-else
            class="flex justify-end"
          >
            <inertia-link
              v-if="$page.props.user && shout.permissions.delete"
              class="focus:outline-none"
              :preserve-state="false"
              as="button"
              method="delete"
              :href="route('shout.delete', shout.id)"
            >
              <icon
                class="text-gray-200 dark:text-gray-500 w-4 h-4 hover:text-red-400 dark:hover:text-red-500"
                name="trash"
              />
            </inertia-link>
            <div class="space-y-2 text-sm max-w-xs mx-2 order-1 items-start">
              <div
                class="px-4 py-2 flex flex-col rounded-2xl inline-block rounded-tr-lg bg-light-blue-100 text-gray-700 dark:bg-cool-gray-900 dark:bg-opacity-40 dark:text-gray-200"
              >
                <div class="text-right">
                  <span
                    v-tippy
                    class="mr-1 text-gray-500 dark:text-gray-400 text-xs focus:outline-none"
                    :title="format(new Date(shout.created_at), 'E, do MMM yyyy, h:mm aaa')"
                  >
                    {{ formatDistanceToNowStrict(new Date(shout.created_at)) }}
                  </span>
                  <inertia-link
                    as="span"
                    :href="route('user.public.get', shout.user.username)"
                    class="cursor-pointer hover:underline font-semibold"
                    :style="[shout.user.roles[0].color ? {color: shout.user.roles[0].color} : null]"
                  >
                    {{ shout.user.username }}
                  </inertia-link>
                </div>
                <span class="text-justify">
                  {{ shout.message }}
                </span>
              </div>
            </div>
            <img
              :src="shout.user.profile_photo_url"
              alt="My profile"
              class="w-8 h-8 rounded-full order-2 mt-2"
            >
          </div>
          <!-- self end -->
        </div>
      </div>

      <div v-if="$page.props.user">
        <input
          v-if="!loading"
          ref="inputbox"
          v-model="message"
          :disabled="sending"
          aria-label="Shout"
          class="mt-1 focus:ring-gray-300 block w-full sm:text-sm rounded-md border-none disabled:opacity-50 bg-gray-100 focus:bg-white dark:bg-cool-gray-900 dark:text-gray-200 dark:focus:ring-gray-700"
          type="text"
          placeholder="Say something.."
          @keypress.enter="sendShout"
        >
        <span
          v-if="error"
          class="text-red-400 text-xs"
        >{{ error }}</span>
      </div>
      <div
        v-else
        class="text-sm text-gray-600 dark:text-gray-400 text-center"
      >
        <inertia-link
          class="font-semibold text-light-blue-500"
          :href="route('login')"
        >
          Login
        </inertia-link>
        or
        <inertia-link
          class="font-semibold text-light-blue-500"
          :href="route('register')"
        >
          Register
        </inertia-link>
        to Shout
      </div>
    </div>
  </div>
</template>

<script>
import {formatDistanceToNowStrict, format} from 'date-fns';
import Icon from '@/Components/Icon';

export default {
    components: {Icon},
    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            format: format,
            shouts: [],
            message: '',
            error: null,
            loading: true,
            sending: false
        };
    },

    created() {
        axios.get(route('shout.index')).then(data => {
            this.shouts = data.data;
        }).finally(e => {
            this.loading = false;
        });

        Echo.channel('shouts').listen('ShoutCreated', data => {
            this.shouts.unshift(data.data);
        });
    },

    methods: {
        sendShout() {
            this.sending = true;
            this.error = null;
            axios.post(route('shout.store'), {
                message: this.message
            }).then(data => {
                if (data.status === 200) {
                    this.shouts.unshift(data.data);
                }
            }).catch(e => {
                if (e.response.status === 422)
                    this.error = e.response.data.errors.message[0];
                else if (e.response.status === 403)
                    this.error = e.response.data.message;
                else
                    this.error = 'Something went wrong';
            }).finally(e => {
                this.message = '';
                this.sending = false;
                this.$nextTick(() => {
                    this.$refs.inputbox.focus();
                });
            });
        }
    }

};
</script>
