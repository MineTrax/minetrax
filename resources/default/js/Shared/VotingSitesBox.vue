<template>
  <Card v-if="enabled && votingsites && votingsites.length > 0">
    <CardContent class="p-4 sm:px-5 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Vote for Server") }}
        </h3>
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-500">
          <!-- star icon -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            class="h-3 w-3"
          >
            <path
              fill-rule="evenodd"
              d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"
              clip-rule="evenodd"
            />
          </svg>
          {{ __("Rewards") }}
        </span>
      </div>

      <p class="text-sm text-muted-foreground">
        {{ __("Vote on these sites and win rewards!") }}
      </p>

      <div class="divide-y divide-border rounded-md border">
        <a
          v-for="(votingsite, index) in votingsites"
          :key="index"
          v-tippy
          :content="votingsite.url"
          :href="votingsite.url"
          target="_blank"
          class="group flex items-center justify-between gap-2 px-3 py-2.5 transition-colors first:rounded-t-md last:rounded-b-md hover:bg-muted/50"
        >
          <span class="text-sm font-semibold text-card-foreground truncate">
            {{ votingsite.name }}
          </span>
          <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
            {{ __("Vote") }}
            <!-- external link icon -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 16 16"
              fill="currentColor"
              class="h-3 w-3"
            >
              <path
                fill-rule="evenodd"
                d="M4.22 11.78a.75.75 0 0 1 0-1.06L9.44 5.5H5.75a.75.75 0 0 1 0-1.5h5.5a.75.75 0 0 1 .75.75v5.5a.75.75 0 0 1-1.5 0V6.56l-5.22 5.22a.75.75 0 0 1-1.06 0Z"
                clip-rule="evenodd"
              />
            </svg>
          </span>
        </a>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import {
    Card,
    CardContent,
} from "@/Components/ui/card";
defineProps({
    votingsites: Array,
    enabled: Boolean,
});
</script>
