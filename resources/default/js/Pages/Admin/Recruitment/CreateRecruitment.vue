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

defineProps({
    roles: {
        type: Array,
        required: true,
    },
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Application Forms'),
        url: route('admin.recruitment.index'),
        current: false,
    },
    {
        text: __('Create Application Form'),
        current: true,
    }
];

const formStatusList = {
    draft: 'Draft - Application is under development and not visible to users',
    active: 'Active - Application is actively accepting submissions',
    disabled: 'Disabled - Application is disabled for new submissions',
    archived: 'Archived - Application is archived and not visible to users',
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
    max_submission_per_user: null,
    submission_cooldown_in_seconds: null,
    is_allow_only_player_linked_users: false,
    is_allow_only_verified_users: false,
    is_allow_messages_from_users: true,
    min_role_weight_to_view_submission: null,
    min_role_weight_to_vote_on_submission: null,
    min_role_weight_to_act_on_submission: null,
    is_notify_staff_on_submission: true,
    related_role_id: null,
    fields: [
        {
            type: 'number',
            label: 'Years of Experience',
            name: 'experience',
            placeholder: null,
            help: null,
            validation: 'required|number',
            options: null,
        },
        {
            type: 'textarea',
            label: 'Tell us about yourself',
            name: 'aboutme',
            placeholder: null,
            help: 'Write about your experience, skills, and why you want to join us.',
            validation: 'required|string',
            options: null,
        },
    ],
});

const createRecruitment = () => {
    form.fields.map(item => {
        item.name = item.label.toLowerCase().replace(/ /g, '_');
    });

    form.post(route('admin.recruitment.store'), {});
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
    <app-head :title="__('Create Application Form')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createRecruitment">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <XInput
                    id="title"
                    v-model="form.title"
                    :label="__('Title of this Application')"
                    :help="__('Eg: Apply to be a Staff Member')"
                    :error="form.errors.title"
                    type="text"
                    name="title"
                  />
                </div>

                <div class="col-span-6">
                  <XInput
                    id="slug"
                    v-model="form.slug"
                    :label="__('Application Slug for URL')"
                    :help="__('Only alphabet, number and dashes. Eg: apply-to-be-a-staff-member')"
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
                    :label="__('Application Status')"
                    :placeholder="__('Select a status of application..')"
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
                  <XInput
                    id="max_submission_per_user"
                    v-model="form.max_submission_per_user"
                    :label="__('Max Submission Per User')"
                    :help="__('How many times a user can reapply after rejection. Leave empty for no limit.')"
                    :error="form.errors.max_submission_per_user"
                    type="number"
                    name="max_submission_per_user"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    v-if="form.max_submission_per_user != 1"
                    id="submission_cooldown_in_seconds"
                    v-model="form.submission_cooldown_in_seconds"
                    :label="__('Submission Cooldown in Seconds')"
                    :help="__('After how many seconds user can reapply this application. Leave empty for no cooldown.')"
                    :error="form.errors.submission_cooldown_in_seconds"
                    type="number"
                    name="submission_cooldown_in_seconds"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="min_role_weight_to_view_submission"
                    v-model="form.min_role_weight_to_view_submission"
                    :label="__('Min Staff Role Weight to View Submission')"
                    :help="__('Leave empty to allow any staff with [view recruitment_submissions] permission to view submissions.')"
                    :error="form.errors.min_role_weight_to_view_submission"
                    type="number"
                    name="min_role_weight_to_view_submission"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="min_role_weight_to_vote_on_submission"
                    v-model="form.min_role_weight_to_vote_on_submission"
                    :label="__('Min Staff Role Weight to Vote on Submission')"
                    :help="__('Leave empty to allow any staff with [vote recruitment_submissions] permission to vote on submissions.')"
                    :error="form.errors.min_role_weight_to_vote_on_submission"
                    type="number"
                    name="min_role_weight_to_vote_on_submission"
                  />
                </div>

                <div class="col-span-6">
                  <XInput
                    id="min_role_weight_to_act_on_submission"
                    v-model="form.min_role_weight_to_act_on_submission"
                    :label="__('Min Staff Role Weight to Act on Submission')"
                    :help="__('Min staff role weight required to Approve/Reject on submission. Leave empty to allow any staff with [acton recruitment_submissions] permission to act on submissions.')"
                    :error="form.errors.min_role_weight_to_act_on_submission"
                    type="number"
                    name="min_role_weight_to_act_on_submission"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_allow_messages_from_users"
                    v-model="form.is_allow_messages_from_users"
                    :label="__('Enable Messages Feature')"
                    :help="__('Enable messages feature for this application. User & Staff will be able to send messages.')"
                    name="is_allow_messages_from_users"
                    :error="form.errors.is_allow_messages_from_users"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_notify_staff_on_submission"
                    v-model="form.is_notify_staff_on_submission"
                    :label="__('Notify Staff on Event')"
                    :help="__('Notify staff (with view permission) when application created/withdrawn or message from user.')"
                    name="is_notify_staff_on_submission"
                    :error="form.errors.is_notify_staff_on_submission"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_allow_only_player_linked_users"
                    v-model="form.is_allow_only_player_linked_users"
                    :label="__('Allow only Player Linked Users')"
                    :help="__('Allow only users who have linked player to their account to apply.')"
                    name="is_allow_only_player_linked_users"
                    :error="form.errors.is_allow_only_player_linked_users"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_allow_only_verified_users"
                    v-model="form.is_allow_only_verified_users"
                    :label="__('Allow only Verified Users')"
                    :help="__('Allow only verified users to apply for this application.')"
                    name="is_allow_only_verified_users"
                    :error="form.errors.is_allow_only_verified_users"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="related_role_id"
                    v-model="form.related_role_id"
                    name="related_role_id"
                    :label="__('This Application is Hiring for')"
                    :placeholder="__('Not Applicable (None)')"
                    :disable-null="false"
                    :select-list="roles"
                    :help="__('If this application is for hiring of a specific role, select the role here.')"
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
                  <Link :href="route('admin.recruitment.index')">
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
                  {{ __("Create Application Form") }}
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
