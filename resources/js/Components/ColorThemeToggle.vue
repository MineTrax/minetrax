<template>
  <div>
    <button @click="toggleTheme">
      <span
        v-if="colorMode === 'dark'"
        v-tippy
        :title="__('Use Light Theme')"
      >
        <MoonIcon
          class="w-5 h-5 text-gray-400 focus:outline-none stroke-2"
        />
      </span>
      <span
        v-else
        v-tippy
        :title="__('Use Dark Theme')"
      >
        <SunIcon
          class="w-6 h-6 text-gray-400 focus:outline-none stroke-2"
        />
      </span>
    </button>
  </div>
</template>

<script>
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'ColorThemeToggle',
    components: { MoonIcon, SunIcon },
    data() {
        return {
            colorMode: window.colorMode
        };
    },
    methods: {
        toggleTheme() {
            if (this.colorMode === 'dark') {
                this.colorMode = 'light';
                window.colorMode = 'light';
                localStorage.theme = 'light';
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
            } else {
                this.colorMode = 'dark';
                window.colorMode = 'dark';
                localStorage.theme = 'dark';
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            }
            window.location.reload();
        }
    }
};
</script>
