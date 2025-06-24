<template>
  <Card v-if="$page.props.generalSettings.enable_shoutbox">
    <CardContent class="p-3 space-y-4">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Shout Box") }}
      </h3>

      <div class="overflow-auto flex-col space-y-4 max-h-96 hide-scrollbar">
        <!--Loading-->
        <div
          v-if="loading"
          class="space-y-4"
        >
          <!-- Other user message skeleton -->
          <div class="chat-message">
            <div class="flex">
              <div class="order-2 items-start mx-2 space-y-2 max-w-xs text-sm">
                <div class="inline-block flex flex-col px-4 py-2 rounded-2xl rounded-tl-lg bg-popover">
                  <div class="mb-2">
                    <Skeleton class="inline-block w-16 h-4" />
                    <Skeleton class="inline-block ml-1 w-12 h-3" />
                  </div>
                  <div class="space-y-1">
                    <Skeleton class="w-32 h-4" />
                    <Skeleton class="w-24 h-4" />
                  </div>
                </div>
              </div>
              <Skeleton class="order-1 mt-2 w-8 h-8 rounded-full" />
            </div>
          </div>

          <!-- Self message skeleton -->
          <div class="chat-message">
            <div class="flex justify-end">
              <div class="order-1 items-start mx-2 space-y-2 max-w-xs text-sm">
                <div class="inline-block flex flex-col px-4 py-2 rounded-2xl rounded-tr-lg bg-primary">
                  <div class="mb-2 text-right">
                    <Skeleton class="inline-block mr-1 w-12 h-3" />
                    <Skeleton class="inline-block w-16 h-4" />
                  </div>
                  <div class="space-y-1">
                    <Skeleton class="w-28 h-4" />
                    <Skeleton class="w-20 h-4" />
                  </div>
                </div>
              </div>
              <Skeleton class="order-2 mt-2 w-8 h-8 rounded-full" />
            </div>
          </div>

          <!-- Another other user message skeleton -->
          <div class="chat-message">
            <div class="flex">
              <div class="order-2 items-start mx-2 space-y-2 max-w-xs text-sm">
                <div class="inline-block flex flex-col px-4 py-2 rounded-2xl rounded-tl-lg bg-popover">
                  <div class="mb-2">
                    <Skeleton class="inline-block w-20 h-4" />
                    <Skeleton class="inline-block ml-1 w-10 h-3" />
                  </div>
                  <div class="space-y-1">
                    <Skeleton class="w-36 h-4" />
                  </div>
                </div>
              </div>
              <Skeleton class="order-1 mt-2 w-8 h-8 rounded-full" />
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
            v-if="!$page.props.auth.user || shout.user_id !== $page.props.auth.user.id"
            class="flex"
          >
            <div class="order-2 items-start mx-2 space-y-2 max-w-xs text-sm">
              <div
                class="flex flex-col px-4 py-2 rounded-2xl rounded-tl-lg text-card-foreground bg-popover"
              >
                <div>
                  <inertia-link
                    as="span"
                    :href="route('user.public.get', shout.user.username)"
                    class="font-semibold cursor-pointer hover:underline"
                    :style="[shout.user.roles[0].color ? {color: shout.user.roles[0].color} : null]"
                  >
                    {{ shout.user.username }}
                  </inertia-link>
                  <span
                    v-tippy
                    class="ml-1 text-xs text-muted-foreground focus:outline-none"
                    :title="formatToDayDateString(shout.created_at)"
                  >
                    {{ formatTimeAgoToNow(shout.created_at, false) }}
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
              class="order-1 mt-2 w-8 h-8 rounded-full"
            >
            <inertia-link
              v-if="$page.props.auth.user && shout.permissions.delete"
              v-confirm="{message:__('Delete this shout permanently?')}"
              class="order-3 focus:outline-none"
              :preserve-state="false"
              as="button"
              method="delete"
              :href="route('shout.delete', shout.id)"
            >
              <icon
                class="w-4 h-4 text-muted-foreground hover:text-destructive"
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
              v-if="$page.props.auth.user && shout.permissions.delete"
              v-confirm="{message:__('Delete this shout permanently?')}"
              class="focus:outline-none"
              :preserve-state="false"
              as="button"
              method="delete"
              :href="route('shout.delete', shout.id)"
            >
              <icon
                class="w-4 h-4 text-muted-foreground hover:text-destructive"
                name="trash"
              />
            </inertia-link>
            <div class="order-1 items-start mx-2 space-y-2 max-w-xs text-sm">
              <div
                class="flex flex-col px-4 py-2 rounded-2xl rounded-tr-lg text-primary-foreground bg-primary"
              >
                <div class="text-right">
                  <span
                    v-tippy
                    class="mr-1 text-xs text-primary-foreground/70 focus:outline-none"
                    :title="formatToDayDateString(shout.created_at)"
                  >
                    {{ formatTimeAgoToNow(shout.created_at, false) }}
                  </span>
                  <inertia-link
                    as="span"
                    :href="route('user.public.get', shout.user.username)"
                    class="font-semibold cursor-pointer hover:underline"
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
              class="order-2 mt-2 w-8 h-8 rounded-full"
            >
          </div>
          <!-- self end -->
        </div>

        <div
          v-if="!loading && (!shouts || shouts.length <= 0)"
          class="italic text-center text-muted-foreground"
        >
          {{ __("No shouts yet.") }}
        </div>
      </div>

      <div v-if="$page.props.auth.user">
        <Input
          v-if="!loading"
          ref="inputbox"
          v-model="message"
          :disabled="sending"
          aria-label="Shout"
          type="text"
          :placeholder="__('Say something..')"
          @keypress.enter="sendShout"
        />
        <span
          v-if="error"
          class="text-xs text-destructive"
        >{{ error }}</span>
      </div>
      <div
        v-else
        class="text-sm text-center text-card-foreground"
      >
        <inertia-link
          class="font-semibold text-primary"
          :href="route('login')"
        >
          {{ __("Login") }}
        </inertia-link>
        <template v-if="$page.props.hasRegistrationFeature">
          {{ " " + __("or") }}
          <inertia-link
            class="font-semibold text-primary"
            :href="route('register')"
          >
            {{ __("Register") }}
          </inertia-link>
        </template>
        {{ __("to Shout") }}
      </div>
    </CardContent>
  </Card>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { Input } from '@/Components/ui/input';
import {USE_WEBSOCKETS} from '@/constants';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'
import { Skeleton } from '@/Components/ui/skeleton'

export default {
    components: {Icon, Card, CardContent, Input, Skeleton},
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow,formatToDayDateString};
    },
    data() {
        return {
            shouts: [],
            message: '',
            error: null,
            loading: true,
            sending: false,
            shoutQueryInterval: null,
        };
    },

    created() {
        if (!this.$page.props.generalSettings.enable_shoutbox) return;

        axios.get(route('shout.index')).then(data => {
            this.shouts = data.data;
        }).finally(() => {
            this.loading = false;
        });

        if (USE_WEBSOCKETS) {
            Echo.channel('shouts').listen('ShoutCreated', data => {
                this.shouts.unshift(data.data);
            });
        } else {
            this.shoutQueryInterval = setInterval(() => this.pollServerForNewShouts(), 5000);
        }
    },

    unmounted() {
        if (this.shoutQueryInterval) {
            clearInterval(this.shoutQueryInterval);
        }
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
                    this.error = this.__('Something went wrong. Try again.');
            }).finally(() => {
                this.message = '';
                this.sending = false;
                this.$nextTick(() => {
                    this.$refs.inputbox.$el.focus();
                });
            });
        },

        pollServerForNewShouts() {
            if (USE_WEBSOCKETS) return;

            const afterId = this.shouts.length > 0 ? this.shouts[0].id : 0;
            axios.get(route('shout.index', {after: afterId})).then(data => {
                const newShouts = data.data;
                if (newShouts.length > 0) {
                    this.shouts = [...newShouts, ...this.shouts];
                }
            });
        },
    }
};
</script>
