<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import StoreCurrencySwitcher from "@/Components/Store/StoreCurrencySwitcher.vue";
import StoreCartBar from "@/Components/Store/StoreCartBar.vue";
import StorePackageCard from "@/Components/Store/StorePackageCard.vue";
import StorePackageListing from "@/Components/Store/StorePackageListing.vue";
import StorePackageComparison from "@/Components/Store/StorePackageComparison.vue";
import StorePackageStacked from "@/Components/Store/StorePackageStacked.vue";
import StoreGoalBox from "@/Shared/StoreGoalBox.vue";
import StoreRecentPurchasesBox from "@/Shared/StoreRecentPurchasesBox.vue";
import StoreTopDonorBox from "@/Shared/StoreTopDonorBox.vue";
import { computed, ref } from "vue";
import { ChevronDownIcon } from "lucide-vue-next";

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
    // Priced server-side for the cart bar. Null when the cart is empty.
    cartTotalFormatted: {
        type: [String, null],
        default: null,
    },
    currency: {
        type: Object,
        required: true,
    },
    // Each key is null when its widget is switched off in the store settings.
    storeWidgets: {
        type: Object,
        default: () => ({ goal: null, recentPurchases: null, topDonor: null }),
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

// The sidebar shipped parent_id from the first version but rendered every category as a
// top-level entry, so a store with sub-categories read as one long undifferentiated list.
// Children are nested under whichever parent is also visible; one whose parent is hidden or
// disabled is promoted rather than dropped, or it would be unreachable.
const categoryTree = computed(() => {
    const visibleIds = new Set(props.categories.map((category) => category.id));

    return props.categories
        .filter((category) => !category.parent_id || !visibleIds.has(category.parent_id))
        .map((category) => ({
            ...category,
            children: props.categories.filter((child) => child.parent_id === category.id),
        }));
});

const isActiveCategory = (category) => props.activeCategory?.slug === category.slug;

// Collapsed on a phone, where the sidebar otherwise pushes the entire catalogue below the fold —
// a visitor landing on the store saw a list of category names and no products at all. Open from
// `lg` up, where it sits beside the grid and costs nothing.
const showCategories = ref(false);
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
          <div class="shrink-0">
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
        <div class="lg:w-1/4 shrink-0">
          <!-- The toggle exists only below `lg`; above it the list is always open. -->
          <button
            type="button"
            class="lg:hidden w-full flex justify-between items-center px-4 py-3 mb-2 bg-card text-card-foreground border border-border rounded-lg shadow font-medium"
            :aria-expanded="showCategories"
            @click="showCategories = !showCategories"
          >
            <span>{{ activeCategory ? activeCategory.name : __("All Packages") }}</span>
            <ChevronDownIcon
              class="w-4 h-4 transition-transform"
              :class="{ 'rotate-180': showCategories }"
            />
          </button>

          <div
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 space-y-2"
            :class="showCategories ? 'block' : 'hidden lg:block'"
          >
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

            <!-- Categories, with any sub-categories nested beneath their parent -->
            <div
              v-for="category in categoryTree"
              :key="category.id"
              class="border-t border-border pt-2 first:border-t-0 first:pt-0"
            >
              <Link
                :href="route('store.category', category.slug)"
                class="flex justify-between items-center px-3 py-2 rounded-lg transition-colors"
                :class="{
                  'bg-muted text-foreground font-semibold': isActiveCategory(category),
                  'text-muted-foreground hover:bg-muted/50': !isActiveCategory(category)
                }"
              >
                <span>{{ category.name }}</span>
                <span class="text-xs bg-muted/50 px-2 py-1 rounded text-muted-foreground">
                  {{ category.packages_count }}
                </span>
              </Link>

              <div
                v-if="category.children.length"
                class="mt-1 ml-3 pl-2 border-l border-border space-y-1"
              >
                <Link
                  v-for="child in category.children"
                  :key="child.id"
                  :href="route('store.category', child.slug)"
                  class="flex justify-between items-center px-3 py-1.5 text-sm rounded-lg transition-colors"
                  :class="{
                    'bg-muted text-foreground font-semibold': isActiveCategory(child),
                    'text-muted-foreground hover:bg-muted/50': !isActiveCategory(child)
                  }"
                >
                  <span>{{ child.name }}</span>
                  <span class="text-xs bg-muted/50 px-2 py-0.5 rounded text-muted-foreground">
                    {{ child.packages_count }}
                  </span>
                </Link>
              </div>
            </div>
          </div>

          <!-- Community boxes. Under the categories rather than above them: the catalogue is what a
               visitor came for, and the goal bar reads as encouragement on the way past. -->
          <div class="mt-4 space-y-4">
            <StoreGoalBox :goal="storeWidgets.goal" />
            <StoreTopDonorBox :donor="storeWidgets.topDonor" />
            <StoreRecentPurchasesBox :purchases="storeWidgets.recentPurchases" />
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

    <!-- Clearance for the fixed cart bar, so the last row of cards is never trapped under it.
         Conditional on the same count the bar is, or every empty-cart visit carries dead space. -->
    <div
      v-if="Number($page.props.store?.cartCount ?? 0) > 0"
      class="h-16"
    />
    <StoreCartBar :total="cartTotalFormatted" />
  </AppLayout>
</template>
