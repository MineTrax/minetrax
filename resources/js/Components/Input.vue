<template>
  <input
    ref="input"
    class="mt-1 block w-full border-gray-300 disabled:opacity-50 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
    :value="value"
    :placeholder="placeholder"
    @input="$emit('input', $event.target.value)"
  >
</template>

<script>
export default {
    props: {
        value: [Number, String, Array, Object, Boolean, Date],
        placeholder: String
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        }
    }
};
</script>

