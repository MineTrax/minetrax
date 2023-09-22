<template>
  <div class="flex items-start">
    <input
      :id="id"
      ref="input"
      v-model="proxyChecked"
      :disabled="disabled"
      type="checkbox"
      :name="name"
      class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
      :class="checkboxSizeClass"
      :required="required"
      @input="$emit('input', $event.target.value)"
    >

    <label
      :for="id"
      class="font-medium text-sm flex flex-col text-gray-600 dark:text-gray-400 ml-2 leading-tight"
    >
      <span v-if="label">{{ label }}</span>
      <span
        v-if="help"
        class="text-xs text-gray-400"
      >{{ help }}</span>
      <span
        v-if="error"
        class="text-xs text-red-500"
      >{{ error }}</span>
    </label>
  </div>
</template>

<script>
export default {
    model: {
        prop: 'checked',
        event: 'change',
    },
    props: {
        modelValue: {
            type: [Array, Boolean, Number],
            default: false
        },
        name: String,
        help: String,
        label: String,
        type: String,
        id: String,
        error: String,
        required: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
    },

    computed: {
        proxyChecked: {
            get() {
                return this.modelValue;
            },
            set(val) {
                this.$emit('update:modelValue', val);
            },
        },

        checkboxSizeClass() {
            if (this.help) {
                return 'p-3';
            }
            return 'p-2';
        }
    },
};
</script>
