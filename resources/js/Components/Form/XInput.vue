<template>
  <div
    class="floating-input relative w-full"
    :class="divClass"
  >
    <input
      :id="id"
      ref="input"
      :type="type"
      :name="name"
      class="dark:bg-cool-gray-900 dark:text-gray-300 border focus:outline-none rounded-md w-full p-3 h-14 disabled:opacity-50 focus:border-light-blue-300 focus:ring text-sm focus:ring-light-blue-200 focus:ring-opacity-50"
      :class="borderColor + ' ' + inputClass"
      :placeholder="label"
      :autocomplete="autocomplete"
      :value="modelValue"
      :autofocus="autofocus"
      :required="required"
      :disabled="disabled"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="hasFocus = true"
      @blur="hasFocus = false"
    >
    <label
      :for="id"
      :class="textColor"
      class="absolute top-0 left-0 px-3 py-5 text-sm h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out "
    >{{
      label
    }}</label>

    <div
      class="info mt-1 flex"
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
        inputClass: {
            type: String,
            default: ''
        },
        divClass: {
            type: String,
            default: ''
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
                return 'border-red-400 dark:border-red-600';
            } else {
                return 'border-gray-300 dark:border-gray-900';
            }
        },
        textColor() {
            if (this.hasFocus) {
                return 'text-light-blue-400';
            }

            if(this.error) {
                return 'text-red-400 dark:text-red-600';
            } else {
                return 'text-gray-500 dark:text-gray-400';
            }
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        }
    }
};
</script>
