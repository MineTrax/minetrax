<template>
  <Card v-if="donor">
    <CardContent class="p-3 space-y-2">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Top Supporter") }}
        </h3>
        <TrophyIcon class="w-4 h-4 text-amber-500" />
      </div>

      <p class="text-xs text-muted-foreground">
        {{ donor.month }}
      </p>

      <div class="flex items-center gap-3 border border-border rounded-lg p-3">
        <!-- The supporter's own face: an account photo, or their Minecraft head if they bought as
             a guest. The trophy stands in only when the widget is anonymised. -->
        <img
          v-if="donor.avatar_url"
          class="w-10 h-10 rounded-full shrink-0 ring-2 ring-amber-500/40 object-cover"
          :src="donor.avatar_url"
          :alt="donor.name"
        >
        <div
          v-else
          class="w-10 h-10 rounded-full shrink-0 bg-amber-500/10 flex items-center justify-center"
        >
          <TrophyIcon class="w-5 h-5 text-amber-500" />
        </div>
        <div class="flex flex-col min-w-0 flex-1">
          <Link
            v-if="donor.username"
            :href="route('user.public.get', donor.username)"
            class="text-sm font-semibold text-card-foreground truncate hover:underline"
          >
            {{ donor.name }}
          </Link>
          <span
            v-else
            class="text-sm font-semibold text-card-foreground truncate"
          >{{ donor.name }}</span>
          <span class="text-xs text-muted-foreground">{{ donor.spent_formatted }}</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { TrophyIcon } from "@heroicons/vue/24/outline";
import { useTranslations } from "@/Composables/useTranslations";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";

defineProps({
    // Null when the widget is off or nobody has bought anything this month.
    donor: {
        type: Object,
        default: null,
    },
});

const { __ } = useTranslations();
</script>
