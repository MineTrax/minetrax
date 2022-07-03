<template>
  <div
    v-if="!loading && response && response.is_uptodate === false"
    ref="box"
  >
    <div
      class="mb-4 bg-white dark:bg-cool-gray-800 border-t-4 border-red-500 rounded-b text-red-900 dark:text-red-500 px-4 py-3 shadow"
      role="alert"
    >
      <div class="flex">
        <div class="py-1">
          <svg
            class="fill-current h-6 w-6 text-red-500 mr-4"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
          >
            <path
              d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"
            />
          </svg>
        </div>
        <div>
          <p class="font-bold">
            Your MineTrax Version is Outdated!
          </p>
          <p class="text-sm text-gray-700 dark:text-gray-200">
            Your current MineTrax version is <span class="text-red-500">{{ response.my_version }}</span> while the latest version is <span class="text-green-500">{{ response.latest_version }}</span>. Please upgrade to enjoy latest features. <br>
            <a
              class="text-light-blue-500 hover:underline"
              target="_blank"
              href="https://minetrax.github.io/docs/upgrade"
            >Click here to know more.</a>
          </p>

          <p class="text-xs text-gray-500 italic mt-2">
            Note: This box is only visible to Staff Member
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
    data() {
        return {
            response: {},
            loading: true,
        };
    },

    created() {
        axios.get(route('version.check')).then(data => {
            this.response = data.data;
        }).finally(() => {
            this.loading = false;
        });
    },
};
</script>
