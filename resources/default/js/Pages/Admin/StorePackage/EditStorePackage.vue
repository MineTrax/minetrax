<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import XCheckbox from "@/Components/Form/XCheckbox.vue";
import XTextarea from "@/Components/Form/XTextarea.vue";
import TipTapEditor from "@/Components/TipTapEditor.vue";
import ImageUpload from "@/Components/Form/ImageUpload.vue";
import Draggable from "vuedraggable";
import { ArrowsUpDownIcon, TrashIcon } from "@heroicons/vue/24/outline";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: Object,
    categories: Array,
    servers: Array,
    selectedServers: Array,
    baseCurrency: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Packages"),
        url: route("admin.store-package.index"),
        current: false,
    },
    {
        text: __("Edit Package"),
        current: false,
    },
    {
        text: "#" + props.storePackage.id,
        current: true,
    }
];

const triggerOptions = {
    purchase: __("Purchase"),
    expiry: __("Expiry"),
    refund: __("Refund"),
    chargeback: __("Chargeback"),
};

const targetOptions = {
    package_servers: __("Package Servers"),
    all_servers: __("All Servers"),
};

const triStateOptions = {
    "null": __("Inherit"),
    "true": __("Yes"),
    "false": __("No"),
};

const categoriesOptions = props.categories.reduce((acc, cat) => {
    return { ...acc, [cat.id]: cat.name };
}, {});

// The factor is 10^exponent, never a literal 100: JPY has no minor unit and KWD has three
// digits, so a fixed 100 would silently misprice both.
const minorUnitFactor = 10 ** (props.baseCurrency?.exponent ?? 2);

function convertPriceFromMinorUnits(minorUnits) {
    if (minorUnits === null || minorUnits === undefined) {
        return null;
    }
    return (minorUnits / minorUnitFactor).toFixed(props.baseCurrency?.exponent ?? 2);
}

function convertPriceToMinorUnits(decimalPrice) {
    if (decimalPrice === null || decimalPrice === undefined || decimalPrice === "") {
        return null;
    }
    return Math.round(parseFloat(decimalPrice) * minorUnitFactor);
}

function convertTriStateToString(value) {
    if (value === null) return "null";
    if (value === true) return "true";
    if (value === false) return "false";
    return value;
}

const form = useForm({
    name: props.storePackage.name,
    store_category_id: props.storePackage.store_category_id,
    short_description: props.storePackage.short_description,
    description: props.storePackage.description || "",
    price: convertPriceFromMinorUnits(props.storePackage.price),
    sort_order: props.storePackage.sort_order,
    is_visible: props.storePackage.is_visible,
    is_enabled: props.storePackage.is_enabled,
    requires_login: props.storePackage.requires_login,
    is_run_on_all_servers: props.storePackage.is_run_on_all_servers,
    is_player_online_required: props.storePackage.is_player_online_required,
    is_command_repeated_per_quantity: props.storePackage.is_command_repeated_per_quantity,
    min_quantity: props.storePackage.min_quantity,
    max_quantity: props.storePackage.max_quantity,
    stock_limit: props.storePackage.stock_limit,
    player_purchase_limit: props.storePackage.player_purchase_limit,
    purchase_limit_period_days: props.storePackage.purchase_limit_period_days,
    expiry_duration_days: props.storePackage.expiry_duration_days,
    photo: null,
    servers: props.selectedServers || [],
    commands: (props.storePackage.commands || []).map(cmd => ({
        id: cmd.id,
        trigger: cmd.trigger,
        command: cmd.command,
        target: cmd.target,
        delay_seconds: cmd.delay_seconds,
        is_player_online_required: convertTriStateToString(cmd.is_player_online_required),
        is_repeat_per_quantity: convertTriStateToString(cmd.is_repeat_per_quantity),
        sort_order: cmd.sort_order,
    })),
    options: (props.storePackage.options || []).map(opt => ({
        id: opt.id,
        name: opt.name,
        placeholder: opt.placeholder,
        is_required: opt.is_required,
        type: opt.type,
        description: opt.description || "",
        sort_order: opt.sort_order,
        choices: (opt.choices || []).map(choice => ({
            id: choice.id,
            name: choice.name,
            value: choice.value,
            price_delta: convertPriceFromMinorUnits(choice.price_delta),
            is_enabled: choice.is_enabled,
            sort_order: choice.sort_order,
        })),
    })),
    "_method": "PUT",
});

function addCommand() {
    form.commands.push({
        trigger: "purchase",
        command: "",
        target: "package_servers",
        delay_seconds: 0,
        is_player_online_required: "null",
        is_repeat_per_quantity: "null",
        sort_order: form.commands.length,
    });
}

function removeCommand(index) {
    if (form.commands.length > 1) {
        form.commands.splice(index, 1);
    }
}

function addOption() {
    form.options.push({
        name: "",
        placeholder: "",
        is_required: false,
        type: "select",
        description: "",
        sort_order: form.options.length,
        choices: [
            {
                name: "",
                value: "",
                price_delta: null,
                is_enabled: true,
                sort_order: 0,
            }
        ],
    });
}

function removeOption(index) {
    if (form.options.length > 1) {
        form.options.splice(index, 1);
    }
}

function addChoice(optionIndex) {
    form.options[optionIndex].choices.push({
        name: "",
        value: "",
        price_delta: null,
        is_enabled: true,
        sort_order: form.options[optionIndex].choices.length,
    });
}

function removeChoice(optionIndex, choiceIndex) {
    if (form.options[optionIndex].choices.length > 1) {
        form.options[optionIndex].choices.splice(choiceIndex, 1);
    }
}

function updatePackage() {
    // Convert price from decimal to minor units
    const payload = {
        ...form.data(),
        price: convertPriceToMinorUnits(form.price),
        // Convert all choice price_delta from decimal to minor units
        options: form.options.map(option => ({
            ...option,
            choices: option.choices.map(choice => ({
                ...choice,
                price_delta: convertPriceToMinorUnits(choice.price_delta),
            })),
        })),
        // Convert tri-state string values to proper boolean/null
        commands: form.commands.map(cmd => ({
            ...cmd,
            is_player_online_required: cmd.is_player_online_required === "null" ? null : cmd.is_player_online_required === "true",
            is_repeat_per_quantity: cmd.is_repeat_per_quantity === "null" ? null : cmd.is_repeat_per_quantity === "true",
        })),
    };

    form.transform(() => payload).post(route("admin.store-package.update", props.storePackage.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Package #:id', { id: storePackage.id })" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updatePackage">
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

                <div class="col-span-6">
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
                    :help="__('Leave empty to keep current image')"
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
                    :label="__('Price')"
                    :help="__('Enter decimal amount (e.g., 9.99)')"
                    :error="form.errors.price"
                    type="number"
                    step="0.01"
                    name="price"
                    min="0"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="min_quantity"
                    v-model.number="form.min_quantity"
                    :label="__('Min Quantity')"
                    :error="form.errors.min_quantity"
                    type="number"
                    name="min_quantity"
                    min="1"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="max_quantity"
                    v-model.number="form.max_quantity"
                    :label="__('Max Quantity')"
                    :help="__('Leave empty for unlimited')"
                    :error="form.errors.max_quantity"
                    type="number"
                    name="max_quantity"
                    min="1"
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
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('Package is available for purchase when enabled')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_visible"
                    v-model="form.is_visible"
                    :label="__('Visible')"
                    :help="__('Package is visible to end users when enabled')"
                    name="is_visible"
                    :error="form.errors.is_visible"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="requires_login"
                    v-model="form.requires_login"
                    :label="__('Requires Login')"
                    :help="__('Only authenticated users can purchase this package')"
                    name="requires_login"
                    :error="form.errors.requires_login"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="stock_limit"
                    v-model.number="form.stock_limit"
                    :label="__('Stock Limit')"
                    :help="__('Leave empty for unlimited stock')"
                    :error="form.errors.stock_limit"
                    type="number"
                    name="stock_limit"
                    min="0"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="player_purchase_limit"
                    v-model.number="form.player_purchase_limit"
                    :label="__('Purchase Limit Per Player')"
                    :help="__('Leave empty for unlimited')"
                    :error="form.errors.player_purchase_limit"
                    type="number"
                    name="player_purchase_limit"
                    min="0"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="purchase_limit_period_days"
                    v-model.number="form.purchase_limit_period_days"
                    :label="__('Purchase Limit Period (Days)')"
                    :help="__('Resets every N days')"
                    :error="form.errors.purchase_limit_period_days"
                    type="number"
                    name="purchase_limit_period_days"
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
              </div>
            </div>
          </div>

          <!-- Delivery Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Delivery") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_run_on_all_servers"
                    v-model="form.is_run_on_all_servers"
                    :label="__('Run on All Servers')"
                    :help="__('When enabled, commands run on all servers')"
                    name="is_run_on_all_servers"
                    :error="form.errors.is_run_on_all_servers"
                  />
                </div>

                <div
                  v-if="!form.is_run_on_all_servers"
                  class="col-span-6"
                >
                  <legend class="text-sm font-medium text-foreground mb-2">
                    {{ __("Select Servers") }}
                  </legend>
                  <div class="space-y-2">
                    <div
                      v-for="server in servers"
                      :key="server.id"
                      class="flex items-center"
                    >
                      <XCheckbox
                        :id="`server_${server.id}`"
                        v-model="form.servers"
                        :model-value="form.servers"
                        :name="`servers[${server.id}]`"
                        type="checkbox"
                        :value="server.id"
                        :label="`${server.name} (${server.hostname})`"
                      />
                    </div>
                  </div>
                  <p
                    v-if="form.errors.servers"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.servers }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_player_online_required"
                    v-model="form.is_player_online_required"
                    :label="__('Player Must Be Online')"
                    :help="__('Player must be online for commands to execute')"
                    name="is_player_online_required"
                    :error="form.errors.is_player_online_required"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_command_repeated_per_quantity"
                    v-model="form.is_command_repeated_per_quantity"
                    :label="__('Repeat Commands Per Quantity')"
                    :help="__('Commands execute once for each quantity purchased')"
                    name="is_command_repeated_per_quantity"
                    :error="form.errors.is_command_repeated_per_quantity"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Commands Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Commands") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Available placeholders: {PLAYER_USERNAME}, {PLAYER_UUID}, {QUANTITY}, {PACKAGE_NAME}, {ORDER_UUID}") }}
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
                    <div class="grid grid-cols-12 gap-3 p-3 bg-muted/50 rounded-lg">
                      <div class="col-span-12 lg:col-span-1 flex gap-2 lg:mt-0 lg:flex-col">
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

                      <div class="col-span-6 lg:col-span-2">
                        <XSelect
                          v-model="command.trigger"
                          :label="__('Trigger')"
                          :select-list="triggerOptions"
                          :error="form.errors[`commands.${index}.trigger`]"
                          :disable-null="true"
                        />
                      </div>

                      <div class="col-span-6 lg:col-span-4">
                        <XInput
                          v-model="command.command"
                          :label="__('Command')"
                          :error="form.errors[`commands.${index}.command`]"
                          type="text"
                          name="command"
                        />
                      </div>

                      <div class="col-span-6 lg:col-span-2">
                        <XSelect
                          v-model="command.target"
                          :label="__('Target')"
                          :select-list="targetOptions"
                          :error="form.errors[`commands.${index}.target`]"
                          :disable-null="true"
                        />
                      </div>

                      <div class="col-span-6 lg:col-span-1">
                        <XInput
                          v-model.number="command.delay_seconds"
                          :label="__('Delay')"
                          :error="form.errors[`commands.${index}.delay_seconds`]"
                          type="number"
                          name="delay_seconds"
                          min="0"
                        />
                      </div>

                      <div class="col-span-6 lg:col-span-2">
                        <XSelect
                          v-model="command.is_player_online_required"
                          :label="__('Player Online')"
                          :placeholder="__('Inherit')"
                          :select-list="triStateOptions"
                          :error="form.errors[`commands.${index}.is_player_online_required`]"
                        />
                      </div>

                      <div class="col-span-6 lg:col-span-2">
                        <XSelect
                          v-model="command.is_repeat_per_quantity"
                          :label="__('Repeat Per Qty')"
                          :placeholder="__('Inherit')"
                          :select-list="triStateOptions"
                          :error="form.errors[`commands.${index}.is_repeat_per_quantity`]"
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

          <!-- Options Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Options") }}
              </h3>

              <div class="space-y-6">
                <Draggable
                  v-model="form.options"
                  :swap-threshold="0.65"
                  class="space-y-6"
                  handle=".drag-handle"
                >
                  <template #item="{ element: option, index: optionIndex }">
                    <div class="border border-border rounded-lg p-4 bg-muted/30">
                      <div class="flex gap-2 mb-4">
                        <div class="drag-handle cursor-move">
                          <ArrowsUpDownIcon class="w-5 h-5 text-muted-foreground hover:text-foreground" />
                        </div>
                        <button
                          type="button"
                          class="focus:outline-hidden group cursor-pointer ml-auto"
                          @click="removeOption(optionIndex)"
                        >
                          <TrashIcon class="w-5 h-5 text-muted-foreground group-hover:text-destructive" />
                        </button>
                      </div>

                      <div class="grid grid-cols-6 gap-4 mb-4">
                        <div class="col-span-6 sm:col-span-2">
                          <XInput
                            v-model="option.name"
                            :label="__('Option Name')"
                            :error="form.errors[`options.${optionIndex}.name`]"
                            type="text"
                            name="name"
                            required
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                          <XInput
                            v-model="option.placeholder"
                            :label="__('Placeholder (UPPER_SNAKE_CASE)')"
                            :help="__('Used in commands as {PLACEHOLDER}')"
                            :error="form.errors[`options.${optionIndex}.placeholder`]"
                            type="text"
                            name="placeholder"
                            required
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                          <div class="flex items-start pt-6">
                            <XCheckbox
                              :id="`option_required_${optionIndex}`"
                              v-model="option.is_required"
                              :label="__('Required')"
                              name="is_required"
                              :error="form.errors[`options.${optionIndex}.is_required`]"
                            />
                          </div>
                        </div>
                      </div>

                      <div class="mb-4">
                        <XTextarea
                          v-model="option.description"
                          :label="__('Description')"
                          :error="form.errors[`options.${optionIndex}.description`]"
                          name="description"
                        />
                      </div>

                      <!-- Choices Section -->
                      <div class="border-t border-border pt-4">
                        <h4 class="text-sm font-medium text-foreground mb-3">
                          {{ __("Choices") }}
                        </h4>

                        <div class="hidden lg:grid grid-cols-10 gap-2 mb-2">
                          <div class="col-span-1" />
                          <label class="col-span-2 block text-xs font-medium text-foreground">{{ __("Name") }}</label>
                          <label class="col-span-2 block text-xs font-medium text-foreground">{{ __("Value") }}</label>
                          <label class="col-span-2 block text-xs font-medium text-foreground">{{ __("Price Delta") }}</label>
                          <label class="col-span-2 block text-xs font-medium text-foreground">{{ __("Enabled") }}</label>
                          <label class="col-span-1 block text-xs font-medium text-foreground" />
                        </div>

                        <Draggable
                          v-model="option.choices"
                          :swap-threshold="0.65"
                          class="space-y-2"
                          handle=".drag-handle-choice"
                        >
                          <template #item="{ element: choice, index: choiceIndex }">
                            <div class="grid grid-cols-10 gap-2 p-2 bg-background rounded border border-border/50">
                              <div class="col-span-10 lg:col-span-1 flex gap-1">
                                <div class="drag-handle-choice cursor-move">
                                  <ArrowsUpDownIcon class="w-4 h-4 text-muted-foreground hover:text-foreground" />
                                </div>
                                <button
                                  type="button"
                                  class="focus:outline-hidden group cursor-pointer"
                                  @click="removeChoice(optionIndex, choiceIndex)"
                                >
                                  <TrashIcon class="w-4 h-4 text-muted-foreground group-hover:text-destructive" />
                                </button>
                              </div>

                              <div class="col-span-5 lg:col-span-2">
                                <XInput
                                  v-model="choice.name"
                                  :label="__('Name')"
                                  :error="form.errors[`options.${optionIndex}.choices.${choiceIndex}.name`]"
                                  type="text"
                                  name="name"
                                />
                              </div>

                              <div class="col-span-5 lg:col-span-2">
                                <XInput
                                  v-model="choice.value"
                                  :label="__('Value')"
                                  :error="form.errors[`options.${optionIndex}.choices.${choiceIndex}.value`]"
                                  type="text"
                                  name="value"
                                />
                              </div>

                              <div class="col-span-5 lg:col-span-2">
                                <XInput
                                  v-model="choice.price_delta"
                                  :label="__('Price Delta')"
                                  :help="__('Decimal amount')"
                                  :error="form.errors[`options.${optionIndex}.choices.${choiceIndex}.price_delta`]"
                                  type="number"
                                  step="0.01"
                                  name="price_delta"
                                />
                              </div>

                              <div class="col-span-5 lg:col-span-2 flex items-start pt-6">
                                <XCheckbox
                                  :id="`choice_enabled_${optionIndex}_${choiceIndex}`"
                                  v-model="choice.is_enabled"
                                  :label="__('Enabled')"
                                  name="is_enabled"
                                  :error="form.errors[`options.${optionIndex}.choices.${choiceIndex}.is_enabled`]"
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
                            @click="addChoice(optionIndex)"
                          >
                            {{ __("Add Choice") }}
                          </Button>
                        </div>
                      </div>
                    </div>
                  </template>
                </Draggable>

                <div class="flex justify-end mt-4">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addOption"
                  >
                    {{ __("Add Option") }}
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
              <Link :href="route('admin.store-package.index')">
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
              {{ __("Update Package") }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
