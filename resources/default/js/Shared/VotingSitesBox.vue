<template>
  <div v-if="enabled && votingsites && votingsites.length > 0">
    <div class="p-3 bg-white dark:bg-surface-800 rounded shadow space-y-2">
      <h3 class="font-extrabold text-secondary-800 dark:text-secondary-200">
        {{ __("Vote for Server") }}
      </h3>

      <p class="rounded text-sm text-secondary-600 dark:text-secondary-400 text-center p-1">
        {{ __("Vote on these sites and win rewards!") }}
      </p>

      <div class="flex flex-col space-y-2">
        <div
          v-for="(votingsite, index) in votingsites"
          :key="index"
          class="rounded flex justify-between items-center p-2 bg-surface-100 dark:bg-surface-900"
        >
          <div
            v-tippy
            :content="votingsite.url"
            class="text-secondary-500 dark:text-secondary-300 text-sm font-bold focus:outline-none"
          >
            {{ votingsite.name }}
          </div>
          <div>
            <a
              target="_blank"
              class="rounded tracking-wider text-sm px-2 py-1 text-secondary-500 dark:text-secondary-300 bg-white dark:bg-surface-800 shadow hover:shadow-md"
              :href="votingsite.url"
            >{{ __("Vote") }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
    props: {
        votingsites: Array,
        enabled: Boolean
    },
};
</script>
