<template>
  <AdminLayout>
    <app-head title="Create New Poll" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Create New Poll") }}
        </h1>
        <inertia-link
          :href="route('admin.poll.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                {{ __("Tips") }}
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                {{ __("Adding polls in your website increase user retention & engagement.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="createPoll">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="question"
                        v-model="form.question"
                        :label="__('Poll Question')"
                        :help="__('Eg: Do you think minecraft is best game?')"
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
                          class="focus:outline-none group"
                          @click="deleteOption(index)"
                        >
                          <icon
                            class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                            name="trash"
                          />
                        </button>
                        <div class="flex-1">
                          <x-input
                            :id="`option${index}`"
                            v-model="option.name"
                            :label="__('Option :number', {number: index+1})"
                            :error="form.errors[`options.${index}.name`]"
                            type="text"
                            :name="`option${index}`"
                          />
                        </div>
                      </div>

                      <div class="flex justify-end mt-1">
                        <button
                          type="button"
                          class="p-1.5 text-xs text-light-blue-500 rounded border border-light-blue-500 focus:outline-none"
                          @click="addMoreOption"
                        >
                          {{ __("Add More") }}
                        </button>
                      </div>

                      <div class="flex justify-end">
                        <jet-input-error
                          :message="form.errors.options"
                          class="mt-2"
                        />
                      </div>
                    </div>

                    <div class="col-span-6 sm:col-span-3 relative">
                      <date-picker
                        id="started_at"
                        v-model:value="form.started_at"
                        :placeholder="__('Poll Starts At')"
                        class="w-full"
                        value-type="format"
                        type="datetime"
                        input-class="border-gray-300 h-14 p-3 text-sm pt-7 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md block w-full dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900"
                      />
                      <label
                        for="started_at"
                        class="absolute -top-2.5 left-0 px-3 py-5 text-xs text-gray-500 h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out"
                      >{{ __("Starts At") }}</label>
                      <jet-input-error
                        :message="form.errors.started_at"
                        class="mt-2"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3 relative">
                      <date-picker
                        id="closed_at"
                        v-model:value="form.closed_at"
                        :placeholder="__('Poll Ends At')"
                        class="w-full"
                        value-type="format"
                        type="datetime"
                        input-class="border-gray-300 h-14 p-3 text-sm pt-7 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md block w-full dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900"
                      />
                      <label
                        for="closed_at"
                        class="absolute -top-2.5 left-0 px-3 py-5 text-xs text-gray-500 h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out"
                      >{{ __("Ends At") }}</label>
                      <jet-input-error
                        :message="form.errors.closed_at"
                        class="mt-2"
                      />
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Create Poll") }}
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import JetInputError from '@/Jetstream/InputError.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import Icon from '@/Components/Icon.vue';
import DatePicker from 'vue-datepicker-next';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        JetInputError,
        LoadingButton,
        XInput,
        Icon,
        DatePicker
    },
    data() {
        return {
            form: useForm({
                question: '',
                options: [{name: ''}, {name: ''}],
                closed_at: null,
                started_at: null
            }),
        };
    },

    methods: {
        addMoreOption() {
            this.form.options.push({
                name: ''
            });
        },
        deleteOption(index) {
            this.form.options.splice(index, 1);
        },

        createPoll() {
            this.form.post(route('admin.poll.store'), {
                preserveScroll: true
            });
        },
    }
};
</script>

<style scoped>
.mx-datepicker {
    width: 100% !important;
}
</style>
