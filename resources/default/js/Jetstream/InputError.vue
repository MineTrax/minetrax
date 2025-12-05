<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-xs text-destructive">
            {{ message }}
        </p>
    </div>
</template>
