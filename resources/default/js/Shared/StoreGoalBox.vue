<template>
  <Card v-if="goal">
    <CardContent class="p-3 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Store Goal") }}
        </h3>
        <span
          v-if="goal.is_reached"
          class="inline-flex items-center rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-semibold text-success"
        >
          {{ __("Reached") }}
        </span>
        <span
          v-else
          class="text-xs font-semibold text-muted-foreground"
        >{{ goal.percent }}%</span>
      </div>

      <p class="text-xs text-muted-foreground">
        {{ goal.month }}
      </p>

      <!-- The bar itself. Width comes from the server's capped percentage, so beating the goal
           fills the bar rather than overflowing it. -->
      <div
        class="h-2.5 w-full overflow-hidden rounded-full bg-muted"
        role="progressbar"
        :aria-valuenow="goal.percent"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-label="__('Store Goal')"
      >
        <div
          class="h-full rounded-full transition-all duration-500"
          :class="goal.is_reached ? 'bg-success' : 'bg-primary'"
          :style="{ width: `${goal.percent}%` }"
        />
      </div>

      <div class="flex items-baseline justify-between">
        <span class="text-sm font-semibold text-card-foreground">{{ goal.raised_formatted }}</span>
        <span class="text-xs text-muted-foreground">{{ __("of :amount", { amount: goal.target_formatted }) }}</span>
      </div>

      <Button
        v-if="$page.props.store?.enabled"
        class="w-full font-bold"
        size="sm"
        as-child
      >
        <Link :href="route('store.index')">
          {{ __("Visit Store") }}
        </Link>
      </Button>
    </CardContent>
  </Card>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";

defineProps({
    // Null when the goal is switched off or no target is set, which is what hides the box.
    goal: {
        type: Object,
        default: null,
    },
});

const { __ } = useTranslations();
</script>
