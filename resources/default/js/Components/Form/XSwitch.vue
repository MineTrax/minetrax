<template>
    <div class="flex items-start">
        <Switch :id="id" :model-value="proxyChecked" @update:model-value="proxyChecked = $event" :disabled="disabled" :name="name" :required="required" />

        <label :for="id" class="font-medium text-sm flex flex-col text-foreground dark:text-foreground ml-3 leading-tight cursor-pointer">
            <span v-if="label">{{ label }}</span>
            <span v-if="help" class="text-xs text-foreground opacity-75">{{ help }}</span>
            <span v-if="error" class="text-xs text-error-500">{{ error }}</span>
        </label>
    </div>
</template>

<script setup>
import Switch from "@/Components/ui/switch/Switch.vue";
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: [Array, Boolean, Number],
        default: false,
    },
    name: String,
    help: String,
    label: String,
    type: String,
    id: String,
    error: String,
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const proxyChecked = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit("update:modelValue", val);
    },
});
</script>
