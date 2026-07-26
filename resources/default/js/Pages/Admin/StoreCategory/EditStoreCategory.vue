<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import XTextarea from "@/Components/Form/XTextarea.vue";

const { __ } = useTranslations();

const props = defineProps({
    category: Object,
    parentCategories: Array,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Categories"),
        url: route("admin.store-category.index"),
        current: false,
    },
    {
        text: __("Edit Category"),
        current: true,
    },
    {
        text: props.category.name,
        current: true,
    }
];

const parentCategoriesMap = props.parentCategories.reduce((acc, cat) => {
    acc[cat.id] = cat.name;
    return acc;
}, {});

const form = useForm({
    name: props.category.name,
    description: props.category.description,
    parent_id: props.category.parent_id,
    sort_order: props.category.sort_order,
    is_visible: props.category.is_visible,
    is_enabled: props.category.is_enabled,
    "_method": "PUT",
});

function updateCategory() {
    form.post(route("admin.store-category.update", props.category.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Category')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateCategory">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-6">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Category Name')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <XTextarea
                    id="description"
                    v-model="form.description"
                    :label="__('Description')"
                    :error="form.errors.description"
                    :placeholder="__('Enter category description')"
                    name="description"
                    :rows="4"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="parent_id"
                    v-model="form.parent_id"
                    :label="__('Parent Category')"
                    :error="form.errors.parent_id"
                    :select-list="parentCategoriesMap"
                    :placeholder="__('None')"
                    name="parent_id"
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

                <div class="col-span-6 sm:col-span-6">
                  <label
                    for="photo"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Photo") }}</label>
                  <span class="block p-2 w-full text-sm text-muted-foreground border border-input cursor-default rounded-lg bg-muted">
                    {{ category.media?.file_name ?? __("No photo") }}
                  </span>
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_visible"
                    v-model="form.is_visible"
                    :label="__('Visible')"
                    :help="__('Make this category visible to users.')"
                    name="is_visible"
                    :error="form.errors.is_visible"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('Enable this category for use.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store-category.index')">
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
                {{ __("Update Category") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
