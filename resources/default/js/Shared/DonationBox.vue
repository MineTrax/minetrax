<template>
  <div v-if="enabled">
    <div class="p-3 sm:px-5 bg-white dark:bg-surface-800 rounded shadow">
      <h3 class="font-extrabold text-foreground dark:text-foreground">
        {{ __("Donate") }}
      </h3>

      <p class="rounded text-sm text-foreground dark:text-foreground text-center p-1">
        {{ donationText }}
      </p>

      <div class="mt-3 text-primary flex justify-center">
        <a
          target="_blank"
          class="hover:bg-primary dark:hover:bg-surface-900 border px-2 py-1 border-primary font-semibold rounded"
          :href="$page.props.generalSettings.donation_box_url"
        >
          {{ __("Donate Now") }}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import {sample} from 'lodash';

export default {
    computed: {
        enabled() {
            return this.$page.props.generalSettings.enable_donation_box && this.$page.props.generalSettings.donation_box_url;
        },
        donationText() {
            return sample([
                this.__('Help us run our servers!'),
                this.__('Your help mean everything to us!'),
                this.__('If you are capable, we would appreciate your contribution')
            ]);
        }
    }
};
</script>
