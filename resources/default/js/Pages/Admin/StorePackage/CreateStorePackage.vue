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
import { useFormErrors } from "@/Composables/useFormErrors";
import TipTapEditor from "@/Components/TipTapEditor.vue";
import ImageUpload from "@/Components/Form/ImageUpload.vue";
import Draggable from "vuedraggable";
import { ArrowsUpDownIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";

const { __ } = useTranslations();
// Laravel keys a per-item array failure as `packages.0`, not `packages`, so reading the bare field
// name renders nothing and a rejected save looks like a save that silently did nothing.
const { fieldError } = useFormErrors();

const props = defineProps({
    categories: Array,
    servers: Array,
    packages: Array,
    variables: Array,
    baseCurrency: Object,
});

// The factor is 10^exponent, never a literal 100: JPY has no minor unit and KWD has three
// digits, so a fixed 100 would silently misprice both.
const minorUnitFactor = 10 ** (props.baseCurrency?.exponent ?? 2);

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Packages"),
        url: route("admin.store.package.index"),
        current: false,
    },
    {
        text: __("Create Package"),
        current: true,
    }
];

const triggerOptions = {
    purchase: __("Purchase"),
    expiry: __("Expiry"),
    refund: __("Refund"),
    chargeback: __("Chargeback"),
};

const typeOptions = {
    minecraft_package: __("Minecraft Package"),
    giftcard: __("Giftcard"),
    both: __("Minecraft Package & Giftcard"),
};

const requirementModeOptions = {
    all: __("Require All"),
    any: __("Require One"),
};

const serverLabel = (server) => `${server.name} (${server.hostname})`;

const categoriesOptions = props.categories.reduce((acc, cat) => {
    return { ...acc, [cat.id]: cat.name };
}, {});

// The step belongs to the currency the amount is typed in, never a hardcoded 0.01: JPY has no minor
// unit and KWD has three, so a fixed step either invites an amount the server refuses or rejects a
// legitimate one.
function stepFor(exponent) {
    return exponent === 0 ? "1" : (1 / (10 ** exponent)).toFixed(exponent);
}

const baseStep = computed(() => stepFor(props.baseCurrency?.exponent ?? 2));

const form = useForm({
    name: null,
    // Left blank the server builds it from the name, which is what most packages want.
    slug: null,
    store_category_id: null,
    short_description: null,
    description: "",
    type: "minecraft_package",
    price: null,
    // Entered as a percentage and sent as basis points, which is how coupons and sales are stored.
    discount_percent: 0,
    is_pay_what_you_want: false,
    pay_what_you_want_max: null,
    gift_card_amount: null,
    is_gift_card_amount_same_as_price: false,
    sort_order: 0,
    is_visible: true,
    is_enabled: true,
    requires_login: false,
    is_featured: false,
    is_giftable: false,
    min_quantity: 1,
    max_quantity: null,
    player_purchase_limit: null,
    player_purchase_limit_period_days: null,
    global_purchase_limit: null,
    global_purchase_limit_period_days: null,
    expiry_duration_days: null,
    available_from: null,
    available_until: null,
    required_packages: [],
    required_packages_mode: "all",
    variables: [],
    comparison_values: {},
    photo: null,
    commands: [
        {
            trigger: "purchase",
            command: "",
            servers: [],
            delay_seconds: 0,
            is_player_online_required: false,
            is_repeat_per_quantity: false,
            sort_order: 0,
        }
    ],
});

const issuesGiftCard = computed(() => form.type === "giftcard" || form.type === "both");

function convertPriceToMinorUnits(decimalPrice) {
    if (decimalPrice === null || decimalPrice === undefined || decimalPrice === "") {
        return null;
    }
    return Math.round(parseFloat(decimalPrice) * minorUnitFactor);
}

// Percent to basis points: 12.5% becomes 1250.
function convertPercentToBasisPoints(percent) {
    if (percent === null || percent === undefined || percent === "") {
        return 0;
    }
    return Math.round(parseFloat(percent) * 100);
}

// The placeholder is namespaced, so it is not guessable from the variable's name alone. Listing it
// next to the command builder is the difference between copying it and mistyping it.
const attachedVariablePlaceholders = computed(() =>
    (form.variables ?? []).map(variable => ({
        id: variable.id,
        name: variable.name,
        placeholder: `{VARIABLE_${(variable.identifier || "").toUpperCase()}}`,
    }))
);

// Only a placeholder, showing what leaving the field empty will produce. The server does the real
// normalising, so this deliberately does not try to match every rule it applies.
const slugPreview = computed(() => {
    const derived = (form.name ?? "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");

    return derived || __("built from the name");
});

// The comparison rows belong to the chosen category, so this follows the dropdown rather than being
// baked in at render. Categories not using the table layout report no fields, which hides the card.
const comparisonFields = computed(() => {
    // Compared as strings: XSelect hands back an object key, which is always a string, while the
    // id is a number.
    const category = props.categories.find(item => String(item.id) === String(form.store_category_id));

    if (! category || (category.display_type?.value ?? category.display_type) !== "comparison") {
        return [];
    }
    return category.comparison_fields ?? [];
});

function packagePayload(form) {
    return {
        ...form.data(),
        price: convertPriceToMinorUnits(form.price),
        discount_bp: convertPercentToBasisPoints(form.discount_percent),
        pay_what_you_want_max: convertPriceToMinorUnits(form.pay_what_you_want_max),
        gift_card_amount: convertPriceToMinorUnits(form.gift_card_amount),
        // Multiselect hands back whole objects; the API takes ids.
        required_packages: (form.required_packages ?? []).map(item => item.id),
        variables: (form.variables ?? []).map(item => item.id),
        commands: form.commands.map(cmd => ({
            ...cmd,
            servers: (cmd.servers ?? []).map(server => ({ id: server.id })),
        })),
    };
}

function addCommand() {
    form.commands.push({
        trigger: "purchase",
        command: "",
        servers: [],
        delay_seconds: 0,
        is_player_online_required: false,
        is_repeat_per_quantity: false,
        sort_order: form.commands.length,
    });
}

function removeCommand(index) {
    if (form.commands.length > 1) {
        form.commands.splice(index, 1);
    }
}

function createPackage() {
    form.transform(() => packagePayload(form)).post(route("admin.store.package.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Store Package')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createPackage">
          <!-- Details Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Details") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Package Name')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="slug"
                    v-model="form.slug"
                    :label="__('URL Slug')"
                    :help="__('The package\'s address on the store. Leave empty to build it from the name.')"
                    :error="form.errors.slug"
                    type="text"
                    name="slug"
                    :placeholder="slugPreview"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="store_category_id"
                    v-model="form.store_category_id"
                    name="store_category_id"
                    :label="__('Category')"
                    :placeholder="__('Select a category')"
                    :select-list="categoriesOptions"
                    :error="form.errors.store_category_id"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="type"
                    v-model="form.type"
                    name="type"
                    :label="__('Package Type')"
                    :help="__('What a purchase delivers: in-game commands, store credit, or both.')"
                    :select-list="typeOptions"
                    :error="form.errors.type"
                    :disable-null="true"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="short_description"
                    v-model="form.short_description"
                    :label="__('Short Description')"
                    :error="form.errors.short_description"
                    type="text"
                    name="short_description"
                  />
                </div>

                <div class="col-span-6">
                  <label class="block text-sm font-medium text-foreground mb-2">
                    {{ __("Description") }}
                  </label>
                  <TipTapEditor
                    id="description"
                    v-model="form.description"
                  />
                  <p
                    v-if="form.errors.description"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.description }}
                  </p>
                </div>

                <div class="col-span-6">
                  <ImageUpload
                    id="photo"
                    v-model="form.photo"
                    :label="__('Package Image')"
                    :error="form.errors.photo"
                    name="photo"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Pricing Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Pricing") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="price"
                    v-model="form.price"
                    :label="form.is_pay_what_you_want ? __('Minimum Price') : __('Price')"
                    :help="form.is_pay_what_you_want
                      ? __('The least a customer may pay, in :currency.', { currency: baseCurrency.code })
                      : __('Decimal amount in :currency, e.g. 9.99', { currency: baseCurrency.code })"
                    :error="form.errors.price"
                    type="number"
                    :step="baseStep"
                    name="price"
                    min="0"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="discount_percent"
                    v-model="form.discount_percent"
                    :label="__('Discount (%)')"
                    :help="__('Shown as a strike-through on the storefront. Leave at 0 for none.')"
                    :error="form.errors.discount_bp"
                    type="number"
                    step="0.01"
                    name="discount_percent"
                    min="0"
                    max="100"
                    :disabled="form.is_pay_what_you_want"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="pay_what_you_want_max"
                    v-model="form.pay_what_you_want_max"
                    :label="__('Maximum Price')"
                    :help="__('Optional cap on what a customer may pay.')"
                    :error="form.errors.pay_what_you_want_max"
                    type="number"
                    :step="baseStep"
                    name="pay_what_you_want_max"
                    min="0"
                    :disabled="!form.is_pay_what_you_want"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="min_quantity"
                    v-model.number="form.min_quantity"
                    :label="__('Min Quantity')"
                    :error="form.errors.min_quantity"
                    type="number"
                    name="min_quantity"
                    min="1"
                    required
                    :disabled="form.is_pay_what_you_want"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="max_quantity"
                    v-model.number="form.max_quantity"
                    :label="__('Max Quantity')"
                    :help="__('Leave empty for unlimited')"
                    :error="form.errors.max_quantity"
                    type="number"
                    name="max_quantity"
                    min="1"
                    :disabled="form.is_pay_what_you_want"
                  />
                </div>

                <div class="col-span-6 border-t border-border pt-4">
                  <XSwitch
                    id="is_pay_what_you_want"
                    v-model="form.is_pay_what_you_want"
                    :label="__('Allow customers to pay what they want')"
                    :help="__('The customer names the amount at checkout. The price above becomes the minimum, and the quantity is fixed at one.')"
                    name="is_pay_what_you_want"
                    :error="form.errors.is_pay_what_you_want"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Gift Card Section -->
          <div
            v-if="issuesGiftCard"
            class="shadow overflow-hidden rounded-lg mb-6"
          >
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Selling Gift Card") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="gift_card_amount"
                    v-model="form.gift_card_amount"
                    :label="__('Create A Gift Card')"
                    :help="__('Store credit sent to the customer after purchase, in :currency.', { currency: baseCurrency.code })"
                    :error="form.errors.gift_card_amount"
                    type="number"
                    :step="baseStep"
                    name="gift_card_amount"
                    min="0"
                    :disabled="form.is_gift_card_amount_same_as_price"
                  />
                </div>

                <!-- pt-9 lines the switch up with the middle of the h-9 input beside it, past that
                     input's own label. items-end would sit it level with the help text instead. -->
                <div class="col-span-6 sm:col-span-3 sm:pt-9">
                  <XSwitch
                    id="is_gift_card_amount_same_as_price"
                    v-model="form.is_gift_card_amount_same_as_price"
                    :label="__('Same as Package Price')"
                    :help="__('Issue credit worth exactly what the customer paid for this line.')"
                    name="is_gift_card_amount_same_as_price"
                    :error="form.errors.is_gift_card_amount_same_as_price"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Requirements Section -->
          <div class="shadow rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border rounded-lg">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Required Packages") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-4">
                  <label class="block text-sm font-medium text-foreground mb-2">
                    {{ __("Packages the customer must already own") }}
                  </label>
                  <Multiselect
                    v-model="form.required_packages"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="packages"
                    label="name"
                    track-by="id"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :searchable="true"
                    :placeholder="__('Leave empty for no requirement')+'...'"
                  />
                  <p class="text-xs text-muted-foreground mt-1">
                    {{ __("Checked at checkout against the delivery player's active purchases. Buying a requirement in the same order counts.") }}
                  </p>
                  <p
                    v-if="fieldError(form.errors, 'required_packages')"
                    class="text-xs text-destructive mt-1"
                  >
                    {{ fieldError(form.errors, 'required_packages') }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XSelect
                    id="required_packages_mode"
                    v-model="form.required_packages_mode"
                    name="required_packages_mode"
                    :label="__('Requirement Mode')"
                    :select-list="requirementModeOptions"
                    :error="form.errors.required_packages_mode"
                    :disable-null="true"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Availability Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Availability") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="player_purchase_limit"
                    v-model.number="form.player_purchase_limit"
                    :label="__('Purchase Limit Per Player')"
                    :help="__('Leave empty for unlimited')"
                    :error="form.errors.player_purchase_limit"
                    type="number"
                    name="player_purchase_limit"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="player_purchase_limit_period_days"
                    v-model.number="form.player_purchase_limit_period_days"
                    :label="__('Per Player Limit Resets After (Days)')"
                    :help="__('Leave empty so the limit never resets')"
                    :error="form.errors.player_purchase_limit_period_days"
                    type="number"
                    name="player_purchase_limit_period_days"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="global_purchase_limit"
                    v-model.number="form.global_purchase_limit"
                    :label="__('Purchase Limit For Everyone')"
                    :help="__('Total across all players. Leave empty for unlimited')"
                    :error="form.errors.global_purchase_limit"
                    type="number"
                    name="global_purchase_limit"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="global_purchase_limit_period_days"
                    v-model.number="form.global_purchase_limit_period_days"
                    :label="__('Everyone Limit Resets After (Days)')"
                    :help="__('Leave empty so the limit never resets, which makes it a fixed stock')"
                    :error="form.errors.global_purchase_limit_period_days"
                    type="number"
                    name="global_purchase_limit_period_days"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="expiry_duration_days"
                    v-model.number="form.expiry_duration_days"
                    :label="__('Expiry Duration (Days)')"
                    :help="__('Package expires after N days. Leave empty for no expiry')"
                    :error="form.errors.expiry_duration_days"
                    type="number"
                    name="expiry_duration_days"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="sort_order"
                    v-model.number="form.sort_order"
                    :label="__('Sort Order')"
                    :error="form.errors.sort_order"
                    type="number"
                    name="sort_order"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XDatePicker
                    id="available_from"
                    v-model="form.available_from"
                    :label="__('Publish On Webstore At')"
                    :help="__('Hidden from the store until this moment. Leave empty to publish immediately')"
                    :error="form.errors.available_from"
                    type="datetime"
                    format="YYYY-MM-DD hh:mm:ss A"
                    value-type="date"
                    :placeholder="__('Select date and time')"
                    name="available_from"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XDatePicker
                    id="available_until"
                    v-model="form.available_until"
                    :label="__('Remove From Webstore After')"
                    :help="__('Withdrawn from the store after this moment. Leave empty to keep it listed')"
                    :error="form.errors.available_until"
                    type="datetime"
                    format="YYYY-MM-DD hh:mm:ss A"
                    value-type="date"
                    :placeholder="__('Select date and time')"
                    name="available_until"
                  />
                </div>

                <div class="col-span-6 grid grid-cols-1 lg:grid-cols-2 gap-4 border-t border-border pt-4">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enable this package and make it visible?')"
                    :help="__('A disabled package is gone from the store entirely, whatever its publish dates say.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />

                  <XSwitch
                    id="is_visible"
                    v-model="form.is_visible"
                    :label="__('List this package in the store?')"
                    :help="__('Turn this off to keep the package buyable by direct link only.')"
                    name="is_visible"
                    :error="form.errors.is_visible"
                  />

                  <XSwitch
                    id="is_featured"
                    v-model="form.is_featured"
                    :label="__('Mark this package as featured?')"
                    :help="__('Featured packages are pinned to the top of their category and carry a badge.')"
                    name="is_featured"
                    :error="form.errors.is_featured"
                  />

                  <XSwitch
                    id="requires_login"
                    v-model="form.requires_login"
                    :label="__('Require the customer to be signed in?')"
                    :help="__('Guests cannot add this package to their cart.')"
                    name="requires_login"
                    :error="form.errors.requires_login"
                  />

                  <XSwitch
                    id="is_giftable"
                    v-model="form.is_giftable"
                    :label="__('Allow users to send this package as a gift to others?')"
                    :help="__('With this off, a signed-in customer with a linked player may only buy it for themselves.')"
                    name="is_giftable"
                    :error="form.errors.is_giftable"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Comparison Values Section -->
          <div
            v-if="comparisonFields.length"
            class="shadow overflow-hidden rounded-lg mb-6"
          >
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Comparison Values") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("This package's column in its category's comparison table. Leave a cell empty to show a dash.") }}
              </p>
              <div class="grid grid-cols-6 gap-6">
                <div
                  v-for="field in comparisonFields"
                  :key="field.key"
                  class="col-span-6 sm:col-span-3"
                >
                  <XSwitch
                    v-if="field.type === 'check'"
                    :id="`comparison_${field.key}`"
                    :model-value="!! form.comparison_values[field.key]"
                    :label="field.name"
                    :help="field.description"
                    :name="`comparison_${field.key}`"
                    @update:model-value="form.comparison_values[field.key] = $event"
                  />
                  <XInput
                    v-else
                    :id="`comparison_${field.key}`"
                    v-model="form.comparison_values[field.key]"
                    :label="field.name"
                    :help="field.description"
                    type="text"
                    :name="`comparison_${field.key}`"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Variables Section -->
          <div class="shadow rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border rounded-lg">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Variables") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Fields the customer fills in while ordering, such as a name prefix or colour. Their values are substituted into this package's commands.") }}
              </p>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <label class="block text-sm font-medium text-foreground mb-2">
                    {{ __("Attached Variables") }}
                  </label>
                  <Multiselect
                    v-model="form.variables"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="variables"
                    label="name"
                    track-by="id"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :searchable="true"
                    :placeholder="__('Leave empty to ask the customer for nothing')+'...'"
                  />
                  <p
                    v-if="fieldError(form.errors, 'variables')"
                    class="text-xs text-destructive mt-1"
                  >
                    {{ fieldError(form.errors, 'variables') }}
                  </p>
                </div>

                <div
                  v-if="attachedVariablePlaceholders.length"
                  class="col-span-6 border-t border-border pt-4"
                >
                  <p class="text-sm font-medium text-foreground mb-2">
                    {{ __("Use these placeholders in the commands below") }}
                  </p>
                  <ul class="space-y-1">
                    <li
                      v-for="variable in attachedVariablePlaceholders"
                      :key="variable.id"
                      class="text-sm text-muted-foreground flex items-center gap-2"
                    >
                      <code class="px-1.5 py-0.5 rounded bg-muted text-foreground text-xs font-mono select-all">{{ variable.placeholder }}</code>
                      <span>{{ variable.name }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Commands Section -->
          <div class="shadow rounded-lg card-clip-safe mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Commands") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Available placeholders: {PLAYER_USERNAME}, {PLAYER_UUID}, {QUANTITY}, {PACKAGE_NAME}, {ORDER_UUID}, plus {VARIABLE_*} for each variable attached above") }}
              </p>

              <div class="space-y-4">
                <div class="hidden lg:grid grid-cols-12 gap-3">
                  <div class="col-span-1" />
                  <label class="col-span-2 block text-sm font-medium text-foreground">{{ __("Trigger") }}</label>
                  <label class="col-span-4 block text-sm font-medium text-foreground">{{ __("Command") }}</label>
                  <label class="col-span-2 block text-sm font-medium text-foreground">{{ __("Target") }}</label>
                  <label class="col-span-1 block text-sm font-medium text-foreground">{{ __("Delay (s)") }}</label>
                  <label class="col-span-2 block text-sm font-medium text-foreground">{{ __("Player Online") }}</label>
                </div>

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

                        <div class="col-span-12 sm:col-span-6 lg:col-span-4">
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

                        <div class="col-span-12 lg:col-span-4">
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
                            v-if="form.errors[`commands.${index}.servers`]"
                            class="text-xs text-destructive mt-1"
                          >
                            {{ form.errors[`commands.${index}.servers`] }}
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
          </div>

          <!-- Form Footer -->
          <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2 rounded-b-lg">
            <Button
              variant="outline"
              as-child
            >
              <Link :href="route('admin.store.package.index')">
                {{ __("Cancel") }}
              </Link>
            </Button>
            <Button
              type="submit"
              :disabled="form.processing"
            >
              <svg
                v-if="form.processing"
                class="animate-spin -ml-1 mr-2 h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              {{ __("Create Package") }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
