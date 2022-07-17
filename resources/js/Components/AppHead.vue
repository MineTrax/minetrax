<template>
  <inertia-head :title="title ? `${title} - ${$page.props.appName}` : `${$page.props.appName} - ${__('Minecraft Servers & Players Tracking')}`">
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
