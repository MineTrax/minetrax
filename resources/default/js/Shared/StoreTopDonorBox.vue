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
        <div class="w-10 h-10 rounded-full shrink-0 bg-amber-500/10 flex items-center justify-center">
          <TrophyIcon class="w-5 h-5 text-amber-500" />
        </div>
        <div class="flex flex-col min-w-0 flex-1">
          <span class="text-sm font-semibold text-card-foreground truncate">{{ donor.name }}</span>
          <span class="text-xs text-muted-foreground">{{ donor.spent_formatted }}</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
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
