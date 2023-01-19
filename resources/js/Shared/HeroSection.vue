<template>
  <div
    v-if="settings.enable_home_hero_section"
    :style="`
                background: url('${bgImageUrl}');
                background-size: ${settings.home_hero_bg_size_css};
                background-repeat: ${settings.home_hero_bg_repeat_css};
                background-position: ${settings.home_hero_bg_position_css};
                background-attachment: ${settings.home_hero_bg_attachment_css};
                height: ${settings.home_hero_bg_height_css};
              `"
  >
    <!-- Hero content: fully optional and configurable, even the CSS -->
  </div>
</template>

<script>
export default {
    props: {
        settings: Object,
    },
    computed: {
        bgImageUrl() {
            return window.colorMode === 'dark' ? this.settings.home_hero_bg_image_path_dark : this.settings.home_hero_bg_image_path_light;
        },
    },
};
</script>

<style scoped>

</style>
