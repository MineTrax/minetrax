<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import StoreCurrencySwitcher from "@/Components/Store/StoreCurrencySwitcher.vue";
import StorePackageCard from "@/Components/Store/StorePackageCard.vue";
import StorePackageListing from "@/Components/Store/StorePackageListing.vue";
import StorePackageComparison from "@/Components/Store/StorePackageComparison.vue";
import StorePackageStacked from "@/Components/Store/StorePackageStacked.vue";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storeName: {
        type: String,
        required: true,
    },
    storeDescription: {
        type: [String, null],
    },
    categories: {
        type: Array,
        required: true,
    },
    activeCategory: {
        type: Object,
    },
    packages: {
        type: Array,
        required: true,
    },
    currency: {
        type: Object,
        required: true,
    },
});

// The layout belongs to the category, so the store index — which has no category — is always a
// grid. A comparison table with no fields configured would be a single blank column, so it falls
// back to the grid rather than rendering nothing useful.
const displayType = computed(() => {
    const chosen = props.activeCategory?.display_type?.value ?? "grid";

    if (chosen === "comparison" && !(props.activeCategory?.comparison_fields?.length)) {
        return "grid";
    }
    return chosen;
});
</script>

<template>
  <AppLayout>
    <AppHead :title="storeName" />

    <!-- Header -->
    <div class="bg-card text-card-foreground border-b border-border">
      <div class="px-2 py-6 md:px-10 max-w-screen-2xl mx-auto">
        <div class="flex justify-between items-start gap-4">
          <div>
            <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-2">
              {{ storeName }}
            </h1>
            <p
              v-if="storeDescription"
              class="text-muted-foreground"
            >
              {{ storeDescription }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <StoreCurrencySwitcher
              :currencies="currency.available"
              :current="currency.current"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="px-2 py-6 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar -->
        <div class="lg:w-1/4 flex-shrink-0">
          <div class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 space-y-2">
            <!-- All Packages Link -->
            <Link
              :href="route('store.index')"
              class="block px-3 py-2 rounded-lg transition-colors"
              :class="{
                'bg-muted text-foreground font-semibold': !activeCategory,
                'text-muted-foreground hover:bg-muted/50': activeCategory
              }"
            >
              {{ __("All Packages") }}
            </Link>

            <!-- Categories -->
            <div
              v-for="category in categories"
              :key="category.id"
              class="border-t border-border pt-2 first:border-t-0 first:pt-0"
            >
              <Link
                :href="route('store.category', category.slug)"
                class="flex justify-between items-center px-3 py-2 rounded-lg transition-colors"
                :class="{
                  'bg-muted text-foreground font-semibold': activeCategory?.slug === category.slug,
                  'text-muted-foreground hover:bg-muted/50': activeCategory?.slug !== category.slug
                }"
              >
                <span>{{ category.name }}</span>
                <span class="text-xs bg-muted/50 px-2 py-1 rounded text-muted-foreground">
                  {{ category.packages_count }}
                </span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Main Grid -->
        <div class="lg:w-3/4 flex-1">
          <!-- Category Header -->
          <div
            v-if="activeCategory"
            class="mb-6"
          >
            <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-2">
              {{ activeCategory.name }}
            </h2>
            <p
              v-if="activeCategory.description"
              class="text-muted-foreground"
            >
              {{ activeCategory.description }}
            </p>
          </div>

          <!-- Packages, laid out the way the category asked for -->
          <template v-if="packages.length > 0">
            <StorePackageComparison
              v-if="displayType === 'comparison'"
              :packages="packages"
              :fields="activeCategory.comparison_fields"
            />

            <div
              v-else-if="displayType === 'listing'"
              class="space-y-3"
            >
              <StorePackageListing
                v-for="storePackage in packages"
                :key="storePackage.id"
                :store-package="storePackage"
              />
            </div>

            <div
              v-else-if="displayType === 'stacked'"
              class="space-y-3"
            >
              <StorePackageStacked
                v-for="storePackage in packages"
                :key="storePackage.id"
                :store-package="storePackage"
              />
            </div>

            <div
              v-else
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
            >
              <StorePackageCard
                v-for="storePackage in packages"
                :key="storePackage.id"
                :store-package="storePackage"
              />
            </div>
          </template>

          <!-- Empty State -->
          <div
            v-if="packages.length === 0"
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-12 text-center"
          >
            <svg
              class="w-16 h-16 mx-auto text-muted-foreground mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
              />
            </svg>
            <h3 class="text-lg font-semibold text-foreground mb-2">
              {{ __("No Packages Available") }}
            </h3>
            <p class="text-muted-foreground">
              {{ __("There are no packages available at the moment.") }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
