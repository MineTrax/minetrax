<template>
  <div
    v-if="!loading && response && response.is_uptodate === false"
    ref="box"
  >
    <AlertCard variant="destructive">
      {{ __("Your MineTrax Version is Outdated!") }}
      <template #body>
        <div class="text-card-foreground">
          {{ __("Your current MineTrax version is") }}&nbsp;<span class="text-destructive font-semibold">{{ response.my_version }}</span>&nbsp;{{ __("while the latest version is") }}&nbsp;<span class="text-success-500 font-semibold">{{ response.latest_version }}</span>. {{ __("Please upgrade to enjoy latest features.") }}
          <br><br>
          <a
            class="text-primary hover:underline font-medium"
            target="_blank"
            href="https://minetrax.github.io/docs/upgrade"
          >{{ __("Click here to know more.") }}</a>

          <p class="text-xs text-muted-foreground italic mt-3">
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
