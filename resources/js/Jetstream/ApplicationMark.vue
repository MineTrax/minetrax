<template>
  <img
    :src="logo"
    alt="Site Header Logo"
    class="logo"
  >
</template>

<script>
export default {
    computed: {
        logo() {
            if (window.colorMode === 'light')
                return this.$page.props.generalSettings.site_header_logo_path_light;
            else
                return this.$page.props.generalSettings.site_header_logo_path_dark;
        }
    }
};
</script>
