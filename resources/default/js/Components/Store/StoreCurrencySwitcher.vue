<script setup>
import { router } from "@inertiajs/vue3";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Button } from "@/Components/ui/button";
import { ChevronDownIcon } from "@heroicons/vue/24/outline";

defineProps({
    currencies: Array,
    current: String,
});

function switchCurrency(code) {
    router.post(route("store.currency.switch"), {
        code: code,
    }, {
        preserveScroll: true,
    });
}
</script>

<template>
  <div v-if="currencies && currencies.length >= 2">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          variant="outline"
          size="sm"
        >
          <span>{{ current }}</span>
          <ChevronDownIcon class="ml-2 h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem
          v-for="curr in currencies"
          :key="curr.code"
          as-child
        >
          <button
            class="w-full text-left cursor-pointer"
            :class="{ 'opacity-50': curr.code === current }"
            :disabled="curr.code === current"
            @click="switchCurrency(curr.code)"
          >
            <span class="font-medium">{{ curr.code }}</span>
            <span class="text-muted-foreground ml-2">{{ curr.symbol }} - {{ curr.name }}</span>
          </button>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
