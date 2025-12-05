<template>
  <div class="flex items-start">
    <input
      :id="id"
      ref="input"
      v-model="proxyChecked"
      :disabled="disabled"
      type="checkbox"
      :name="name"
      class="rounded border-foreground dark:border-foreground text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150 dark:bg-surface-900 dark:checked:bg-primary"
      :class="checkboxSizeClass"
      :required="required"
      @input="$emit('input', $event.target.value)"
    >

    <label
      :for="id"
      class="font-medium text-sm flex flex-col text-foreground dark:text-foreground ml-2 leading-tight"
    >
      <span v-if="label">{{ label }}</span>
      <span
        v-if="help"
        class="text-xs text-foreground"
      >{{ help }}</span>
      <span
        v-if="error"
        class="text-xs text-error-500"
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
