<template>
  <Card v-if="purchases && purchases.length">
    <CardContent class="p-3 space-y-2">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Recent Purchases") }}
      </h3>

      <div class="space-y-2">
        <div
          v-for="purchase in purchases"
          :key="purchase.id"
          class="flex items-center gap-3 border border-border rounded-lg p-3"
        >
          <!-- Only a named buyer gets an avatar: a picture identifies somebody as surely as a
               username does, so an anonymised list must not carry one. -->
          <img
            v-if="purchase.buyer_user"
            class="w-9 h-9 rounded-full shrink-0 ring-2 ring-border"
            :src="purchase.buyer_user.profile_photo_url"
            alt="Avatar"
          >
          <div
            v-else
            class="w-9 h-9 rounded-full shrink-0 bg-muted flex items-center justify-center"
          >
            <ShoppingBagIcon class="w-4 h-4 text-muted-foreground" />
          </div>

          <div class="flex flex-col min-w-0 flex-1">
            <div class="text-sm text-card-foreground truncate">
              <Link
                v-if="purchase.buyer_user"
                :href="route('user.public.get', purchase.buyer_user.username)"
                class="font-semibold hover:underline"
              >
                {{ purchase.buyer }}
              </Link>
              <span
                v-else
                class="font-semibold"
              >{{ purchase.buyer }}</span>
            </div>
            <span class="text-xs text-muted-foreground truncate">
              {{ itemsLabel(purchase) }}
            </span>
            <span
              v-tippy
              class="text-xs text-muted-foreground focus:outline-hidden"
              :title="formatToDayDateString(purchase.purchased_at)"
            >{{ formatTimeAgoToNow(purchase.purchased_at) }}</span>
          </div>

          <span class="text-xs font-semibold text-card-foreground shrink-0">
            {{ purchase.total_formatted }}
          </span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { ShoppingBagIcon } from "@heroicons/vue/24/outline";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";

defineProps({
    // Null when the widget is switched off, which is what hides the box.
    purchases: {
        type: Array,
        default: null,
    },
});

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

// One line whatever the basket held: naming every package in a five-item order would push the
// column out of shape.
function itemsLabel(purchase) {
    const first = purchase.items?.[0];

    if (! first) {
        return __("A purchase");
    }

    const name = first.quantity > 1 ? `${first.quantity}× ${first.package_name}` : first.package_name;
    const rest = (purchase.items?.length ?? 0) - 1;

    return rest > 0 ? __(":name and :count more", { name, count: rest }) : name;
}
</script>
