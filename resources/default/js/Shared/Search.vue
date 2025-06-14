<template>
  <div class="relative mx-auto text-secondary-600 dark:text-secondary-400">
    <form @submit.prevent="performSearch">
      <input
        v-model="searchString"
        aria-label="search"
        class="border-none bg-surface-200 dark:bg-surface-900 h-10 px-5 pr-10 w-48 rounded-full text-sm focus:outline-none focus:ring-0 transition-all duration-300 ease-in-out"
        :class="{'w-80': showResults || isFocused}"
        type="search"
        name="search"
        :placeholder="__('Search')+'..'"
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
          class="text-secondary-400 dark:text-secondary-600 h-4 w-4 stroke-2"
        />
      </button>
    </form>

    <div
      v-if="showResults && searchString"
      id="results"
      class="absolute bg-white dark:bg-surface-800 px-3 py-1 w-full rounded-md shadow-lg z-50"
    >
      <div
        v-if="loading"
        id="loading"
        class="text-center p-2"
      >
        {{ __("Loading") }}...
      </div>
      <div
        v-if="!loading"
        id="users"
      >
        <span class="text-xs text-secondary-400 dark:text-secondary-300 font-extrabold">{{ __("USERS") }}</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="user in usersList"
            id="user"
            :key="user.username"
            as="a"
            :href="route('user.public.get', user.username)"
            class="flex px-2 py-1 justify-between hover:bg-primary-100 dark:hover:bg-surface-900 rounded cursor-pointer"
          >
            <div class="flex">
              <img
                class="mr-3 w-10 h-10 rounded-full"
                :src="user.profile_photo_url"
                alt="Image"
              >
              <div class="text-sm">
                <p class="text-secondary-700 dark:text-secondary-300 font-bold">
                  {{ user.title }}
                </p>
                <p class="text-secondary-500 dark:text-secondary-500">
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
          class="italic"
        >
          {{ __("No users found.") }}
        </div>
      </div>
      <div
        v-if="!loading"
        id="players"
        class="mt-5 pb-4"
      >
        <span class="text-xs text-secondary-400 dark:text-secondary-300 font-extrabold">{{ __("PLAYERS") }}</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="player in playersList"
            id="player"
            :key="player.uuid"
            as="a"
            :href="route('player.show', player.uuid)"
            class="flex justify-between px-2 py-1 hover:bg-primary-100 dark:hover:bg-surface-900 rounded cursor-pointer"
          >
            <div class="flex items-center">
              <img
                class="mr-3 w-8 h-8"
                :src="player.avatar_url"
                alt="Avatar"
              >
              <div class="text-sm">
                <p class="text-secondary-700 dark:text-secondary-300 font-bold">
                  {{ player.title }}
                </p>
              </div>
            </div>

            <div class="flex space-x-2">
              <icon
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
          class="italic"
        >
          {{ __("No players found.") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import {debounce} from 'lodash/function';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'Search',
    components: {Icon, MagnifyingGlassIcon},
    data() {
        return {
            showResults: false,
            loading: false,
            searchString: '',
            usersList: [],
            playersList: [],
            isFocused: false
        };
    },
    // This hide the dropdown if clicked outside of the component
    created: function() {
        window.addEventListener('click', (e) => {
            // close dropdown when clicked outside
            if (!this.$el.contains(e.target)){
                this.showResults = false;
                this.searchString = '';
                this.isFocused = false;
            }
        });
    },
    methods: {
        performSearch: debounce(function() {
            if (!this.searchString) {
                return;
            }

            this.showResults = true;
            this.loading = true;
            axios.get(route('search', {q: this.searchString})).then(data => {
                this.usersList = data.data.users;
                this.playersList = data.data.players;
            }).finally(() => {
                this.loading = false;
            });
        }, 200),
        handleBlur() {
            // Use setTimeout to allow click events on results to fire first
            setTimeout(() => {
                this.isFocused = false;
                if (!this.searchString) {
                    this.showResults = false;
                }
            }, 150);
        }
    },
};
</script>

<style scoped>

</style>
