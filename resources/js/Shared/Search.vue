<template>
  <div class="relative mx-auto text-gray-600 dark:text-gray-400">
    <form @submit.prevent="performSearch">
      <input
        v-model="searchString"
        aria-label="search"
        class="border-none bg-gray-200 dark:bg-cool-gray-900 h-10 px-5 pr-10 focus:w-80 rounded-full text-sm focus:outline-none focus:ring-0"
        :class="{'w-80': showResults}"
        type="search"
        name="search"
        placeholder="Search.."
        autocomplete="off"
        @input="performSearch"
      >
      <button
        type="submit"
        class="absolute right-0 top-0 mt-3 mr-4"
      >
        <svg
          id="Capa_1"
          class="text-gray-400 dark:text-gray-600 h-4 w-4 fill-current"
          xmlns="http://www.w3.org/2000/svg"
          xmlns:xlink="http://www.w3.org/1999/xlink"
          version="1.1"
          x="0px"
          y="0px"
          viewBox="0 0 56.966 56.966"
          style="enable-background:new 0 0 56.966 56.966;"
          xml:space="preserve"
        >
          <path
            d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23  s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92  c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17  s-17-7.626-17-17S14.61,6,23.984,6z"
          />
        </svg>
      </button>
    </form>

    <div
      v-if="showResults && searchString"
      id="results"
      class="absolute bg-white dark:bg-cool-gray-800 px-3 py-1 w-full rounded-md shadow-lg z-50"
    >
      <div
        v-if="loading"
        id="loading"
        class="text-center p-2"
      >
        Loading...
      </div>
      <div
        v-if="!loading"
        id="users"
      >
        <span class="text-xs text-gray-400 dark:text-gray-300 font-extrabold">USERS</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="user in usersList"
            id="user"
            :key="user.username"
            as="div"
            :href="route('user.public.get', user.username)"
            class="flex px-2 py-1 justify-between hover:bg-light-blue-100 dark:hover:bg-cool-gray-900 rounded cursor-pointer"
          >
            <div class="flex">
              <img
                class="mr-3 w-10 h-10 rounded-full"
                :src="user.profile_photo_url"
                alt="Image"
              >
              <div class="text-sm">
                <p class="text-gray-700 dark:text-gray-300 font-bold">
                  {{ user.title }}
                </p>
                <p class="text-gray-500 dark:text-gray-500">
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
          No users found.
        </div>
      </div>
      <div
        v-if="!loading"
        id="players"
        class="mt-5 pb-4"
      >
        <span class="text-xs text-gray-400 dark:text-gray-300 font-extrabold">PLAYERS</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="player in playersList"
            id="player"
            :key="player.uuid"
            as="div"
            :href="route('player.show', player.uuid)"
            class="flex justify-between px-2 py-1 hover:bg-light-blue-100 dark:hover:bg-cool-gray-900 rounded cursor-pointer"
          >
            <div class="flex items-center">
              <img
                class="mr-3 w-8 h-8"
                :src="player.avatar_url"
                alt="Avatar"
              >
              <div class="text-sm">
                <p class="text-gray-700 dark:text-gray-300 font-bold">
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
          No players found.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon';
import {debounce} from 'lodash/function';

export default {
    name: 'Search',
    components: {Icon},
    data() {
        return {
            showResults: false,
            loading: false,
            searchString: '',
            usersList: [],
            playersList: []
        };
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
            }).finally(x => {
                this.loading = false;
            });
        }, 200)
    },

    // This hide the dropdown if clicked outside of the component
    created: function() {
        window.addEventListener('click', (e) => {
            // close dropdown when clicked outside
            if (!this.$el.contains(e.target)){
                this.showResults = false;
                this.searchString = '';
            }
        });
    }
};
</script>

<style scoped>

</style>
