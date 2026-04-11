<script setup>
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import Icon from "@/Components/Icon.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import {
    ChartBarSquareIcon,
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";
import { Link } from "@inertiajs/vue3";
import { startCase } from "lodash";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    customForms: Object,
    filters: Object,
});

function getStatusColor(status) {
    switch (status) {
    case"active":
        return"green";
    case"draft":
        return"pending";
    case"disabled":
        return"red";
    case"archived":
        return"deferred";
    default:
        return status;
    }
}

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Custom Forms"),
        current: true,
    }
];

const headerRow = [
    {
        key:"id",
        label: __("ID"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"title",
        label: __("Title"),
        sortable: true,
        filterable: {
            type:"text",
        }
    },
    {
        key:"status",
        label: __("Status"),
        sortable: true,
        filterable: {
            type:"multiselect",
            options: ["draft","active","disabled","archived"],
        }
    },
    {
        key:"can_create_submission",
        label: __("Can Submit"),
        sortable: true,
        class:"whitespace-nowrap",
    },
    {
        key:"is_notify_staff_on_submission",
        sortable: true,
        label: __("Notify Staff on Submit"),
        class:"whitespace-nowrap",
    },
    {
        key:"is_visible_in_listing",
        sortable: true,
        label: __("Visible in Listing"),
        class:"whitespace-nowrap",
    },
    {
        key:"submissions_count",
        sortable: true,
        label: __("Total Submissions"),
        class:"text-right whitespace-nowrap",
    },
    {
        key:"created_at",
        sortable: true,
        label: __("Created"),
    },
    {
        key:"actions",
        label: __("Actions"),
        sortable: false,
        class:"w-1/12 text-right",
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Manage Custom Forms')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create custom_forms')"
            as-child
          >
            <Link :href="route('admin.custom-form.create')">
              {{ __("Create Custom Form") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="customForms"
        :filters="filters"
        :row-href="(item) => route('admin.custom-form.show', item.id)"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>

          <DtRowItem>
            {{ item.title }}
          </DtRowItem>

          <DtRowItem>
            <CommonStatusBadge
              :status="getStatusColor(item.status.value)"
              :value="startCase(item.status.value)"
            />
          </DtRowItem>

          <td class="px-4 whitespace-normal">
            <div class="flex items-center">
              <div
                class="text-sm font-medium text-foreground dark:text-foreground"
              >
                {{ item.can_create_submission }}
              </div>
            </div>
          </td>

          <td
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_notify_staff_on_submission"
              class="text-success focus:outline-hidden"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>

          <td
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_visible_in_listing"
              class="text-success focus:outline-hidden"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>

          <DtRowItem class="text-right">
            {{ item.submissions_count }}
          </DtRowItem>

          <DtRowItem class="whitespace-nowrap">
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>

          <td
            class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
          >
            <ButtonGroup>
              <Button
                v-if="['active','disabled'].includes(item.status.value)"
                variant="outline"
                size="icon"
                as-child
                class="text-primary hover:text-primary"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('custom-form.show', item.slug)"
                  :title="__('Show Public View')"
                >
                  <EyeIcon />
                </Link>
              </Button>
              <Button
                variant="outline"
                size="icon"
                as-child
                class="text-success hover:text-success"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.custom-form.show', item.id)"
                  :title="__('Show Intel')"
                >
                  <ChartBarSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('update custom_forms')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.custom-form.edit', item.id)"
                  :title="__('Edit Custom Form')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete custom_forms')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message:
                      'Deleting this Custom Form will also delete all its submissions. Are you sure you want to delete this form & its submissions permanently?',
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.custom-form.delete', item.id)"
                  :title="__('Delete Custom Form')"
                >
                  <TrashIcon />
                </Link>
              </Button>
            </ButtonGroup>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
