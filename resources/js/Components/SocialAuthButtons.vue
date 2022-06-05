<template>
  <div
    v-if="shouldDisplayThisPage"
    id="social-auth"
  >
    <div class="flex justify-between items-center mt-3">
      <hr class="w-full dark:border-gray-600">
      <span class="p-2 text-gray-400 dark:text-gray-500 whitespace-nowrap text-sm">Or continue with</span>
      <hr class="w-full dark:border-gray-600">
    </div>

    <div class="flex justify-around flex-wrap">
      <a
        v-if="$page.props.enabledSocialAuths.google"
        :href="route('social.login', 'google')"
        class="inline-flex mt-1 justify-center py-2 px-9 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
      >
        <icon
          name="google"
          class="h-5 w-5 fill-current"
        />
      </a>
      <a
        v-if="$page.props.enabledSocialAuths.facebook"
        :href="route('social.login', 'facebook')"
        class="inline-flex mt-1 justify-center py-2 px-9 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-700 disabled:opacity-50"
      >
        <icon
          name="facebook"
          class="w-5 h-5 fill-current"
        />
      </a>
      <a
        v-if="$page.props.enabledSocialAuths.twitter"
        :href="route('social.login', 'twitter')"
        class="inline-flex mt-1 justify-center py-2 px-9 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-400 hover:bg-light-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
      >
        <icon
          name="twitter"
          class="h-5 w-5 fill-current"
        />
      </a>
      <a
        v-if="$page.props.enabledSocialAuths.discord"
        :href="route('social.login', 'discord')"
        class="inline-flex mt-1 justify-center py-2 px-9 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-500 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 disabled:opacity-50"
      >
        <icon
          name="discord"
          class="h-5 w-5 fill-current"
        />
      </a>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon';

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
