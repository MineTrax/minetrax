<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XDatePicker from "@/Components/Form/XDatePicker.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import Multiselect from "vue-multiselect";
import Draggable from "vuedraggable";
import { ArrowsUpDownIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { useFormErrors } from "@/Composables/useFormErrors";
import { computed } from "vue";

const { __ } = useTranslations();
// Laravel keys a per-item array failure as `packages.0`, not `packages`, so reading the bare field
// name renders nothing and a rejected save looks like a save that silently did nothing.
const { fieldError } = useFormErrors();

const props = defineProps({
    packages: Array,
    categories: Array,
    servers: Array,
    baseCurrency: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Sales"),
        url: route("admin.store.sale.index"),
        current: false,
    },
    {
        text: __("Create Sale"),
        current: true,
    }
];

const discountTypeOptions = {
    percent: __("Percentage Off"),
    fixed: __("Fixed Amount Off"),
};

const scopeTypeOptions = {
    all: __("Whole Store"),
    categories: __("Selected Categories"),
    packages: __("Selected Packages"),
};

const triggerOptions = {
    purchase: __("Purchase"),
    expiry: __("Expiry"),
    refund: __("Refund"),
    chargeback: __("Chargeback"),
};

const serverLabel = (server) => `${server.name} (${server.hostname})`;

// The step belongs to the currency the amount is typed in, never a hardcoded 0.01: JPY has no minor
// unit and KWD has three, so a fixed step either invites an amount the server refuses or rejects a
// legitimate one.
function stepFor(exponent) {
    return exponent === 0 ? "1" : (1 / (10 ** exponent)).toFixed(exponent);
}

const baseStep = computed(() => stepFor(props.baseCurrency?.exponent ?? 2));

const form = useForm({
    name: null,
    discount_type: "percent",
    // Entered as a human figure and converted on submit: percentages are stored as basis points and
    // amounts as minor units, neither of which anyone wants to type.
    discount_percent: 10,
    discount_amount: null,
    min_basket: null,
    starts_at: null,
    ends_at: null,
    is_enabled: true,
    scope_type: "all",
    packages: [],
    categories: [],
    commands: [],
});

const isPercent = computed(() => form.discount_type === "percent");
const isScopedToPackages = computed(() => form.scope_type === "packages");
const isScopedToCategories = computed(() => form.scope_type === "categories");

// The factor is 10^exponent, never a literal 100: JPY has no minor unit and KWD has three digits.
const minorUnitFactor = 10 ** (props.baseCurrency?.exponent ?? 2);

function toMinorUnits(decimalAmount) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return null;
    }
    return Math.round(parseFloat(decimalAmount) * minorUnitFactor);
}

function toBasisPoints(percent) {
    if (percent === null || percent === "" || isNaN(parseFloat(percent))) {
        return null;
    }
    return Math.round(parseFloat(percent) * 100);
}

function salePayload(form) {
    return {
        ...form.data(),
        discount_value: isPercent.value
            ? toBasisPoints(form.discount_percent)
            : toMinorUnits(form.discount_amount),
        // Compared against the cart before any sale is applied, and converted into whatever the
        // buyer is paying in, so it is always entered in the base currency.
        min_basket_amount: toMinorUnits(form.min_basket),
        // Only the picker the chosen mode uses is sent. Otherwise a list left behind from a mode the
        // admin switched away from would be saved as a scope nothing on screen shows.
        packages: isScopedToPackages.value ? (form.packages ?? []).map(item => item.id) : [],
        categories: isScopedToCategories.value ? (form.categories ?? []).map(item => item.id) : [],
        // Multiselect hands back whole objects; the API takes ids.
        commands: form.commands.map(cmd => ({
            ...cmd,
            servers: (cmd.servers ?? []).map(server => ({ id: server.id })),
            packages: (cmd.packages ?? []).map(pkg => ({ id: pkg.id })),
        })),
    };
}

function addCommand() {
    form.commands.push({
        trigger: "purchase",
        command: "",
        servers: [],
        packages: [],
        delay_seconds: 0,
        is_player_online_required: false,
        is_repeat_per_quantity: false,
        sort_order: form.commands.length,
    });
}

function removeCommand(index) {
    // No minimum-one guard, unlike the package form: a sale with no commands is the normal case, so
    // the last row has to be removable.
    form.commands.splice(index, 1);
}

function createSale() {
    form.transform(() => salePayload(form)).post(route("admin.store.sale.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Store Sale')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createSale">
          <div class="shadow rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Discount") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Sale Name')"
                    :help="__('Shown to the customer as the badge on discounted packages.')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XSelect
                    id="discount_type"
                    v-model="form.discount_type"
                    name="discount_type"
                    :label="__('Discount Type')"
                    :select-list="discountTypeOptions"
                    :error="form.errors.discount_type"
                    :disable-null="true"
                  />
                </div>

                <div
                  v-if="isPercent"
                  class="col-span-6 sm:col-span-2"
                >
                  <XInput
                    id="discount_percent"
                    v-model="form.discount_percent"
                    :label="__('Percentage Off')"
                    :help="__('Applied after the package\'s own discount.')"
                    :error="form.errors.discount_value"
                    type="number"
                    step="0.01"
                    name="discount_percent"
                    min="0.01"
                    max="100"
                    required
                  />
                </div>

                <div
                  v-else
                  class="col-span-6 sm:col-span-2"
                >
                  <XInput
                    id="discount_amount"
                    v-model="form.discount_amount"
                    :label="__('Amount Off')"
                    :help="__('In :currency, converted for customers paying in another currency.', { currency: baseCurrency.code })"
                    :error="form.errors.discount_value"
                    type="number"
                    :step="baseStep"
                    name="discount_amount"
                    min="0"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="min_basket"
                    v-model="form.min_basket"
                    :label="__('Minimum Cart Total')"
                    :help="__('In :currency. The sale only applies once the whole cart reaches this, counted before any sale or upgrade credit. Leave empty for no minimum.', { currency: baseCurrency.code })"
                    :error="form.errors.min_basket_amount"
                    type="number"
                    :step="baseStep"
                    name="min_basket"
                    min="0"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XDatePicker
                    id="starts_at"
                    v-model="form.starts_at"
                    :label="__('Starts At')"
                    :help="__('Prices are untouched before this moment. Leave empty to start immediately.')"
                    :error="form.errors.starts_at"
                    type="datetime"
                    format="YYYY-MM-DD hh:mm:ss A"
                    value-type="date"
                    :placeholder="__('Select date and time')"
                    name="starts_at"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XDatePicker
                    id="ends_at"
                    v-model="form.ends_at"
                    :label="__('Ends At')"
                    :help="__('Prices return to normal after this moment. Leave empty to run until disabled.')"
                    :error="form.errors.ends_at"
                    type="datetime"
                    format="YYYY-MM-DD hh:mm:ss A"
                    value-type="date"
                    :placeholder="__('Select date and time')"
                    name="ends_at"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('A disabled sale discounts nothing even inside its window.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="shadow rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Applies To") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("A sale either covers the whole store, or the categories you name, or the packages you name.") }}
              </p>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <XSelect
                    id="scope_type"
                    v-model="form.scope_type"
                    name="scope_type"
                    :label="__('Scope')"
                    :select-list="scopeTypeOptions"
                    :error="form.errors.scope_type"
                    :disable-null="true"
                  />
                </div>

                <div
                  v-if="isScopedToPackages"
                  class="col-span-6 sm:col-span-4"
                >
                  <label
                    for="packages"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Packages") }}</label>
                  <Multiselect
                    id="packages"
                    v-model="form.packages"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="packages"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    track-by="id"
                    label="name"
                    :placeholder="__('Search packages')+'...'"
                  />
                  <p
                    v-if="fieldError(form.errors, 'packages')"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ fieldError(form.errors, 'packages') }}
                  </p>
                </div>

                <div
                  v-if="isScopedToCategories"
                  class="col-span-6 sm:col-span-4"
                >
                  <label
                    for="categories"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Categories") }}</label>
                  <Multiselect
                    id="categories"
                    v-model="form.categories"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="categories"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    track-by="id"
                    label="name"
                    :placeholder="__('Search categories')+'...'"
                  />
                  <p
                    v-if="fieldError(form.errors, 'categories')"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ fieldError(form.errors, 'categories') }}
                  </p>
                  <p class="text-xs text-muted-foreground mt-2">
                    {{ __("Packages filed under a child category are not included. Name the child categories too.") }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Commands Section -->
          <div class="shadow rounded-lg card-clip-safe mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Bonus Commands") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-1">
                {{ __("Run on top of the package's own commands, but only for a package that was bought while this sale was discounting it. Name a package per command to give different amounts — 100 coins on one, 1000 on another.") }}
              </p>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Available placeholders: {PLAYER_USERNAME}, {PLAYER_UUID}, {QUANTITY}, {PACKAGE_NAME}, {ORDER_UUID}, {SALE_NAME}, {SALE_ID}") }}
              </p>

              <div class="space-y-4">
                <Draggable
                  v-model="form.commands"
                  :swap-threshold="0.65"
                  class="space-y-3"
                  handle=".drag-handle"
                >
                  <template #item="{ element: command, index }">
                    <div class="p-4 bg-muted/50 rounded-lg space-y-4">
                      <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12 lg:col-span-1 flex gap-2 lg:mt-6 lg:flex-col">
                          <div class="drag-handle cursor-move">
                            <ArrowsUpDownIcon class="w-5 h-5 text-muted-foreground hover:text-foreground" />
                          </div>
                          <button
                            type="button"
                            class="focus:outline-hidden group cursor-pointer"
                            @click="removeCommand(index)"
                          >
                            <TrashIcon class="w-5 h-5 text-muted-foreground group-hover:text-destructive" />
                          </button>
                        </div>

                        <div class="col-span-12 sm:col-span-4 lg:col-span-2">
                          <XSelect
                            v-model="command.trigger"
                            :label="__('Trigger')"
                            :select-list="triggerOptions"
                            :error="form.errors[`commands.${index}.trigger`]"
                            :disable-null="true"
                          />
                        </div>

                        <div class="col-span-12 sm:col-span-6 lg:col-span-6">
                          <XInput
                            v-model="command.command"
                            :label="__('Command')"
                            :error="form.errors[`commands.${index}.command`]"
                            type="text"
                            name="command"
                          />
                        </div>

                        <div class="col-span-12 sm:col-span-2 lg:col-span-1">
                          <XInput
                            v-model.number="command.delay_seconds"
                            :label="__('Delay (s)')"
                            :error="form.errors[`commands.${index}.delay_seconds`]"
                            type="number"
                            name="delay_seconds"
                            min="0"
                          />
                        </div>

                        <div class="col-span-12 lg:col-span-6">
                          <label class="block text-sm font-medium text-foreground mb-2">{{ __("Run for packages") }}</label>
                          <Multiselect
                            v-model="command.packages"
                            class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                            :options="packages"
                            track-by="id"
                            label="name"
                            :multiple="true"
                            :close-on-select="false"
                            :clear-on-select="false"
                            :searchable="true"
                            :placeholder="__('Leave empty to run for every package this sale discounts')+'...'"
                          />
                          <p
                            v-if="fieldError(form.errors, `commands.${index}.packages`)"
                            class="text-xs text-destructive mt-1"
                          >
                            {{ fieldError(form.errors, `commands.${index}.packages`) }}
                          </p>
                        </div>

                        <div class="col-span-12 lg:col-span-6">
                          <label class="block text-sm font-medium text-foreground mb-2">{{ __("Run on servers") }}</label>
                          <Multiselect
                            v-model="command.servers"
                            class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                            :options="servers"
                            :custom-label="serverLabel"
                            track-by="id"
                            :multiple="true"
                            :close-on-select="false"
                            :clear-on-select="false"
                            :searchable="true"
                            :placeholder="__('Leave empty to run on all servers')+'...'"
                          />
                          <p
                            v-if="fieldError(form.errors, `commands.${index}.servers`)"
                            class="text-xs text-destructive mt-1"
                          >
                            {{ fieldError(form.errors, `commands.${index}.servers`) }}
                          </p>
                        </div>
                      </div>

                      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 border-t border-border pt-4">
                        <XSwitch
                          :id="`command_online_${index}`"
                          v-model="command.is_player_online_required"
                          :label="__('Require player to be online')"
                          :help="__('This command only runs while the player is online on the target server. If they are offline it is queued and runs the moment they join.')"
                          :error="form.errors[`commands.${index}.is_player_online_required`]"
                          :name="`command_online_${index}`"
                        />

                        <XSwitch
                          :id="`command_repeat_${index}`"
                          v-model="command.is_repeat_per_quantity"
                          :label="__('Repeat once per quantity')"
                          :help="__('Buying 3 runs this command 3 times. Leave off to run it once with {QUANTITY} substituted instead — right for a rank, wrong for crate keys.')"
                          :error="form.errors[`commands.${index}.is_repeat_per_quantity`]"
                          :name="`command_repeat_${index}`"
                        />
                      </div>
                    </div>
                  </template>
                </Draggable>

                <div class="flex justify-end mt-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addCommand"
                  >
                    {{ __("Add Command") }}
                  </Button>
                </div>
              </div>
            </div>

            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2 rounded-b-lg">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store.sale.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ __("Create Sale") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
