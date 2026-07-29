<script setup>
import { computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import Chart from "@/Components/Dashboard/Chart.vue";
import KpiOverviewCard from "@/Components/Dashboard/KpiOverviewCard.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import XSelect from "@/Components/Form/XSelect.vue";
import { router } from "@inertiajs/vue3";
import {
    BanknotesIcon,
    ShoppingCartIcon,
    ReceiptPercentIcon,
    UsersIcon,
    ClockIcon,
    ArrowUturnLeftIcon,
} from "@heroicons/vue/24/outline";

const { __ } = useTranslations();

const props = defineProps({
    days: Number,
    baseCurrency: Object,
    totals: Object,
    revenueOverTime: Array,
    statusBreakdown: Array,
    topPackages: Array,
    gatewayBreakdown: Array,
    currencyBreakdown: Array,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store Statistics"), current: true },
];

const rangeOptions = {
    7: __("Last 7 days"),
    30: __("Last 30 days"),
    90: __("Last 90 days"),
    365: __("Last year"),
};

// XSelect hands back an option key, which JavaScript always makes a string, so the current value is
// stringified to match.
const selectedRange = computed({
    get: () => String(props.days),
    set: (value) => router.get(route("admin.store.statistics.index"), { days: Number(value) }, {
        preserveState: true,
        preserveScroll: true,
    }),
});

const changeLabel = computed(() => {
    if (props.totals.revenue_change_percent === null) {
        return null;
    }
    const value = props.totals.revenue_change_percent;

    return `${value > 0 ? "+" : ""}${value}%`;
});

const changeClass = computed(() =>
    (props.totals.revenue_change_percent ?? 0) >= 0
        ? "text-success bg-success/10"
        : "text-destructive bg-destructive/10"
);

const revenueChart = computed(() => ({
    tooltip: {
        trigger: "axis",
        // The formatted string comes from the server, which is the only side that knows the
        // currency's symbol, position and number of decimals.
        formatter: (params) => {
            const point = props.revenueOverTime[params[0].dataIndex];

            return `${point.day}<br/>${point.revenue_formatted}<br/>${point.orders} ${__("order(s)")}`;
        },
    },
    grid: { left: "3%", right: "4%", bottom: "3%", containLabel: true },
    xAxis: {
        type: "category",
        boundaryGap: false,
        data: props.revenueOverTime.map(point => point.day),
    },
    yAxis: {
        type: "value",
        name: props.baseCurrency.code,
    },
    series: [
        {
            name: __("Revenue"),
            type: "line",
            smooth: true,
            areaStyle: {},
            data: props.revenueOverTime.map(point => point.revenue),
        },
    ],
}));

const statusChart = computed(() => ({
    tooltip: { trigger: "item" },
    legend: { bottom: 0 },
    series: [
        {
            name: __("Orders"),
            type: "pie",
            radius: ["40%", "65%"],
            data: props.statusBreakdown.map(row => ({
                name: __(row.status),
                value: row.total,
            })),
        },
    ],
}));

const hasRevenue = computed(() => props.revenueOverTime.some(point => point.revenue > 0));
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Statistics')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between items-start mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="w-48">
          <XSelect
            id="days"
            v-model="selectedRange"
            name="days"
            :select-list="rangeOptions"
            :disable-null="true"
          />
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-6">
        {{ __("Every figure is in :currency, converted at the rate each order was placed at rather than today's — so past revenue does not move when a rate does.", { currency: baseCurrency.code }) }}
      </p>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
        <KpiOverviewCard
          :icon="BanknotesIcon"
          icon-class="text-success bg-success/10"
          :title="__('Revenue')"
          :value="totals.revenue_period.formatted"
          :description="__('In the selected range')"
          :change="changeLabel"
          :change-class="changeClass"
          :change-desc="__('vs previous range')"
        />
        <KpiOverviewCard
          :icon="ShoppingCartIcon"
          icon-class="text-primary bg-primary/10"
          :title="__('Orders')"
          :value="totals.orders_period"
          :description="__('Paid orders in the selected range')"
        />
        <KpiOverviewCard
          :icon="ReceiptPercentIcon"
          icon-class="text-primary bg-primary/10"
          :title="__('Average Order')"
          :value="totals.average_order_value.formatted"
          :description="__('Revenue divided by paid orders')"
        />
        <KpiOverviewCard
          :icon="UsersIcon"
          icon-class="text-primary bg-primary/10"
          :title="__('Customers')"
          :value="totals.customers_period"
          :description="__('Distinct players delivered to')"
        />
        <KpiOverviewCard
          :icon="ArrowUturnLeftIcon"
          icon-class="text-destructive bg-destructive/10"
          :title="__('Refunded')"
          :value="totals.refunded_period.formatted"
          :description="__('Orders refunded or disputed in the range')"
        />
        <KpiOverviewCard
          :icon="ClockIcon"
          icon-class="text-muted-foreground bg-muted"
          :title="__('Awaiting Payment')"
          :value="totals.pending_orders"
          :description="__('Pending orders, all time')"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <Card class="lg:col-span-2">
          <CardHeader class="pb-0">
            <CardTitle class="text-sm font-medium">
              {{ __("Revenue Over Time") }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-4">
            <Chart
              v-if="hasRevenue"
              :options="revenueChart"
              height="320px"
              :autoresize="true"
            />
            <p
              v-else
              class="text-sm text-muted-foreground py-24 text-center"
            >
              {{ __("No paid orders in this range yet.") }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-0">
            <CardTitle class="text-sm font-medium">
              {{ __("Orders by Status") }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-4">
            <Chart
              v-if="statusBreakdown.length"
              :options="statusChart"
              height="320px"
              :autoresize="true"
            />
            <p
              v-else
              class="text-sm text-muted-foreground py-24 text-center"
            >
              {{ __("No orders yet.") }}
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">
              {{ __("Best Sellers") }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-4 pt-0">
            <table
              v-if="topPackages.length"
              class="w-full text-sm"
            >
              <tbody>
                <tr
                  v-for="row in topPackages"
                  :key="row.package_name"
                  class="border-b border-border last:border-0"
                >
                  <td class="py-2 pr-2">
                    {{ row.package_name }}
                  </td>
                  <td class="py-2 text-right text-muted-foreground whitespace-nowrap">
                    {{ row.units }} ×
                  </td>
                  <td class="py-2 pl-3 text-right font-medium whitespace-nowrap">
                    {{ row.revenue_formatted }}
                  </td>
                </tr>
              </tbody>
            </table>
            <p
              v-else
              class="text-sm text-muted-foreground"
            >
              {{ __("Nothing sold in this range.") }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">
              {{ __("By Payment Method") }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-4 pt-0">
            <table
              v-if="gatewayBreakdown.length"
              class="w-full text-sm"
            >
              <tbody>
                <tr
                  v-for="row in gatewayBreakdown"
                  :key="row.label"
                  class="border-b border-border last:border-0"
                >
                  <td class="py-2 pr-2 capitalize">
                    {{ row.label === "none" ? __("No payment needed") : row.label }}
                  </td>
                  <td class="py-2 text-right text-muted-foreground whitespace-nowrap">
                    {{ row.orders }}
                  </td>
                  <td class="py-2 pl-3 text-right font-medium whitespace-nowrap">
                    {{ row.revenue_formatted }}
                  </td>
                </tr>
              </tbody>
            </table>
            <p
              v-else
              class="text-sm text-muted-foreground"
            >
              {{ __("Nothing sold in this range.") }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">
              {{ __("By Currency") }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-4 pt-0">
            <table
              v-if="currencyBreakdown.length"
              class="w-full text-sm"
            >
              <tbody>
                <tr
                  v-for="row in currencyBreakdown"
                  :key="row.currency"
                  class="border-b border-border last:border-0"
                >
                  <td class="py-2 pr-2 font-mono text-xs">
                    {{ row.currency }}
                  </td>
                  <td class="py-2 text-right text-muted-foreground whitespace-nowrap">
                    {{ row.native_formatted }}
                  </td>
                  <td class="py-2 pl-3 text-right font-medium whitespace-nowrap">
                    {{ row.revenue_formatted }}
                  </td>
                </tr>
              </tbody>
            </table>
            <p
              v-else
              class="text-sm text-muted-foreground"
            >
              {{ __("Nothing sold in this range.") }}
            </p>
          </CardContent>
        </Card>
      </div>

      <p class="text-xs text-muted-foreground mt-6">
        {{ __("All-time revenue: :amount", { amount: totals.revenue_all_time.formatted }) }}
      </p>
    </div>
  </AdminLayout>
</template>
