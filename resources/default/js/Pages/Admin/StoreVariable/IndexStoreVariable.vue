<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useTranslations } from "@/Composables/useTranslations";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { Link } from "@inertiajs/vue3";
import { PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import Icon from "@/Components/Icon.vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();

defineProps({
    variables: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Variables"),
        current: true,
    }
];

const typeLabels = {
    text: __("Input Text Form"),
    textarea: __("Multi-line Text"),
    number: __("Number"),
    select: __("Dropdown"),
    radio: __("Radio Buttons"),
    checkbox: __("Checkbox"),
};

const headerRow = [
    {
        key: "name",
        sortable: true,
        label: __("Variable"),
        filterable: {
            type: "text",
        },
    },
    {
        key: "identifier",
        sortable: true,
        label: __("Placeholder"),
    },
    {
        key: "type",
        sortable: true,
        label: __("Type"),
    },
    {
        key: "packages_count",
        sortable: true,
        class: "text-center",
        label: __("Packages"),
    },
    {
        key: "is_required",
        label: __("Required"),
        sortable: true,
    },
    {
        key: "is_enabled",
        label: __("Enabled"),
        sortable: true,
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Variables Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_variables')"
            as-child
          >
            <Link :href="route('admin.store.variable.create')">
              {{ __("Create Variable") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A variable is a field the customer fills in while ordering. Attach it to a package, then use its placeholder in that package's commands.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="variables"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem>
            <div class="font-medium text-foreground">
              {{ item.name }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <code class="px-1.5 py-0.5 rounded bg-muted text-xs font-mono select-all">
              {{ item.command_placeholder }}
            </code>
          </DtRowItem>

          <DtRowItem>
            {{ item.type?.value ? typeLabels[item.type.value] : "—" }}
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.packages_count }}
          </DtRowItem>

          <td class="px-4">
            <Icon
              v-if="item.is_required"
              class="text-success"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-muted-foreground"
              name="cross-circle"
            />
          </td>

          <td class="px-4">
            <Icon
              v-if="item.is_enabled"
              class="text-success"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>

          <td
            class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
          >
            <ButtonGroup>
              <Button
                v-if="can('update store_variables')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.variable.edit', item.id)"
                  :title="__('Edit Variable')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete store_variables')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Are you sure you want to delete this Store Variable permanently? Packages using it will stop asking for it.'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.variable.delete', item.id)"
                  :title="__('Delete Variable')"
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
