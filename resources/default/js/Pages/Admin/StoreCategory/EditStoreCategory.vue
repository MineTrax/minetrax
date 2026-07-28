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
import Draggable from "vuedraggable";
import { ArrowsUpDownIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { computed, ref } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    category: Object,
    parentCategories: Array,
    displayTypes: Array,
    comparisonFieldTypes: Array,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Categories"),
        url: route("admin.store.category.index"),
        current: false,
    },
    {
        text: __("Edit Category"),
        current: false,
    },
    {
        text: "#" + props.category.id,
        current: true,
    }
];

const photoInput = ref(null);

const parentCategoriesMap = props.parentCategories.reduce((acc, cat) => {
    acc[cat.id] = cat.name;
    return acc;
}, {});

const form = useForm({
    _method: "PUT",
    name: props.category.name,
    description: props.category.description,
    parent_id: props.category.parent_id,
    sort_order: props.category.sort_order,
    is_visible: props.category.is_visible,
    is_enabled: props.category.is_enabled,
    display_type: props.category.display_type?.value ?? props.category.display_type,
    comparison_fields: props.category.comparison_fields || [],
    is_cumulative: !! props.category.is_cumulative,
    photo: null,
});

const displayTypeCopy = {
    grid: {
        label: __("Grid Mode"),
        help: __("Cards in a grid. The default, and the right choice for a lot of packages."),
    },
    comparison: {
        label: __("Comparison Mode"),
        help: __("A table comparing each package's features. Best for a handful of packages."),
    },
    listing: {
        label: __("Listing Mode"),
        help: __("A vertical list, one row per package. Good for a middling number."),
    },
    stacked: {
        label: __("Stacked Mode"),
        help: __("A list with the quantity front and centre. Perfect for bulk items."),
    },
};

const usesComparisonFields = computed(
    () => props.displayTypes.find(type => type.value === form.display_type)?.uses_comparison_fields ?? false
);

const comparisonTypeOptions = props.comparisonFieldTypes.reduce((acc, type) => ({
    ...acc,
    [type.value]: type.value === "check" ? __("Arrow Checks") : __("Custom Text (Supports HTML)"),
}), {});

// Generated once, when the row is added, and round-tripped from then on. Renaming a field must not
// orphan the values every package already holds against it.
function comparisonFieldKey(existingKeys) {
    let index = existingKeys.length + 1;
    let key = `field_${index}`;

    while (existingKeys.includes(key)) {
        key = `field_${++index}`;
    }
    return key;
}

function addComparisonField() {
    form.comparison_fields.push({
        key: comparisonFieldKey(form.comparison_fields.map(field => field.key)),
        name: "",
        description: null,
        type: "text",
    });
}

function removeComparisonField(index) {
    form.comparison_fields.splice(index, 1);
}

function updateCategory() {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    // POST with _method spoofing rather than put(): the photo makes this multipart, which PUT
    // cannot carry through PHP.
    form.post(route("admin.store.category.update", props.category.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Category #:id', { id: category.id })" />

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

                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="photo"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Photo") }}</label>
                  <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    accept="image/*"
                    class="block p-2 w-full text-sm text-foreground border border-input rounded-lg cursor-pointer bg-background focus:outline-hidden"
                  >
                  <p class="text-xs text-muted-foreground mt-2">
                    {{ __("Leave empty to keep current image") }}
                  </p>
                  <p
                    v-if="form.errors.photo"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.photo }}
                  </p>
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

                <!-- Display type -->
                <div class="col-span-6 border-t border-border pt-6">
                  <h3 class="text-lg font-medium text-foreground text-center mb-4">
                    {{ __("Category Display Type") }}
                  </h3>
                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button
                      v-for="type in displayTypes"
                      :key="type.value"
                      type="button"
                      class="text-center p-4 rounded-lg border transition-colors"
                      :class="form.display_type === type.value
                        ? 'border-primary bg-primary/5'
                        : 'border-border hover:border-muted-foreground'"
                      @click="form.display_type = type.value"
                    >
                      <span class="block font-medium text-foreground mb-1">
                        {{ displayTypeCopy[type.value]?.label ?? type.value }}
                      </span>
                      <span class="block text-xs text-muted-foreground">
                        {{ displayTypeCopy[type.value]?.help }}
                      </span>
                      <span
                        class="inline-block mt-3 w-4 h-4 rounded-full border-2"
                        :class="form.display_type === type.value
                          ? 'border-primary bg-primary'
                          : 'border-muted-foreground'"
                      />
                    </button>
                  </div>
                  <p
                    v-if="form.errors.display_type"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.display_type }}
                  </p>
                </div>

                <!-- Comparison fields -->
                <div
                  v-if="usesComparisonFields"
                  class="col-span-6 border-t border-border pt-6"
                >
                  <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                    <h3 class="text-lg font-medium text-foreground">
                      {{ __("Comparison Category Options") }}
                    </h3>
                    <Button
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="addComparisonField"
                    >
                      {{ __("Add Comparison Field") }}
                    </Button>
                  </div>

                  <p
                    v-if="form.comparison_fields.length === 0"
                    class="text-sm text-muted-foreground"
                  >
                    {{ __("Add at least one field, or this category falls back to the grid layout.") }}
                  </p>

                  <Draggable
                    v-model="form.comparison_fields"
                    :swap-threshold="0.65"
                    class="space-y-3"
                    handle=".drag-handle"
                  >
                    <template #item="{ element: field, index }">
                      <div class="p-4 bg-muted/50 rounded-lg grid grid-cols-12 gap-3">
                        <div class="col-span-12 lg:col-span-1 flex gap-2 lg:mt-6 lg:flex-col">
                          <div class="drag-handle cursor-move">
                            <ArrowsUpDownIcon class="w-5 h-5 text-muted-foreground hover:text-foreground" />
                          </div>
                          <button
                            type="button"
                            class="focus:outline-hidden group cursor-pointer"
                            @click="removeComparisonField(index)"
                          >
                            <TrashIcon class="w-5 h-5 text-muted-foreground group-hover:text-destructive" />
                          </button>
                        </div>

                        <div class="col-span-12 sm:col-span-4 lg:col-span-4">
                          <XInput
                            v-model="field.name"
                            :label="__('Comparison Name')"
                            :error="form.errors[`comparison_fields.${index}.name`]"
                            type="text"
                            name="comparison_name"
                          />
                        </div>

                        <div class="col-span-12 sm:col-span-4 lg:col-span-4">
                          <XInput
                            v-model="field.description"
                            :label="__('Comparison Description')"
                            :error="form.errors[`comparison_fields.${index}.description`]"
                            type="text"
                            name="comparison_description"
                          />
                        </div>

                        <div class="col-span-12 sm:col-span-4 lg:col-span-3">
                          <XSelect
                            v-model="field.type"
                            :label="__('Comparison Type')"
                            :select-list="comparisonTypeOptions"
                            :error="form.errors[`comparison_fields.${index}.type`]"
                            :disable-null="true"
                          />
                        </div>
                      </div>
                    </template>
                  </Draggable>

                  <p class="text-xs text-muted-foreground mt-3">
                    {{ __("Each package in this category gets a cell for every field, filled in on the package's own form.") }}
                  </p>
                </div>

                <!-- Advanced -->
                <div class="col-span-6 border-t border-border pt-6">
                  <h3 class="text-lg font-medium text-foreground text-center mb-4">
                    {{ __("Advanced Settings") }}
                  </h3>
                  <div class="p-4 border border-border rounded-lg">
                    <XSwitch
                      id="is_cumulative"
                      v-model="form.is_cumulative"
                      :label="__('Cumulate the purchases inside of this category so customers only pay the difference when purchasing a higher priced package.')"
                      :help="__('Credits the price of the cheaper package the customer already owns here against a dearer one. Never credits a downgrade, and never more than the new package costs.')"
                      name="is_cumulative"
                      :error="form.errors.is_cumulative"
                    />
                  </div>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store.category.index')">
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
