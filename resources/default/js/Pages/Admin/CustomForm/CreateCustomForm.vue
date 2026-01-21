<script setup>
import { FormKitSchema } from '@formkit/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import { computed, ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { useFormKit } from '@/Composables/useFormKit';
import { kebabCase } from 'lodash';
import Draggable from 'vuedraggable';
import { ArrowsUpDownIcon, TrashIcon } from '@heroicons/vue/24/outline';
import TipTapEditor from '@/Components/TipTapEditor.vue';

const { __ } = useTranslations();

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Custom Forms'),
        url: route('admin.custom-form.index'),
        current: false,
    },
    {
        text: __('Create Custom Form'),
        current: true,
    }
];

const formStatusList = {
    draft: 'Draft - Form is under development and not visible to users',
    active: 'Active - Form is actively accepting submissions',
    disabled: 'Disabled - Form is disabled for new submissions',
    archived: 'Archived - Form is archived and not visible to users',
};

const canCreateSubmissionList = {
    anyone: 'Anyone - Anyone including Guest can submit this form',
    auth: 'Auth Only - Only registered users can submit this form',
    staff: 'Staff Only - Only staff members (is_staff) can submit this form',
};

const formFieldType = {
    text: {},
    textarea: {},
    select: {
        hasOptions: true,
    },
    multiselect: {
        hasOptions: true,
    },
    radio: {
        hasOptions: true,
    },
    checkbox: {},
    email: {},
    number: {},
    password: {},
    tel: {},
    url: {},
    week: {},
    month: {},
    time: {},
    date: {},
    'datetime-local': {},
};

const form = useForm({
    title: '',
    slug: '',
    status: 'draft',
    description: '',
    can_create_submission: 'anyone',
    max_submission_per_user: null,
    min_role_weight_to_view_submission: null,
    is_notify_staff_on_submission: true,
    is_visible_in_listing: true,
    fields: [
        {
            type: 'text',
            label: 'Name',
            name: 'name',
            placeholder: null,
            help: null,
            validation: 'required|length:3,100',
            options: null,
        },
        {
            type: 'select',
            label: 'Type',
            name: 'select',
            placeholder: null,
            help: null,
            validation: 'required',
            options: 'Type1,Type2,Type3',
        },
    ],
});

const createCustomForm = () => {
    form.fields.map(item => {
        item.name = item.label.toLowerCase().replace(/ /g, '_');
    });

    if (form.can_create_submission === 'anyone') {
        form.max_submission_per_user = null;
    }

    form.post(route('admin.custom-form.store'), {});
};

function addField() {
    form.fields.push({
        type: 'text',
        label: '',
        name: '',
        validation: 'required',
    });
}

function removeField(index) {
    if (form.fields.length === 1) {
        return;
    }
    form.fields.splice(index, 1);
}

const showingFormPreview = ref(false);

const computedFormSchema = computed(() => {
    return useFormKit().generateSchemaFromFieldsArray(form.fields);
});

watch(() => form.title, (value) => {
    form.slug = kebabCase(value);
});
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Custom Form')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createCustomForm">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <XInput
                    id="title"
                    v-model="form.title"
                    :label="__('Title of Custom Form')"
                    :help="__('Eg: Contact Us')"
                    :error="form.errors.title"
                    type="text"
                    name="title"
                  />
                </div>

                <div class="col-span-6">
                  <XInput
                    id="slug"
                    v-model="form.slug"
                    :label="__('Form Slug')"
                    :help="__('Only alphabet, number and dashes. Eg: contact-us')"
                    :error="form.errors.slug"
                    type="text"
                    name="slug"
                  />
                </div>

                <div class="col-span-6">
                  <XSelect
                    id="status"
                    v-model="form.status"
                    name="status"
                    :label="__('Form Status')"
                    :placeholder="__('Select a status of form..')"
                    :disable-null="true"
                    :select-list="formStatusList"
                  />
                </div>

                <div class="col-span-6">
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

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="can_create_submission"
                    v-model="form.can_create_submission"
                    name="can_create_submission"
                    :label="__('Permission to Create Submission')"
                    :placeholder="__('Select a who can create submittion for this form..')"
                    :disable-null="true"
                    :select-list="canCreateSubmissionList"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    v-if="form.can_create_submission !== 'anyone'"
                    id="max_submission_per_user"
                    v-model="form.max_submission_per_user"
                    :label="__('Max Submission Per User')"
                    :help="__('Leave empty to allow unlimited submission per user.')"
                    :error="form.errors.max_submission_per_user"
                    type="number"
                    name="max_submission_per_user"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="min_role_weight_to_view_submission"
                    v-model="form.min_role_weight_to_view_submission"
                    :label="__('Min Staff Role Weight to View Submission')"
                    :help="__('Leave empty to allow any staff with view custom_form_submissions permission to view submissions.')"
                    :error="form.errors.min_role_weight_to_view_submission"
                    type="number"
                    name="min_role_weight_to_view_submission"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_notify_staff_on_submission"
                    v-model="form.is_notify_staff_on_submission"
                    :label="__('Notify Staff on Submission')"
                    :help="__('Notify staff members (with view submission permission) when new submission is made for this form.')"
                    name="is_notify_staff_on_submission"
                    :error="form.errors.is_notify_staff_on_submission"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_visible_in_listing"
                    v-model="form.is_visible_in_listing"
                    :label="__('Is Visible in Listing')"
                    :help="__('Allow this form to be listed in custom form listing page.')"
                    name="is_visible_in_listing"
                    :error="form.errors.is_visible_in_listing"
                  />
                </div>

                <div class="col-span-6 space-y-4">
                  <legend class="text-base font-medium text-foreground">
                    {{ __("Fields") }}
                  </legend>

                  <div class="w-full space-y-2">
                    <div class="hidden lg:flex space-x-4">
                      <div class="w-5" />
                      <div class="w-5" />
                      <label class="flex-1 block text-sm font-medium text-foreground">
                        {{ __("Name") }}
                        <span class="text-destructive">*</span>
                      </label>
                      <label class="flex-1 block text-sm font-medium text-foreground">
                        {{ __("Type") }}
                        <span class="text-destructive">*</span>
                      </label>
                      <label class="flex-1 block text-sm font-medium text-foreground">
                        {{ __("Validation") }}
                      </label>
                      <label class="flex-1 block text-sm font-medium text-foreground">
                        {{ __("Help Text") }}
                      </label>
                      <label class="flex-1 block text-sm font-medium text-foreground">
                        {{ __("Options") }}
                        <span class="text-destructive">*</span>
                        <span class="text-xs text-muted-foreground">(Eg: Option1,Option2)</span>
                      </label>
                    </div>

                    <Draggable
                      v-model="form.fields"
                      :swap-threshold="0.65"
                      class="space-y-3"
                      handle=".drag-handle"
                    >
                      <template #item="{ element: field, index }">
                        <div class="flex flex-col lg:flex-row gap-4 items-start p-3 lg:p-0 bg-muted/50 lg:bg-transparent rounded-lg">
                          <div class="flex gap-2 lg:mt-6">
                            <div class="drag-handle cursor-move">
                              <ArrowsUpDownIcon
                                class="w-5 h-5 text-muted-foreground hover:text-foreground"
                              />
                            </div>
                            <button
                              type="button"
                              class="focus:outline-none group"
                              @click="removeField(index)"
                            >
                              <TrashIcon
                                class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                              />
                            </button>
                          </div>
                          <div class="flex-1 w-full lg:w-auto">
                            <XInput
                              v-model="field.label"
                              :label="__('Name Field :index', { index: index + 1 })"
                              :error="form.errors[`fields.${index}.label`] || form.errors[`fields.${index}.name`]"
                              type="text"
                              :required="true"
                            />
                          </div>
                          <div class="flex-1 w-full lg:w-auto">
                            <XSelect
                              v-model="field.type"
                              :label="__('Field Type')"
                              :error="form.errors[`fields.${index}.type`]"
                              :select-list="Object.keys(formFieldType)"
                              :required="true"
                            />
                          </div>
                          <div class="flex-1 w-full lg:w-auto">
                            <XInput
                              v-model="field.validation"
                              :label="__('Validation Field :index', { index: index + 1 })"
                              :error="form.errors[`fields.${index}.validation`]"
                              type="text"
                            />
                          </div>
                          <div class="flex-1 w-full lg:w-auto">
                            <XInput
                              v-model="field.help"
                              :label="__('Help Text Field :index', { index: index + 1 })"
                              :error="form.errors[`fields.${index}.help`]"
                              type="text"
                            />
                          </div>
                          <div class="flex-1 w-full lg:w-auto">
                            <XInput
                              v-if="formFieldType[field.type].hasOptions"
                              v-model="field.options"
                              :label="__('Options Field :index', { index: index + 1 })"
                              :error="form.errors[`fields.${index}.options`]"
                              type="text"
                              :required="true"
                            />
                            <div
                              v-else
                              class="h-full text-muted-foreground text-lg font-semibold w-full flex items-center justify-center min-h-[40px]"
                            >
                              -
                            </div>
                          </div>
                        </div>
                      </template>
                    </Draggable>

                    <div class="flex justify-end mt-2">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addField"
                      >
                        {{ __("Add New Field") }}
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-between gap-2">
              <Button
                type="button"
                variant="outline"
                @click="showingFormPreview = true"
              >
                {{ __("Preview Form") }}
              </Button>
              <div class="flex gap-2">
                <Button
                  variant="outline"
                  as-child
                >
                  <Link :href="route('admin.custom-form.index')">
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
                  {{ __("Create Custom Form") }}
                </Button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <Dialog
        :open="showingFormPreview"
        @update:open="showingFormPreview = $event"
      >
        <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{{ __("Form Preview") }}</DialogTitle>
          </DialogHeader>

          <FormKitSchema :schema="computedFormSchema" />

          <DialogFooter>
            <Button
              variant="outline"
              @click="showingFormPreview = false"
            >
              {{ __("Close") }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AdminLayout>
</template>
