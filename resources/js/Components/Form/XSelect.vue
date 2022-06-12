<template>
  <div class="floating-input relative w-full">
    <select
      :id="id"
      ref="input"
      name="gender"
      class="border-gray-300 pt-6 text-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md block w-full p-3 h-14 dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900"
      :class="borderColor"
      :value="value"
      :autofocus="autofocus"
      :required="required"
      :disabled="disabled"
      :name="name"
      @change="$emit('input', $event.target.value)"
      @focus="hasFocus = true"
      @blur="hasFocus = false"
    >
      <option
        v-if="placeholder"
        class="text-gray-500 dark:text-gray-400"
        :value="null"
        :disabled="disableNull"
      >
        {{ placeholder }}
      </option>
      <option
        v-for="(label, value) in computedList"
        :key="value"
        :value="value"
      >
        {{ label }}
      </option>
    </select>

    <label
      :for="id"
      :class="textColor"
      class="absolute -top-3 left-0 px-3 py-5 text-xs h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out dark:text-gray-400"
    >{{
      label
    }}</label>

    <div
      class="info mt-1 flex"
      :class="help ? 'justify-between ' + helpErrorFlex : 'justify-end ' + helpErrorFlex "
    >
      <p
        v-show="help"
        class="text-xs text-gray-500 dark:text-gray-400"
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
        selectList: [Object, Array],
        value: [Number, String, Array, Object, Boolean, Date],
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
        borderColor() {
            if (this.error) {
                return 'border-red-400 dark:border-red-400';
            } else {
                return 'border-gray-300';
            }
        },
        textColor() {
            if (this.hasFocus) {
                return 'text-light-blue-400';
            }

            if (this.error) {
                return 'text-red-400';
            } else {
                return 'text-gray-500';
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
