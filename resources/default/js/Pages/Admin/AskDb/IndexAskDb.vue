<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed, nextTick, onMounted, reactive, ref } from 'vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
const { __ } = useTranslations();

const props = defineProps({
    featureEnabled: {
        type: Boolean,
        required: true,
    },
    appDebug: {
        type: Boolean,
        required: true,
    },
    chatHistory: {
        type: Array,
        default: () => [],
    },
});

const form = reactive({
    prompt: null,
    loading: false,
    error: null,
    verboseError: null,
});

const showWelcome = computed(() => {
    return !results.length && form.loading === false;
});
let results = reactive(props.chatHistory);

function askDb() {
    form.loading = true;
    form.error = null;
    form.verboseError = null;

    results.push({
        type: 'user',
        content: form.prompt,
    });
    scrollToBottom();

    const prompt = form.prompt;
    form.prompt = null;

    axios.post(route('admin.ask-db.query'), {
        prompt: prompt,
    }).then((response) => {
        results.push(response.data.data);
    }).catch((error) => {
        form.error = error.response?.data?.message || error.message || __('Failed to Query Database! Try again after rephrasing your question.');
        if (props.appDebug) {
            form.verboseError = error.response?.data?.verbose || null;
        }
        form.prompt = prompt;
    }).finally(() => {
        form.loading = false;
        scrollToBottom();
    });
}

function askWithExample(example) {
    if(!example) return;

    if(form.loading) return;

    form.prompt = example;
    askDb();
}

function handleKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        askDb();
    }
}

const examples = [
    'Who is the most recent user to signup?',
    'Give 5 recent users who have linked their players.',
    'Which player has the most player kills?',
    'Which server has most number of players?',
    'How many user have their email verified?',
    'How many post is created by superadmin?',
];

const container = ref(null);
onMounted(() => {
    scrollToBottom();
});

function scrollToBottom() {
    if (container.value) {
        nextTick(() => {
            container.value.scrollTo({
                top: container.value.scrollHeight,
                behavior: 'smooth', // Ensures the scrolling is smooth
            });
        });
    }
}
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('AskDB - AI based database query.')" />

    <div class="p-10 mx-auto space-y-4 max-w-5xl h-[94vh] flex flex-col">
      <!-- Feature not enabled -->
      <div
        v-if="!featureEnabled"
        class="p-5 mb-2 text-center text-red-500 bg-white rounded dark:bg-gray-800"
      >
        {{ __("This feature is not enabled!") }}
      </div>

      <!-- Container -->
      <div
        id="container"
        ref="container"
        class="pr-2 overflow-y-auto grow"
        :class="{'opacity-25': !featureEnabled}"
      >
        <template v-if="showWelcome">
          <!-- Logo -->
          <div
            id="logo"
            class="flex items-center justify-center mt-8"
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
            <h1 class="text-4xl font-bold text-gray-500 dark:text-gray-500">
              {{ __("Ask DB") }}
            </h1>
          </div>
          <p class="text-sm text-center text-gray-700 dark:text-gray-400">
            {{ __("Ask DB is an AI based database query system. You can ask questions in natural language and it will try to answer it.") }}
          </p>

          <!-- Examples -->
          <div
            id="examples"
          >
            <h1 class="mt-12 mb-2 text-xl font-bold text-center text-gray-500">
              {{ __("You can ask questions like:") }}
            </h1>

            <div class="grid grid-cols-2 gap-4">
              <button
                v-for="example in examples"
                :key="example"
                class="block w-full p-6 text-sm font-normal text-gray-700 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 dark:text-gray-400"
                @click="askWithExample(example)"
              >
                {{ example }}
              </button>
            </div>
          </div>
        </template>

        <!-- Results -->
        <div
          v-if="results && results.length > 0"
          id="results"
          class="space-y-8 text-gray-800 dark:text-gray-300"
        >
          <div
            v-for="result in results"
            :key="result.id"
          >
            <div
              v-if="result.type == 'user'"
              class="flex justify-end w-full"
            >
              <div
                class="px-4 py-2.5 dark:bg-gray-800 bg-gray-300 rounded-full"
                v-html="result.content"
              />
            </div>
            <div
              v-else
              class="flex w-full"
            >
              <div>
                <Icon
                  name="askdb-logo"
                  class="w-6 h-6 mr-2 text-gray-500 dark:text-gray-400"
                />
              </div>
              <div>
                <div
                  class="prose max-w-none lg:max-w-[45vw] dark:prose-dark"
                  v-html="result.content"
                />
                <p
                  v-if="result.usage"
                  class="text-xs text-gray-500 mt-0.5 italic"
                >
                  <span class="">{{ __("Prompt: :prompt tokens, Completion: :completion tokens", {
                    prompt: result.usage?.promptTokens,
                    completion: result.usage?.completionTokens
                  }) }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div
        id="form"
        :class="{'opacity-25': !featureEnabled}"
      >
        <div class="flex">
          <form
            class="relative flex w-full"
            @submit.prevent="askDb"
          >
            <div class="w-full">
              <div class="relative">
                <textarea
                  id="large-input"
                  v-model="form.prompt"
                  name="prompt"
                  :placeholder="__('Enter your query in natural language..')"
                  class="block w-full h-24 p-3 pr-12 text-gray-800 border-none rounded-lg shadow resize-none bg-gray-50 sm:text-md dark:bg-gray-800 dark:placeholder-gray-400 dark:text-gray-300 focus:outline-none focus:ring-0"
                  :disabled="form.loading"
                  rows="2"
                  @keydown="handleKeydown"
                />

                <button
                  type="submit"
                  :disabled="form.loading"
                  class="absolute p-2 transition-colors duration-200 ease-in-out rounded-full shadow-sm bottom-2 right-2 bg-light-blue-100 dark:bg-light-blue-800 text-light-blue-600 dark:text-light-blue-300 hover:bg-light-blue-200 dark:hover:bg-light-blue-700 hover:shadow-md disabled:opacity-50"
                >
                  <LoadingSpinner
                    :loading="form.loading"
                    class="w-5 h-5"
                  />
                  <svg
                    v-if="!form.loading"
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 5l7 7-7 7M5 5l7 7-7 7"
                    />
                  </svg>
                </button>
              </div>

              <div class="mt-1">
                <p
                  v-if="form.error"
                  class="text-sm text-right text-red-400"
                >
                  {{ form.error }}
                </p>

                <p
                  v-if="appDebug && form.verboseError"
                  class="p-2 text-sm text-center text-red-400 bg-white rounded shadow dark:bg-gray-800"
                >
                  {{ form.verboseError }}
                </p>
              </div>

              <Link
                as="button"
                method="delete"
                :href="route('admin.ask-db.reset')"
                :preserve-state="false"
                :preserve-scroll="false"
                class="float-right mt-1.5 text-xs text-gray-600 dark:text-gray-500 hover:text-red-500"
              >
                {{ __("Clear History") }}
              </Link>

              <p class="mt-2 hidden lg:block text-xs text-center text-gray-500">
                {{ __("This is an experimental feature and still in beta. It may not always provide correct result. Please use with caution.") }}
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

