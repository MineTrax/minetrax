<template>
  <inertia-head :title="title ? `${title} - ${$page.props.appName}` : `${$page.props.appName} - Minecraft Servers & Players Tracking`">
    <slot />
  </inertia-head>
</template>

<script>
export default {
    props: {
        title: String,
    },
};
</script>
