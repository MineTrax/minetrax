<template>
  <AdminLayout>
    <app-head title="Create Custom Form" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Create Custom Form") }}
        </h1>
        <inertia-link
          :href="route('admin.custom-form.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-6 md:gap-6">
          <div class="mt-5 md:mt-0 md:col-span-6">
            <form @submit.prevent="createCustomForm">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-4">
                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="title"
                        v-model="form.title"
                        :label="__('Title of Custom Form')
                        "
                        :help="__('Eg: Contact Us')"
                        :error="form.errors.title"
                        type="text"
                        name="title"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="slug"
                        v-model="form.slug"
                        :label="__('Form Slug')"
                        :help="__(
                          'Only alphabet, number and dashes. Eg: contact-us'
                        )
                        "
                        :error="form.errors.slug"
                        type="text"
                        name="slug"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <x-select
                        id="status"
                        v-model="form.status"
                        name="status"
                        :label="__('Form Status')"
                        :placeholder="__(
                          'Select a status of form..'
                        )
                        "
                        :disable-null="true"
                        :select-list="formStatusList"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <textarea
                        id="description"
                        v-model="form.description"
                        aria-label="description"
                        name="description"
                        class="mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                      <jet-input-error
                        :message="form.errors.description
                        "
                        class="mt-2 text-right"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-select
                        id="can_create_submission"
                        v-model="form.can_create_submission
                        "
                        name="can_create_submission"
                        :label="__(
                          'Permission to Create Submission'
                        )
                        "
                        :placeholder="__(
                          'Select a who can create submittion for this form..'
                        )
                        "
                        :disable-null="true"
                        :select-list="canCreateSubmissionList
                        "
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        v-if="form.can_create_submission !== 'anyone'"
                        id="max_submission_per_user"
                        v-model="form.max_submission_per_user"
                        :label="__('Max Submission Per User')
                        "
                        :help="__('Leave empty to allow unlimited submission per user.')"
                        :error="form.errors.max_submission_per_user"
                        type="number"
                        name="max_submission_per_user"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="min_role_weight_to_view_submission"
                        v-model="form.min_role_weight_to_view_submission"
                        :label="__('Role Weight to View Submission')
                        "
                        :help="__('Leave empty to allow any staff with view custom_form_submissions permission to view submissions.')"
                        :error="form.errors.min_role_weight_to_view_submission"
                        type="number"
                        name="min_role_weight_to_view_submission"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <fieldset>
                        <div class="mt-4 flex space-x-4">
                          <XCheckbox
                            id="is_notify_staff_on_submission"
                            v-model="form.is_notify_staff_on_submission
                            "
                            :label="__(
                              'Notify Staff on Submission'
                            )
                            "
                            :help="__(
                              'Notify staff members (with view submission permission) when new submission is made for this form.'
                            )
                            "
                            name="is_notify_staff_on_submission"
                          />
                        </div>
                        <jet-input-error
                          :message="form.errors.is_notify_staff_on_submission
                          "
                          class="mt-2"
                        />
                      </fieldset>
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <fieldset>
                        <div class="mt-4 flex space-x-4">
                          <XCheckbox
                            id="is_visible_in_listing"
                            v-model="form.is_visible_in_listing
                            "
                            :label="__(
                              'Is Visible in Listing'
                            )
                            "
                            :help="__(
                              'Allow this form to be listed in custom form listing page.'
                            )
                            "
                            name="is_visible_in_listing"
                          />
                        </div>
                        <jet-input-error
                          :message="form.errors.is_visible_in_listing
                          "
                          class="mt-2"
                        />
                      </fieldset>
                    </div>

                    <div class="flex-col col-span-6 space-y-1 sm:col-span-6">
                      <legend class="text-base font-medium text-gray-900 dark:text-gray-300">
                        {{ __("Fields") }}
                      </legend>

                      <div class="w-full space-y-1">
                        <div class="flex space-x-4">
                          <div class="w-5" />
                          <label
                            class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                          >{{
                             __("Name") }}
                            <span class="text-red-500">*</span>
                          </label>
                          <label
                            class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                          >{{
                             __("Type") }}
                            <span class="text-red-500">*</span>
                          </label>
                          <label
                            class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                          >{{
                            __("Validation")
                          }}</label>
                          <label
                            class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                          >{{
                            __("Help Text")
                          }}</label>
                          <label
                            class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                          >{{
                             __("Options") }}
                            <span class="text-red-500">*
                            </span>
                            <span class="text-xs text-gray-500">(Eg:
                              Options1,Option2)</span>
                          </label>
                        </div>

                        <div
                          v-for="(
                            field, index
                          ) in form.fields"
                          :key="index"
                          class="flex space-x-4"
                        >
                          <button
                            type="button"
                            class="focus:outline-none group"
                            @click="
                              removeField(index)
                            "
                          >
                            <Icon
                              class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                              name="trash"
                            />
                          </button>
                          <div class="flex-1">
                            <x-input
                              v-model="field.label
                              "
                              :label="__(
                                'Name Field :index',
                                {
                                  index:
                                    index +
                                    1,
                                }
                              )
                              "
                              :error="form.errors[
                                `fields.${index}.label`
                              ] ||
                                form.errors[
                                  `fields.${index}.name`
                                ]
                              "
                              type="text"
                              help-error-flex="flex-col"
                              :required="true"
                            />
                          </div>
                          <div class="flex-1">
                            <x-select
                              v-model="field.type"
                              :label="__('Page Type')
                              "
                              :error="form.errors[
                                `fields.${index}.type`
                              ]
                              "
                              help-error-flex="flex-col"
                              :select-list="Object.keys(
                                formFieldType
                              )
                              "
                              :required="true"
                            />
                          </div>
                          <div class="flex-1">
                            <x-input
                              v-model="field.validation
                              "
                              :label="__(
                                'Validation Field :index',
                                {
                                  index:
                                    index +
                                    1,
                                }
                              )
                              "
                              :error="form.errors[
                                `fields.${index}.validation`
                              ]
                              "
                              type="text"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div class="flex-1">
                            <x-input
                              v-model="field.help"
                              :label="__(
                                'Help Text Field :index',
                                {
                                  index:
                                    index +
                                    1,
                                }
                              )
                              "
                              :error="form.errors[
                                `fields.${index}.help`
                              ]
                              "
                              type="text"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div class="flex-1">
                            <x-input
                              v-if="formFieldType[
                                field.type
                              ].hasOptions
                              "
                              v-model="field.options
                              "
                              :label="__(
                                'Options Field :index',
                                {
                                  index:
                                    index +
                                    1,
                                }
                              )
                              "
                              :error="form.errors[
                                `fields.${index}.options`
                              ]
                              "
                              type="text"
                              help-error-flex="flex-col"
                              :required="true"
                            />
                            <div
                              v-else
                              class="h-full text-gray-700 text-lg font-semibold dark:text-gray-300 w-full flex items-center justify-center"
                            >
                              -
                            </div>
                          </div>
                        </div>

                        <div class="flex justify-end mt-1">
                          <button
                            type="button"
                            class="p-1.5 text-xs text-light-blue-500 rounded border border-light-blue-500 focus:outline-none hover:text-light-blue-300 hover:border-light-blue-300 transition ease-in-out duration-150"
                            @click="addField"
                          >
                            {{
                              __("Add New Field")
                            }}
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-between gap-2">
                  <loading-button
                    class="inline-flex justify-center py-2 px-4 border border-gray-200 shadow-sm text-sm font-medium rounded-md text-gray-600 bg-gray-50 hover:bg-white disabled:opacity-50 dark:bg-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:border-gray-600"
                    type="button"
                    @click="showingFormPreview = true"
                  >
                    {{ __("Preview Form") }}
                  </loading-button>
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Create Custom Form") }}
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <JetDialogModal
        :show="showingFormPreview"
        @close="showingFormPreview = false"
      >
        <template #title>
          <h3 class="text-lg font-bold">
            {{ __("Form Preview") }}
          </h3>
        </template>

        <template #content>
          <FormKitSchema :schema="computedFormSchema" />
        </template>

        <template #footer>
          <jet-secondary-button @click="showingFormPreview = false">
            {{ __("Close") }}
          </jet-secondary-button>
        </template>
      </JetDialogModal>
    </div>
  </AdminLayout>
</template>

<script setup>
import { FormKitSchema } from '@formkit/vue';
import JetInputError from '@/Jetstream/InputError.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import EasyMDE from 'easymde';
import { onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Icon from '@/Components/Icon.vue';
import { computed, watch } from 'vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import {useFormKit} from '@/Composables/useFormKit';
import { kebabCase } from 'lodash';

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

let easyMDE = null;

const createCustomForm = () => {
    form.description = easyMDE.value();
    form.fields.map(item => {
        item.name = item.label.toLowerCase().replace(/ /g, '_');
    });

    if (form.can_create_submission === 'anyone') {
        form.max_submission_per_user = null;
    }

    form.post(route('admin.custom-form.store'), {});
};

onMounted(() => {
    easyMDE = new EasyMDE({
        previewClass: 'editor-preview prose max-w-none',
    });
});

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
