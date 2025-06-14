<template>
  <div class="floating-input relative w-full">
    <select
      :id="id"
      ref="input"
      class="border-secondary-300 text-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-md block w-full p-3 dark:bg-surface-900 dark:text-secondary-300 dark:border-secondary-900"
      :class="[
        error ? 'border-error-400 dark:border-error-400' : 'border-secondary-300',
        label ? 'pt-6 h-14' : '',
        selectClass,
      ]"
      :value="modelValue"
      :autofocus="autofocus"
      :required="required"
      :disabled="disabled"
      :name="name"
      @change="$emit('update:modelValue', $event.target.value)"
      @focus="hasFocus = true"
      @blur="hasFocus = false"
    >
      <option
        v-if="placeholder"
        class="text-secondary-500 dark:text-secondary-400"
        value=""
        :disabled="disableNull"
      >
        {{ placeholder }}
      </option>
      <option
        v-for="(lbl, value) in computedList"
        :key="value"
        :value="value"
      >
        {{ lbl }}
      </option>
    </select>

    <label
      v-if="label"
      :for="id"
      :class="textColor"
      class="absolute -top-3 left-0 px-3 py-5 text-xs h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out dark:text-secondary-400"
    >{{
      label
    }}</label>

    <div
      class="info mt-1 flex"
      :class="help ? 'justify-between ' + helpErrorFlex : 'justify-end ' + helpErrorFlex "
    >
      <p
        v-show="help"
        class="text-xs text-secondary-500 dark:text-secondary-400"
      >
        {{ help }}
      </p>
      <p
        v-show="error"
        class="text-xs text-error-500"
      >
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
    props: {
        selectList: [Object, Array],
        modelValue: [Number, String, Array, Object, Boolean, Date],
        name: String,
        placeholder: String,
        help: String,
        label: String,
        id: String,
        error: String,
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
        disableNull: {
            type: Boolean,
            default: false
        },
        selectClass: {
            type: String,
            required: false,
        }
    },

    data() {
        return {
            hasFocus: false
        };
    },

    computed: {
        computedList() {
            if (!Array.isArray(this.selectList)) {
                return this.selectList;
            }

            return this.selectList.reduce((acc,value) => {
                return {[value]: value, ...acc};
            }, {});
        },
        textColor() {
            if (this.hasFocus) {
                return 'text-primary-400';
            }

            if (this.error) {
                return 'text-error-400';
            } else {
                return 'text-secondary-500';
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
