<template>
  <Card v-if="enabled && votingsites && votingsites.length > 0">
    <CardContent class="p-3 space-y-2">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Vote for Server") }}
      </h3>

      <p class="rounded text-sm text-card-foreground text-center p-1">
        {{ __("Vote on these sites and win rewards!") }}
      </p>

      <div class="flex flex-col space-y-2">
        <div
          v-for="(votingsite, index) in votingsites"
          :key="index"
          class="flex justify-between items-center"
        >
          <div
            v-tippy
            :content="votingsite.url"
            class="text-card-foreground text-sm font-bold focus:outline-none"
          >
            {{ votingsite.name }}
          </div>
          <div>
            <Button
              variant="outline"
              size="sm"
              as="a"
              target="_blank"
              :href="votingsite.url"
            >
              {{ __("Vote") }}
            </Button>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import {
  Card,
  CardContent,
} from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'

defineProps({
  votingsites: Array,
  enabled: Boolean,
});
</script>
