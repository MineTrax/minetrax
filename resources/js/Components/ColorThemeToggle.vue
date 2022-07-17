<template>
  <div>
    <button @click="toggleTheme">
      <icon
        v-if="colorMode === 'dark'"
        v-tippy
        :title="__('Use Light Theme')"
        name="moon-full"
        class="w-6 h-6 text-gray-400 focus:outline-none"
      />
      <icon
        v-else
        v-tippy
        :title="__('Use Dark Theme')"
        name="moon-outline"
        class="w-6 h-6 text-gray-400 focus:outline-none"
      />
    </button>
  </div>
</template>

<script>
import Icon from '@/Components/Icon';

export default {
    name: 'ColorThemeToggle',
    components: {Icon},
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
