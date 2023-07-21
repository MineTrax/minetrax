<script setup>
import AppHead from '@/Components/AppHead.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import { reactive, ref } from 'vue';
import { useStorage } from '@vueuse/core';
const { __ } = useTranslations();

defineProps({
    featureEnabled: {
        type: Boolean,
        required: true,
    },
});

const showInformationAlert = useStorage('show-askai-information-alert', true);

const form = reactive({
    prompt: null,
    loading: false,
    error: null,
});

const showExamples = ref(true);
let results = reactive([]);

function askDb() {
    form.loading = true;
    form.error = null;
    axios.post(route('admin.ask-db.query'), {
        prompt: form.prompt,
    }).then((response) => {
        results.unshift({
            prompt: form.prompt,
            result: response.data.data,
        });
        form.prompt = null;
        showExamples.value = false;
    }).catch((error) => {
        form.error = error.response?.data?.message || error.message || __('Failed to Query Database! Try again after rephrasing your question.');
    }).finally(() => {
        form.loading = false;
    });
}

function askWithExample(example) {
    if(!example) return;

    if(form.loading) return;

    form.prompt = example;
    askDb();
}

const examples = [
    'Who is the most recent user to signup?',
    'Give 5 recent users who have linked their players.',
    'Which player has the most player kills?',
    'Which server has most number of players?',
    'How many user have their email verified?',
    'How many post is created by superadmin?',
];

</script>

<template>
  <AdminLayout>
    <AppHead :title="__('AskDB - AI based database query.')" />

    <div class="p-10 mx-auto space-y-4 max-w-5xl">
      <AlertCard
        v-if="showInformationAlert"
        :close-button="true"
        text-color="text-light-blue-800 dark:text-light-blue-500"
        border-color="border-light-blue-500"
        @close="showInformationAlert = false"
      >
        {{ __("What is Ask DB?") }}
        <template #body>
          {{
            __(
              "Ask DB is an AI based database query system. You can ask questions in natural language and it will try to answer it."
            )
          }}

          <p class="italic text-gray-400 dark:text-gray-500">
            {{
              __(
                "This is an experimental feature and still in beta. It may not always provide correct result. Please use with caution."
              )
            }}
          </p>
        </template>
      </AlertCard>


      <div
        v-if="!featureEnabled"
        class="rounded p-5 text-red-500 italic bg-white dark:bg-gray-800 text-center"
      >
        {{ __("This feature is not enabled!") }}
      </div>

      <template v-if="featureEnabled">
        <div
          id="logo"
          class="flex items-center justify-center"
        >
          <svg
            class="w-16 mr-2 text-gray-400"
            fill="currentColor"
            viewBox="0 0 24 24"
            pathfill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
          ><path
            data-v-94a7a0d7=""
            fill="currentColor"
            d="M18.5 10.255c0 .044 0 .089-.003.133A1.537 1.537 0 0 0 17.473 10c-.162 0-.32.025-.473.074V5.75a.75.75 0 0 0-.75-.75h-8.5a.75.75 0 0 0-.75.75v4.505c0 .414.336.75.75.75h8.276l-.01.025-.003.012-.45 1.384-.01.026a1.625 1.625 0 0 1-.019.053H7.75a2.25 2.25 0 0 1-2.25-2.25V5.75A2.25 2.25 0 0 1 7.75 3.5h3.5v-.75a.75.75 0 0 1 .649-.743L12 2a.75.75 0 0 1 .743.649l.007.101-.001.75h3.5a2.25 2.25 0 0 1 2.25 2.25v4.505Zm-5.457 3.781.112-.036H6.254a2.25 2.25 0 0 0-2.25 2.25v.907a3.75 3.75 0 0 0 1.305 2.844c1.563 1.343 3.802 2 6.691 2 2.076 0 3.817-.339 5.213-1.028a1.545 1.545 0 0 1-1.169-1.003l-.004-.012-.03-.093c-1.086.422-2.42.636-4.01.636-2.559 0-4.455-.556-5.713-1.638a2.25 2.25 0 0 1-.783-1.706v-.907a.75.75 0 0 1 .75-.75H12v-.003a1.543 1.543 0 0 1 1.031-1.456l.012-.005ZM10.999 7.75a1.25 1.25 0 1 0-2.499 0 1.25 1.25 0 0 0 2.499 0ZM14.242 6.5a1.25 1.25 0 1 1 0 2.499 1.25 1.25 0 0 1 0-2.499Zm1.847 10.912a2.831 2.831 0 0 0-1.348-.955l-1.377-.448a.544.544 0 0 1 0-1.025l1.377-.448a2.84 2.84 0 0 0 1.76-1.762l.01-.034.449-1.377a.544.544 0 0 1 1.026 0l.448 1.377a2.837 2.837 0 0 0 1.798 1.796l1.378.448.027.007a.544.544 0 0 1 0 1.025l-1.378.448a2.839 2.839 0 0 0-1.798 1.796l-.447 1.377a.55.55 0 0 1-.2.263.544.544 0 0 1-.827-.263l-.448-1.377a2.834 2.834 0 0 0-.45-.848Zm7.694 3.801-.765-.248a1.577 1.577 0 0 1-.999-.998l-.249-.765a.302.302 0 0 0-.57 0l-.249.764a1.577 1.577 0 0 1-.983.999l-.766.248a.302.302 0 0 0 0 .57l.766.249a1.576 1.576 0 0 1 .998 1.002l.25.764a.303.303 0 0 0 .57 0l.248-.764a1.575 1.575 0 0 1 1-.999l.765-.248a.302.302 0 0 0 0-.57l-.016-.004Z"
          /></svg>
          <h1 class="text-4xl font-bold dark:text-gray-500 text-gray-500">
            {{ __("Ask DB") }}
          </h1>
        </div>

        <div id="form">
          <div class="flex">
            <form
              class="flex w-full"
              @submit.prevent="askDb"
            >
              <div class="w-full">
                <input
                  id="large-input"
                  v-model="form.prompt"
                  type="text"
                  name="prompt"
                  :placeholder="__('Enter your query in natural language..')"
                  class="block w-full p-4 shadow-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-md focus:ring-light-blue-500 focus:border-light-blue-500 dark:bg-gray-700 dark:focus:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-light-blue-500 dark:focus:border-light-blue-500"
                  :disabled="form.loading"
                >
                <p
                  v-if="form.error"
                  class="text-sm mt-1 text-red-400 text-right"
                >
                  {{ form.error }}
                </p>
              </div>

              <LoadingButton
                :loading="form.loading"
                class="ml-2 uppercase h-14 whitespace-nowrap inline-flex justify-center py-2 px-4 border-2 border-light-blue-500 shadow-sm text-sm font-bold rounded-md text-light-blue-500 hover:bg-light-blue-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                type="submit"
              >
                {{ __("Ask DB") }}
              </LoadingButton>
            </form>
          </div>
        </div>

        <div
          v-if="results && results.length > 0"
          id="results"
          class="space-y-4"
        >
          <div
            v-for="result in results"
            :key="result.id"
            class="bg-white rounded shadow p-5 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:border"
          >
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">
              {{ result.prompt }}
            </h3>
            <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
            <p class="dark:text-gray-300 text-gray-700 whitespace-pre-wrap">
              {{ result.result }}
            </p>
          </div>
        </div>

        <div
          v-if="showExamples"
          id="examples"
        >
          <h1 class="text-gray-500 text-center text-xl font-bold mt-12 mb-2">
            {{ __("You can ask questions like:") }}
          </h1>

          <div class="grid grid-cols-2 gap-4">
            <button
              v-for="example in examples"
              :key="example"
              class="block w-full text-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 font-normal text-gray-700 dark:text-gray-400"
              @click="askWithExample(example)"
            >
              {{ example }}
            </button>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

