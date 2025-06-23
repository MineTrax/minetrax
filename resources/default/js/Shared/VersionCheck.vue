<template>
  <div
    v-if="!loading && response && response.is_uptodate === false"
    ref="box"
  >
    <AlertCard
          text-color="text-error-800 dark:text-error-500"
    border-color="border-error-500"
    >
      {{ __("Your MineTrax Version is Outdated!") }}
      <template
        #body
      >
        <div class="text-foreground dark:text-foreground">
          {{ __("Your current MineTrax version is") }}&nbsp;<span class="text-error-500">{{ response.my_version }}</span>&nbsp;{{ __("while the latest version is") }}&nbsp;<span class="text-success-500">{{ response.latest_version }}</span>. {{ __("Please upgrade to enjoy latest features.") }} <br>
          <a
            class="text-primary hover:underline"
            target="_blank"
            href="https://minetrax.github.io/docs/upgrade"
          >{{ __("Click here to know more.") }}</a>

          <p class="text-xs text-foreground italic mt-2">
            {{ __("Note: This box is only visible to Staff Member") }}
          </p>
        </div>
      </template>
    </AlertCard>
  </div>
</template>

<script>
import AlertCard from '@/Components/AlertCard.vue';

export default {
    components: {
        AlertCard
    },
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
