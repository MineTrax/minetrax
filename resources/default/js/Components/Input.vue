<template>
  <input
    ref="input"
    class="mt-1 block w-full border-secondary-300 disabled:opacity-50 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-md shadow-sm"
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

