<template>
  <Card v-if="enabled && votingsites && votingsites.length > 0">
    <CardContent class="p-4 sm:px-5 space-y-3">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Vote for Server") }}
      </h3>

      <p class="text-sm text-muted-foreground">
        {{ __("Vote on these sites and win rewards!") }}
      </p>

      <div class="grid gap-2">
        <Button
          v-for="(votingsite, index) in votingsites"
          :key="index"
          v-tippy
          :content="votingsite.url"
          variant="outline"
          as="a"
          target="_blank"
          :href="votingsite.url"
          class="w-full justify-between font-semibold"
        >
          {{ votingsite.name }}
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            class="h-4 w-4 opacity-50"
          >
            <path
              fill-rule="evenodd"
              d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Zm7.25-.75a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V6.31l-5.47 5.47a.75.75 0 1 1-1.06-1.06l5.47-5.47H12.25a.75.75 0 0 1-.75-.75Z"
              clip-rule="evenodd"
            />
          </svg>
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import Button from "@/Components/ui/button/Button.vue";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";
defineProps({
    votingsites: Array,
    enabled: Boolean,
});
</script>
