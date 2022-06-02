<template>
  <iframe
    v-if="enabled && server"
    :src="`https://discord.com/widget?id=${server}&theme=${colorMode}`"
    width="100%"
    height="500"
    allowtransparency="true"
    frameborder="0"
    sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
  />
</template>

<script>

export default {
    props: {
        enabled: Boolean,
        server: String,
    },
    data() {
        return {
            colorMode: window.colorMode
        };
    }
};
</script>
