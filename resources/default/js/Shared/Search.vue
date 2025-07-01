<template>
  <div ref="searchContainer" class="relative mx-auto text-foreground">
    <form @submit.prevent="performSearch">
      <input
        v-model="searchString"
        aria-label="search"
        class="border bg-background h-10 px-5 pr-10 w-48 rounded-full text-sm focus:outline-none focus:ring-0 transition-all duration-300 ease-in-out"
        :class="{'w-80': showResults || isFocused}"
        type="search"
        name="search"
        :placeholder="__('Search..')"
        autocomplete="off"
        @input="performSearch"
        @focus="isFocused = true"
        @blur="handleBlur"
      >
      <button
        type="submit"
        class="absolute right-0 top-0 mt-3 mr-4"
      >
        <MagnifyingGlassIcon
          class="text-foreground h-4 w-4 stroke-2"
        />
      </button>
    </form>

    <div
      v-if="showResults && searchString"
      id="results"
      class="absolute bg-popover px-3 py-1 w-full rounded-md shadow-lg z-50 border border-border"
    >
      <div
        v-if="loading"
        id="loading"
        class="text-center p-2 text-popover-foreground"
      >
        {{ __("Loading") }}...
      </div>
      <div
        v-if="!loading"
        id="users"
      >
        <span class="text-xs text-popover-foreground font-extrabold">{{ __("USERS") }}</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="user in usersList"
            id="user"
            :key="user.username"
            as="a"
            :href="route('user.public.get', user.username)"
            class="flex px-2 py-1 justify-between hover:bg-accent hover:text-accent-foreground rounded cursor-pointer"
          >
            <div class="flex">
              <img
                class="mr-3 w-10 h-10 rounded-full"
                :src="user.profile_photo_url"
                alt="Image"
              >
              <div class="text-sm">
                <p class="text-popover-foreground font-bold">
                  {{ user.title }}
                </p>
                <p class="text-popover-foreground">
                  @{{ user.username }}
                </p>
              </div>
            </div>

            <div class="flex">
              <img
                v-tippy
                :title="user.country.name"
                :src="user.country.photo_path"
                alt=""
                class="h-8 w-8 -mt-0.5 focus:outline-none"
              >
            </div>
          </inertia-link>
        </div>

        <div
          v-if="!usersList || usersList.length <= 0"
          id="emptyusers"
          class="italic text-muted-foreground"
        >
          {{ __("No users found.") }}
        </div>
      </div>
      <div
        v-if="!loading"
        id="players"
        class="mt-5 pb-4"
      >
        <span class="text-xs text-popover-foreground font-extrabold">{{ __("PLAYERS") }}</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="player in playersList"
            id="player"
            :key="player.uuid"
            as="a"
            :href="route('player.show', player.uuid)"
            class="flex justify-between px-2 py-1 hover:bg-accent hover:text-accent-foreground rounded cursor-pointer"
          >
            <div class="flex items-center">
              <img
                class="mr-3 w-8 h-8"
                :src="player.avatar_url"
                alt="Avatar"
              >
              <div class="text-sm">
                <p class="text-popover-foreground font-bold">
                  {{ player.title }}
                </p>
              </div>
            </div>

            <div class="flex space-x-2">
              <Icon
                v-show="player.rating != null"
                v-tippy
                class="w-8 h-8 focus:outline-none"
                :name="`rating-${player.rating}`"
                :content="player.rating"
              />
              <img
                v-show="player.rank.photo_path"
                v-tippy
                :src="player.rank.photo_path"
                :alt="player.rank.name"
                :title="player.rank.name"
                class="h-8 w-8 focus:outline-none"
              >
              <img
                v-tippy
                :title="player.country.name"
                :src="player.country.photo_path"
                alt=""
                class="h-8 w-8 -mt-0.5 focus:outline-none"
              >
            </div>
          </inertia-link>
        </div>

        <div
          v-if="!playersList || playersList.length <= 0"
          id="emptyplayers"
          class="italic text-muted-foreground"
        >
          {{ __("No players found.") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Icon from '@/Components/Icon.vue';
import { debounce } from 'lodash/function';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

// Template ref
const searchContainer = ref(null);

// Reactive data
const showResults = ref(false);
const loading = ref(false);
const searchString = ref('');
const usersList = ref([]);
const playersList = ref([]);
const isFocused = ref(false);

// Debounced search function
const performSearch = debounce(() => {
    if (!searchString.value) {
        return;
    }

    showResults.value = true;
    loading.value = true;

    axios.get(route('search', { q: searchString.value }))
        .then(data => {
            usersList.value = data.data.users;
            playersList.value = data.data.players;
        })
        .finally(() => {
            loading.value = false;
        });
}, 200);

// Handle blur event
const handleBlur = () => {
    // Use setTimeout to allow click events on results to fire first
    setTimeout(() => {
        isFocused.value = false;
        if (!searchString.value) {
            showResults.value = false;
        }
    }, 150);
};

// Click outside handler
const handleClickOutside = (e) => {
    // Close dropdown when clicked outside
    if (searchContainer.value && !searchContainer.value.contains(e.target)) {
        showResults.value = false;
        searchString.value = '';
        isFocused.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>

</style>
