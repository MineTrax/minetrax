<template>
  <div class="flex -mx-1">
    <template
      v-for="(v,i) in 5"
      :key="i"
    >
      <div
        class="w-1/5 px-1"
      >
        <div
          class="h-2 rounded-xl transition-colors"
          :class="i<score?(score<=2?'bg-red-400':(score<=4?'bg-yellow-400':'bg-green-500')):'bg-gray-200 dark:bg-gray-900'"
        />
      </div>
    </template>
  </div>
</template>

<script>
import zxcvbn from 'zxcvbn';

export default {
    name: 'PasswordStrengthMeter',
    props: {
        value: {
            type: String,
            required: true
        }
    },

    computed: {
        score() {
            return this.value ? zxcvbn(this.value).score + 1 : null;
        },
    },
};
</script>
