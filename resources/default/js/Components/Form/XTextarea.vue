<template>
  <div class="floating-input relative w-full">
    <textarea
      :id="id"
      ref="input"
      :name="name"
      class="border hide-scrollbar resize-none focus:outline-none rounded-md w-full p-3 min-h-16 h-20 disabled:opacity-50 focus:border-light-blue-300 focus:ring text-sm focus:ring-light-blue-200 focus:ring-opacity-50 dark:bg-cool-gray-900 dark:border-cool-gray-900 dark:text-gray-300"
      :class="borderColor"
      :placeholder="label"
      :autocomplete="autocomplete"
      :value="modelValue"
      :autofocus="autofocus"
      :required="required"
      :disabled="disabled"
      @input="handleInputEvent"
      @focus="hasFocus = true"
      @blur="hasFocus = false"
    />
    <label
      :for="id"
      :class="textColor"
      class="absolute top-0 left-0 px-3 pt-5 text-sm pointer-events-none transform origin-left transition-all duration-100 ease-in-out dark:text-gray-400"
    >{{
      label
    }}
    </label>

    <div
      class="info -mt-0.5 flex"
      :class="help ? 'justify-between ' + helpErrorFlex : 'justify-end ' + helpErrorFlex "
    >
      <p
        v-show="help"
        class="text-xs text-gray-500"
      >
        {{ help }}
      </p>
      <p
        v-show="error"
        class="text-xs text-red-500"
      >
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
    props: {
        modelValue: [Number, String, Array, Object, Boolean, Date],
        name: String,
        help: String,
        label: String,
        type: String,
        id: String,
        error: String,
        autocomplete: {
            type: String,
            default: 'off'
        },
        autofocus: {
            type: [String, Boolean],
            default: false
        },
        required: {
            type: [String, Boolean],
            default: false
        },
        disabled: {
            type: [String, Boolean],
            default: false
        },
        helpErrorFlex: {
            type: String,
            default: 'flex-col'
        },
        autoResize: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            hasFocus: false
        };
    },

    computed: {
        borderColor() {
            if(this.error) {
                return 'border-red-400';
            } else {
                return 'border-gray-300';
            }
        },
        textColor() {
            if (this.hasFocus) {
                return 'text-light-blue-400';
            }

            if(this.error) {
                return 'text-red-400';
            } else {
                return 'text-gray-500';
            }
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },
        handleInputEvent($event) {
            this.$emit('update:modelValue', $event.target.value);
            if (this.autoResize) {
                const textarea = this.$refs['input'];
                textarea.style.height = 'initial';
                textarea.style.height = `${textarea.scrollHeight}px`;
            }
        },
    }
};
</script>
