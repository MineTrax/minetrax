<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import { useFormKit } from "@/Composables/useFormKit";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import { FormKitSchema } from "@formkit/vue";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import TipTapEditor from "@/Components/TipTapEditor.vue";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    variableTypes: Array,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Variables"),
        url: route("admin.store.variable.index"),
        current: false,
    },
    {
        text: __("New Variable"),
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

const typeOptions = props.variableTypes.reduce(
    (acc, type) => ({ ...acc, [type.value]: typeLabels[type.value] ?? type.value }),
    {}
);

const form = useForm({
    name: null,
    identifier: null,
    description: "",
    type: "text",
    options: null,
    placeholder: null,
    is_required: true,
    max_length: 32,
    is_enabled: true,
    sort_order: 0,
});

const selectedType = computed(() => props.variableTypes.find(type => type.value === form.type));
const hasOptions = computed(() => selectedType.value?.has_options ?? false);
const isFreeText = computed(() => selectedType.value?.is_free_text ?? false);

// What an admin actually types into a command. Worth showing, because it is not the bare
// identifier: the VARIABLE_ prefix keeps it out of the built-in placeholders' namespace.
const commandPlaceholder = computed(() => {
    const identifier = (form.identifier || "").trim().toLowerCase().replace(/[^a-z0-9]+/g, "_");
    return identifier ? `{VARIABLE_${identifier.toUpperCase()}}` : null;
});

// The same schema builder the storefront and the custom forms use, so this preview is the input
// the customer will really see rather than an approximation of it.
const previewSchema = computed(() => useFormKit().generateSchemaFromFieldsArray([{
    type: form.type,
    label: form.name || __("Untitled variable"),
    name: "preview",
    placeholder: form.placeholder,
    help: form.description ? form.description.replace(/<[^>]*>/g, "") : null,
    options: hasOptions.value ? form.options : null,
}]));

function createVariable() {
    form.post(route("admin.store.variable.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Store Variable')" />

    <div class="px-10 py-8 mx-auto max-w-5xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createVariable">
          <!-- Details -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Name For Variable')"
                    :help="__('Shown to the customer as the field label, e.g. Prefix Color')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    required
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
                  <p class="text-xs text-muted-foreground mt-2">
                    {{ __("Shown as help text under the field.") }}
                  </p>
                  <p
                    v-if="form.errors.description"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.description }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="identifier"
                    class="block text-sm font-medium text-foreground mb-2"
                  >
                    {{ __("Variable Identifier") }}
                    <span class="text-destructive ml-1">*</span>
                  </label>
                  <div class="flex items-center">
                    <span class="px-3 h-9 flex items-center rounded-l-md border border-input border-r-0 bg-muted text-muted-foreground text-sm">{</span>
                    <input
                      id="identifier"
                      v-model="form.identifier"
                      type="text"
                      name="identifier"
                      :placeholder="__('prefix_color')"
                      class="flex h-9 w-full border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring"
                    >
                    <span class="px-3 h-9 flex items-center rounded-r-md border border-input border-l-0 bg-muted text-muted-foreground text-sm">}</span>
                  </div>
                  <p
                    v-if="commandPlaceholder"
                    class="text-xs text-muted-foreground mt-2"
                  >
                    {{ __("Use this in the package's commands:") }}
                    <code class="px-1 py-0.5 rounded bg-muted text-foreground font-mono select-all">{{ commandPlaceholder }}</code>
                  </p>
                  <p
                    v-if="form.errors.identifier"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.identifier }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="type"
                    v-model="form.type"
                    name="type"
                    :label="__('Variable Type')"
                    :select-list="typeOptions"
                    :error="form.errors.type"
                    :disable-null="true"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Field Settings -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Field Settings") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div
                  v-if="hasOptions"
                  class="col-span-6"
                >
                  <XInput
                    id="options"
                    v-model="form.options"
                    :label="__('Choices')"
                    :help="__('Separate each choice with a comma, e.g. Red,Green,Blue')"
                    :error="form.errors.options"
                    type="text"
                    name="options"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="placeholder"
                    v-model="form.placeholder"
                    :label="__('Placeholder')"
                    :help="__('Faint hint text inside the empty field')"
                    :error="form.errors.placeholder"
                    type="text"
                    name="placeholder"
                  />
                </div>

                <div
                  v-if="isFreeText"
                  class="col-span-6 sm:col-span-2"
                >
                  <XInput
                    id="max_length"
                    v-model.number="form.max_length"
                    :label="__('Maximum Length')"
                    :help="__('Leave empty for the 255 character limit')"
                    :error="form.errors.max_length"
                    type="number"
                    name="max_length"
                    min="1"
                    max="255"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="sort_order"
                    v-model.number="form.sort_order"
                    :label="__('Sort Order')"
                    :error="form.errors.sort_order"
                    type="number"
                    name="sort_order"
                  />
                </div>

                <div class="col-span-6 grid grid-cols-1 lg:grid-cols-2 gap-4 border-t border-border pt-4">
                  <XSwitch
                    id="is_required"
                    v-model="form.is_required"
                    :label="__('Require the customer to fill this in?')"
                    :help="__('With this off the customer may leave it blank, and the placeholder resolves to nothing.')"
                    name="is_required"
                    :error="form.errors.is_required"
                  />

                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enable this variable?')"
                    :help="__('A disabled variable is not asked for, even on packages it is attached to.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="shadow overflow-hidden rounded-lg mb-6">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Preview") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Exactly what the customer sees on the package page.") }}
              </p>
              <FormKit
                type="form"
                :actions="false"
              >
                <FormKitSchema :schema="previewSchema" />
              </FormKit>
            </div>
          </div>

          <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2 rounded-b-lg">
            <Button
              variant="outline"
              as-child
            >
              <Link :href="route('admin.store.variable.index')">
                {{ __("Cancel") }}
              </Link>
            </Button>
            <Button
              type="submit"
              :disabled="form.processing"
            >
              {{ __("Create a Variable") }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
