<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import Multiselect from "vue-multiselect";
import TipTapEditor from "@/Components/TipTapEditor.vue";
import ImageUpload from "@/Components/Form/ImageUpload.vue";
import Draggable from "vuedraggable";
import { ArrowsUpDownIcon, TrashIcon } from "@heroicons/vue/24/outline";

const { __ } = useTranslations();

const props = defineProps({
    categories: Array,
    servers: Array,
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

const serverLabel = (server) => `${server.name} (${server.hostname})`;

const categoriesOptions = props.categories.reduce((acc, cat) => {
    return { ...acc, [cat.id]: cat.name };
}, {});

const form = useForm({
    name: null,
    store_category_id: null,
    short_description: null,
    description: "",
    price: null,
    sort_order: 0,
    is_visible: true,
    is_enabled: true,
    requires_login: false,
    min_quantity: 1,
    max_quantity: null,
    stock_limit: null,
    player_purchase_limit: null,
    purchase_limit_period_days: null,
    expiry_duration_days: null,
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

function convertPriceToMinorUnits(decimalPrice) {
    if (decimalPrice === null || decimalPrice === undefined || decimalPrice === "") {
        return null;
    }
    return Math.round(parseFloat(decimalPrice) * minorUnitFactor);
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
    // Convert price from decimal to minor units
    const payload = {
        ...form.data(),
        price: convertPriceToMinorUnits(form.price),
        // Multiselect hands back whole server objects; the API takes ids.
        commands: form.commands.map(cmd => ({
            ...cmd,
            servers: (cmd.servers ?? []).map(server => ({ id: server.id })),
        })),
    };

    form.transform(() => payload).post(route("admin.store.package.store"), {});
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
