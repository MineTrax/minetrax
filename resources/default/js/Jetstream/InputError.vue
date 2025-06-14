<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-xs text-error-500">
            {{ message }}
        </p>
    </div>
</template>
