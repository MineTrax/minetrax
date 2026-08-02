<template>
  <div
    ref="searchContainer"
    class="relative mx-auto text-foreground"
  >
    <form @submit.prevent="performSearch">
      <input
        ref="searchInput"
        v-model="searchString"
        aria-label="search"
        class="border bg-background h-10 px-5 pr-10 w-48 rounded-full text-sm focus:outline-hidden focus:ring-0 transition-all duration-300 ease-in-out"
        :class="{'w-80': showResults || isFocused}"
        type="search"
        name="search"
        :placeholder="__('Search..')"
        autocomplete="off"
        :tabindex="disableAutofocus && !isUserInteracted ? -1 : 0"
        @input="performSearch"
        @focus="handleFocus"
        @blur="handleBlur"
        @click="handleClick"
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
                class="h-8 w-8 -mt-0.5 focus:outline-hidden"
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
                class="w-8 h-8 focus:outline-hidden"
                :name="`rating-${player.rating}`"
                :content="player.rating"
              />
              <img
                v-show="player.rank.photo_path"
                v-tippy
                :src="player.rank.photo_path"
                :alt="player.rank.name"
                :title="player.rank.name"
                class="h-8 w-8 focus:outline-hidden"
              >
              <img
                v-tippy
                :title="player.country.name"
                :src="player.country.photo_path"
                alt=""
                class="h-8 w-8 -mt-0.5 focus:outline-hidden"
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

      <!-- Last, and only when the store module is on: a community site's search is for people
           first, and a permanently empty "Shop" heading on a site with no store is noise. -->
      <div
        v-if="!loading && storeEnabled"
        id="shop"
        class="mt-5 pb-4"
      >
        <span class="text-xs text-popover-foreground font-extrabold">{{ __("SHOP") }}</span>

        <div class="flex flex-col">
          <inertia-link
            v-for="item in shopList"
            id="shopitem"
            :key="item.slug"
            as="a"
            :href="route('store.package', item.slug)"
            class="flex justify-between items-center gap-3 px-2 py-1 hover:bg-accent hover:text-accent-foreground rounded cursor-pointer"
          >
            <div class="flex items-center min-w-0">
              <img
                v-if="item.photo_url"
                class="mr-3 w-10 h-10 rounded object-cover shrink-0"
                :src="item.photo_url"
                :alt="item.title"
              >
              <div
                v-else
                class="mr-3 w-10 h-10 rounded bg-muted flex items-center justify-center shrink-0"
              >
                <ShoppingBagIcon class="w-5 h-5 text-muted-foreground" />
              </div>
              <p class="text-sm text-popover-foreground font-bold truncate">
                {{ item.title }}
              </p>
            </div>

            <span
              v-if="item.price_formatted"
              class="text-sm font-semibold text-popover-foreground whitespace-nowrap"
            >
              {{ item.price_formatted }}
            </span>
          </inertia-link>
        </div>

        <div
          v-if="!shopList || shopList.length <= 0"
          id="emptyshop"
          class="italic text-muted-foreground"
        >
          {{ __("No packages found.") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Icon from "@/Components/Icon.vue";
import { MagnifyingGlassIcon, ShoppingBagIcon } from "@heroicons/vue/24/outline";
import { debounce } from "lodash/function";
import { usePage } from "@inertiajs/vue3";
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";

// Props
const props = defineProps({
    disableAutofocus: {
        type: Boolean,
        default: false
    }
});

// Template refs
const searchContainer = ref(null);
const searchInput = ref(null);

// Reactive data
const showResults = ref(false);
const loading = ref(false);
const searchString = ref("");
const usersList = ref([]);
const playersList = ref([]);
const shopList = ref([]);
const isFocused = ref(false);

// The endpoint omits the shop aspect entirely when the module is off, so the section is hidden
// rather than left showing a permanent "No packages found."
const storeEnabled = computed(() => !!usePage().props.store?.enabled);

// Debounced search function
const performSearch = debounce(() => {
    if (!searchString.value) {
        return;
    }

    showResults.value = true;
    loading.value = true;

    axios.get(route("search", { q: searchString.value }))
        .then(data => {
            usersList.value = data.data.users;
            playersList.value = data.data.players;
            shopList.value = data.data.shop;
        })
        .finally(() => {
            loading.value = false;
        });
}, 200);

// Track user interaction to allow manual focus
const isUserInteracted = ref(false);

// Handle click event - restore tabindex to allow focus
const handleClick = () => {
    if (props.disableAutofocus && !isUserInteracted.value) {
        isUserInteracted.value = true;
        // Small delay to ensure tabindex is updated before focus
        nextTick(() => {
            if (searchInput.value) {
                searchInput.value.focus();
            }
        });
    }
};

// Handle focus event
const handleFocus = () => {
    isFocused.value = true;
};

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
        searchString.value = "";
        isFocused.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    window.addEventListener("click", handleClickOutside);

    // If autofocus is disabled, blur any automatic focus
    if (props.disableAutofocus && searchInput.value) {
        // Check and blur at various intervals to catch autofocus
        const checkAndBlur = () => {
            if (searchInput.value && document.activeElement === searchInput.value && !isUserInteracted.value) {
                searchInput.value.blur();
            }
        };

        nextTick(checkAndBlur);
        setTimeout(checkAndBlur, 50);
        setTimeout(checkAndBlur, 100);
        setTimeout(checkAndBlur, 200);
    }
});

onUnmounted(() => {
    window.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>

</style>
