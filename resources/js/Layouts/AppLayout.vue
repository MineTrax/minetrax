<script>
import JetBanner from '@/Jetstream/Banner.vue';
import Toast from '@/Components/Toast.vue';
import Icon from '@/Components/Icon.vue';
import AppHead from '@/Components/AppHead.vue';
import MainNavbarCustom from '@/Shared/MainNavbarCustom.vue';
import MainFooter from '@/Shared/MainFooter.vue';
import HeaderBroadcastBar from '@/Shared/HeaderBroadcastBar.vue';
import CookieConsent from '@/Components/CookieConsent.vue';

export default {
    components: {
        MainNavbarCustom,
        AppHead,
        Icon,
        Toast,
        JetBanner,
        CookieConsent,
        HeaderBroadcastBar,
        MainFooter
    },

    data() {
        return {
            title: 'MineTrax'
        };
    },
};
</script>

<template>
  <AppHead :title="title" />

  <div>
    <jet-banner />
    <toast
      :toast="$page.props.toast"
      :popstate="$page.props.popstate"
    />

    <inertia-link
      v-if="$page.props.isImpersonating"
      v-tippy
      :title="__('Leave Impersonation')"
      as="a"
      :href="route('admin.impersonate.leave')"
      class="fixed flex p-2 text-white bg-red-500 rounded-full bottom-4 right-4 hover:bg-red-700 z-50"
    >
      <icon
        name="ban"
        class="w-8 h-8"
      />
    </inertia-link>

    <div
      class="min-h-screen"
      :class="{ 'border-4 border-red-500' : $page.props.isImpersonating}"
    >
      <!-- NavBar -->
      <MainNavbarCustom />


      <!-- Page Content -->
      <main class="min-h-[80vh]">
        <HeaderBroadcastBar />
        <slot />
      </main>

      <MainFooter />
    </div>
  </div>

  <CookieConsent />
</template>
