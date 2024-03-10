<template>
  <div class="w-64 flex-shrink-0 pr-10">
    <div class="flex flex-col">
      <div class="uppercase mb-2 text-xs tracking-wide text-gray-600 dark:text-gray-400 font-bold">
        {{ __("SETTINGS") }}
      </div>

      <setting-link
        :href="route('admin.setting.general.show')"
        :active="route().current('admin.setting.general.show')"
      >
        {{ __("General") }}
      </setting-link>
      <setting-link
        :href="route('admin.setting.theme.show')"
        :active="route().current('admin.setting.theme.show')"
      >
        {{ __("Theme") }}
      </setting-link>
      <setting-link
        :href="route('admin.setting.plugin.show')"
        :active="route().current('admin.setting.plugin.show')"
      >
        {{ __("Plugin") }}
      </setting-link>
      <setting-link
        :href="route('admin.setting.player.show')"
        :active="route().current('admin.setting.player.show')"
      >
        {{ __("Player") }}
      </setting-link>
      <setting-link
        :href="route('admin.setting.navigation.show')"
        :active="route().current('admin.setting.navigation.show')"
      >
        {{ __("Navigation") }}
      </setting-link>
    </div>
  </div>
</template>

<script>
import SettingLink from '@/Jetstream/SettingLink.vue';

export default {
    name: 'SettingSidebar',
    components: {
        SettingLink
    }
};
</script>

<style scoped>

</style>
