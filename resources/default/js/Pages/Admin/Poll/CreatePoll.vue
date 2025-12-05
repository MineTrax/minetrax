<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XDatePicker from '@/Components/Form/XDatePicker.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import { TrashIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Polls'),
        url: route('admin.poll.index'),
        current: false,
    },
    {
        text: __('Create Poll'),
        current: true,
    }
];

const form = useForm({
    question: '',
    options: [{ name: '' }, { name: '' }],
    closed_at: null,
    started_at: null
});

function addMoreOption() {
    form.options.push({
        name: ''
    });
}

function deleteOption(index) {
    form.options.splice(index, 1);
}

function createPoll() {
    form.post(route('admin.poll.store'), {
        preserveScroll: true
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create New Poll')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createPoll">
              <div class="shadow overflow-hidden rounded-lg">
                <div class="px-4 py-5 bg-card sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                      <XInput
                        id="question"
                        v-model="form.question"
                        :label="__('Poll Question')"
                        :placeholder="__('Eg: Do you think minecraft is best game?')"
                        :error="form.errors.question"
                        type="text"
                        name="question"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6 flex-col space-y-1">
                      <div
                        v-for="(option, index) in form.options"
                        :key="index"
                        class="flex space-x-4"
                      >
                        <button
                          type="button"
                          class="focus:outline-none group flex mt-2.5"
                          @click="deleteOption(index)"
                        >
                          <TrashIcon
                            class="w-4 h-4 text-muted-foreground group-hover:text-destructive"
                          />
                        </button>
                        <div class="flex-1">
                          <XInput
                            :id="`option${index}`"
                            v-model="option.name"
                            :placeholder="__('Option :number', {number: index+1})"
                            :error="form.errors[`options.${index}.name`]"
                            type="text"
                            :name="`option${index}`"
                          />
                        </div>
                      </div>

                      <div class="flex justify-end mt-1">
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          @click="addMoreOption"
                        >
                          {{ __("Add More") }}
                        </Button>
                      </div>

                      <div class="flex justify-end">
                        <JetInputError
                          :message="form.errors.options"
                          class="mt-2"
                        />
                      </div>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <XDatePicker
                        id="started_at"
                        v-model="form.started_at"
                        :label="__('Starts At')"
                        :placeholder="__('Poll Starts At')"
                        :error="form.errors.started_at"
                        type="datetime"
                        format="YYYY-MM-DD hh:mm:ss A"
                        value-type="format"
                        name="started_at"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <XDatePicker
                        id="closed_at"
                        v-model="form.closed_at"
                        :label="__('Ends At')"
                        :placeholder="__('Poll Ends At')"
                        :error="form.errors.closed_at"
                        type="datetime"
                        format="YYYY-MM-DD hh:mm:ss A"
                        value-type="format"
                        name="closed_at"
                      />
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
                  <Button
                    variant="outline"
                    as-child
                  >
                    <Link :href="route('admin.poll.index')">
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
                    {{ __("Create Poll") }}
                  </Button>
                </div>
              </div>
            </form>
      </div>
    </div>
  </AdminLayout>
</template>
