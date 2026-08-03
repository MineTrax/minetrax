<template>
  <div
    v-if="siteKey"
    ref="containerRef"
  />
  <div
    v-else
    class="text-sm text-destructive"
  >
    {{ __("Turnstile site key is not configured.") }}
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    siteKey: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["verify", "expire", "error", "reset"]);

const containerRef = ref(null);
const widgetId = ref(null);

const scriptId = "turnstile-script";

onMounted(() => {
    if (typeof window === "undefined") {
        return;
    }

    if (window.turnstile) {
        renderWidget();
    } else if (document.getElementById(scriptId)) {
        waitForTurnstile();
    } else {
        loadScript();
    }
});

onUnmounted(() => {
    removeWidget();
});

function loadScript() {
    const script = document.createElement("script");
    script.id = scriptId;
    script.src = "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
    script.async = true;
    script.defer = true;
    script.onload = () => renderWidget();

    document.head.appendChild(script);
}

function waitForTurnstile() {
    if (window.turnstile) {
        renderWidget();

        return;
    }

    setTimeout(waitForTurnstile, 50);
}

function renderWidget() {
    if (typeof window === "undefined" || !window.turnstile || !containerRef.value || widgetId.value) {
        return;
    }

    widgetId.value = window.turnstile.render(containerRef.value, {
        sitekey: props.siteKey,
        callback: (token) => emit("verify", token),
        "expired-callback": () => emit("expire"),
        "error-callback": (error) => emit("error", error),
    });
}

function reset() {
    if (widgetId.value && typeof window !== "undefined" && window.turnstile) {
        window.turnstile.reset(widgetId.value);
        emit("reset");
    }
}

function removeWidget() {
    if (widgetId.value && typeof window !== "undefined" && window.turnstile) {
        try {
            window.turnstile.remove(widgetId.value);
        } catch (error) {
            // Widget may already be removed from the DOM by navigation.
        }
    }

    widgetId.value = null;
}

defineExpose({
    reset,
});
</script>
