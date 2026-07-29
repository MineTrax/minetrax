<script setup>
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import XInput from "@/Components/Form/XInput.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { EyeIcon, NoSymbolIcon, ClockIcon } from "@heroicons/vue/24/outline";

const { __ } = useTranslations();

defineProps({
    grants: Object,
    filters: Object,
    statuses: Array,
    permissions: Object,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Package Grants"), current: true },
];

const headerRow = [
    {
        key: "player_username",
        sortable: false,
        label: __("Player"),
        filterable: { type: "text" },
    },
    {
        key: "package",
        sortable: false,
        label: __("Package"),
    },
    {
        key: "status",
        sortable: true,
        label: __("Status"),
    },
    {
        key: "granted_at",
        sortable: true,
        label: __("Granted"),
    },
    {
        key: "expires_at",
        sortable: true,
        label: __("Expires"),
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-2/12 text-right",
    },
];

const extendTarget = ref(null);

const extendForm = useForm({
    days: 30,
});

function expiryLabel(grant) {
    if (! grant.expires_at) {
        return __("Never");
    }
    return new Date(grant.expires_at).toLocaleString();
}

function revoke(grant) {
    router.post(route("admin.store.grant.revoke", grant.id), {}, { preserveScroll: true });
}

function openExtend(grant) {
    extendTarget.value = grant;
    extendForm.days = 30;
    extendForm.clearErrors();
}

function submitExtend() {
    extendForm.post(route("admin.store.grant.extend", extendTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            extendTarget.value = null;
        },
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Package Grants Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A grant is what a paid order actually gave a player. Timed grants expire on their own every few minutes; revoking one here runs the package's expiry commands to take the perk back in game.") }}
      </p>

      <!-- Extend form -->
      <div
        v-if="extendTarget"
        class="bg-card rounded-lg shadow p-6 mb-6"
      >
        <h3 class="text-sm font-medium mb-1">
          {{ __("Extend Expiry") }}
        </h3>
        <p class="text-xs text-muted-foreground mb-4">
          {{ __(":package for :player currently expires :date.", {
            package: extendTarget.package?.name ?? extendTarget.order_item?.package_name,
            player: extendTarget.order_item?.order?.player_username,
            date: expiryLabel(extendTarget),
          }) }}
        </p>

        <form
          class="space-y-4"
          @submit.prevent="submitExtend"
        >
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <XInput
              id="days"
              v-model="extendForm.days"
              :label="__('Add Days')"
              :help="__('Added to the current expiry, not to today.')"
              :error="extendForm.errors.days"
              type="number"
              name="days"
              min="1"
            />
          </div>

          <div class="flex justify-end gap-2">
            <Button
              type="button"
              variant="outline"
              @click="extendTarget = null"
            >
              {{ __("Cancel") }}
            </Button>
            <Button
              type="submit"
              :disabled="extendForm.processing"
            >
              {{ __("Extend Grant") }}
            </Button>
          </div>
        </form>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="grants"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem>
            <div class="font-medium text-foreground">
              {{ item.order_item?.order?.player_username ?? "—" }}
            </div>
            <div class="text-xs text-muted-foreground font-mono">
              {{ item.player_uuid }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <div class="font-medium text-foreground">
              {{ item.package?.name ?? item.order_item?.package_name ?? "—" }}
            </div>
            <div
              v-if="!item.package"
              class="text-xs text-muted-foreground"
            >
              {{ __("Package deleted") }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <CommonStatusBadge :status="item.status.value" />
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs text-muted-foreground">{{ new Date(item.granted_at).toLocaleString() }}</span>
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs text-muted-foreground">{{ expiryLabel(item) }}</span>
          </DtRowItem>

          <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
            <ButtonGroup>
              <Button
                v-if="item.order_item?.order?.uuid"
                variant="outline"
                size="icon"
                as-child
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.order.show', item.order_item.order.uuid)"
                  :title="__('View Order')"
                >
                  <EyeIcon />
                </Link>
              </Button>
              <Button
                v-if="permissions.update && item.status.value === 'active' && item.expires_at"
                v-tippy
                variant="outline"
                size="icon"
                :title="__('Extend Expiry')"
                @click="openExtend(item)"
              >
                <ClockIcon />
              </Button>
              <Button
                v-if="permissions.update && item.status.value === 'active'"
                v-tippy
                v-confirm="{
                  message: __('Revoke this grant? The package\'s expiry commands run, so the perk is removed in game.'),
                }"
                variant="outline"
                size="icon"
                class="text-destructive hover:text-destructive"
                :title="__('Revoke Grant')"
                @click="revoke(item)"
              >
                <NoSymbolIcon />
              </Button>
            </ButtonGroup>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
