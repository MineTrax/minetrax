<template>
  <Card v-if="enabled">
    <CardContent class="p-3 sm:px-5">
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
    </CardContent>
  </Card>
</template>

<script>
import {sample} from 'lodash';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

export default {
    components: {
        Card,
        CardContent,
    },
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
