<template>
    <div class="flex items-start">
        <div class="relative">
            <input :id="id" ref="input" v-model="proxyChecked" :disabled="disabled" type="checkbox" :name="name" class="sr-only" :required="required" @input="$emit('input', $event.target.value)" />

            <button
                type="button"
                :class="[
                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
                    proxyChecked ? 'bg-primary' : 'bg-gray-200 dark:bg-gray-700',
                ]"
                :disabled="disabled"
                @click="toggleSwitch"
            >
                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out', proxyChecked ? 'translate-x-6' : 'translate-x-1']" />
            </button>
        </div>

        <label :for="id" class="font-medium text-sm flex flex-col text-foreground dark:text-foreground ml-3 leading-tight cursor-pointer" @click="toggleSwitch">
            <span v-if="label">{{ label }}</span>
            <span v-if="help" class="text-xs text-foreground opacity-75">{{ help }}</span>
            <span v-if="error" class="text-xs text-error-500">{{ error }}</span>
        </label>
    </div>
</template>

<script>
export default {
    model: {
        prop: "checked",
        event: "change",
    },
    props: {
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
    },

    computed: {
        proxyChecked: {
            get() {
                return this.modelValue;
            },
            set(val) {
                this.$emit("update:modelValue", val);
            },
        },
    },

    methods: {
        toggleSwitch() {
            if (!this.disabled) {
                this.proxyChecked = !this.proxyChecked;
            }
        },
    },
};
</script>

<style scoped>
/* Additional styles for better visual feedback */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
