<template>
  <div
    v-if="shouldDisplayThisPage"
    id="social-auth"
  >
    <div
      v-if="!$page.props.disableEmailPasswordAuth"
      class="flex items-center justify-between mt-3"
    >
      <hr class="w-full dark:border-secondary-600">
      <span
        class="p-2 text-secondary-400 dark:text-secondary-500 whitespace-nowrap text-sm"
      >{{ __("Or continue with") }}
      </span>
      <hr class="w-full dark:border-secondary-600">
    </div>
    <div
      v-else
      class="mb-4"
    >
      <h2 class="text-2xl font-bold text-secondary-800 dark:text-secondary-200">
        {{ __("Sign in") }}
      </h2>
      <span class="text-sm text-secondary-500 dark:text-secondary-300">{{ __("with social to continue.") }}</span>
    </div>

    <div
      class="flex flex-wrap justify-around"
      :class="[$page.props.disableEmailPasswordAuth ? 'flex-col space-y-4' : '']"
    >
      <a
        v-if="$page.props.enabledSocialAuths.google"
        :href="route('social.login', 'google')"
        class="inline-flex justify-center py-2 mt-1 space-x-4 text-sm font-medium text-error-500 border rounded-md shadow-sm px-9 dark:border-transparent dark:text-white dark:bg-error-500 hover:bg-surface-50 dark:hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-500 disabled:opacity-50"
      >
        <icon
          name="google"
          class="w-5 h-5 fill-current"
        />
        <span
          v-if="$page.props.disableEmailPasswordAuth"
        >{{ __("Continue with Google") }}
        </span>
      </a>
      <a
        v-if="$page.props.enabledSocialAuths.facebook"
        :href="route('social.login', 'facebook')"
        class="inline-flex justify-center py-2 mt-1 space-x-4 text-sm font-medium text-primary-700 border rounded-md shadow-sm px-9 dark:border-transparent dark:text-white dark:bg-primary-700 hover:bg-surface-50 dark:hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-700 disabled:opacity-50"
      >
        <icon
          name="facebook"
          class="w-5 h-5 fill-current"
        />
        <span
          v-if="$page.props.disableEmailPasswordAuth"
        >{{ __("Continue with Facebook") }}
        </span>
      </a>
      <a
        v-if="$page.props.enabledSocialAuths.twitter"
        :href="route('social.login', 'twitter')"
        class="inline-flex justify-center py-2 mt-1 space-x-4 text-sm font-medium border rounded-md shadow-sm px-9 dark:border-transparent dark:text-white text-primary-400 dark:bg-primary-400 hover:bg-surface-50 dark:hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
      >
        <icon
          name="twitter"
          class="w-5 h-5 fill-current"
        />
        <span
          v-if="$page.props.disableEmailPasswordAuth"
        >{{ __("Continue with Twitter") }}
        </span>
      </a>
      <a
        v-if="$page.props.enabledSocialAuths.discord"
        :href="route('social.login', 'discord')"
        class="inline-flex justify-center py-2 mt-1 space-x-4 text-sm font-medium text-primary-500 border rounded-md shadow-sm px-9 dark:border-transparent dark:text-white dark:bg-primary-500 hover:bg-surface-50 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-700 disabled:opacity-50"
      >
        <icon
          name="discord"
          class="w-5 h-5 fill-current"
        />
        <span
          v-if="$page.props.disableEmailPasswordAuth"
        >{{ __("Continue with Discord") }}
        </span>
      </a>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';

export default {
    name: 'SocialAuthButtons',
    components: {Icon},

    computed: {
        shouldDisplayThisPage:  function () {
            return this.$page.props.enabledSocialAuths &&  Object.values(this.$page.props.enabledSocialAuths).some(auth => auth);
        }
    }
};
</script>

<style scoped>

</style>
