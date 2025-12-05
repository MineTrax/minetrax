<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { computed, nextTick, onMounted, reactive, ref } from 'vue';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Ask DB'),
        current: true,
    }
];

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
    if (!props.featureEnabled || form.loading || !form.prompt) {
        return;
    }

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
        results.pop();
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
      <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />

      <!-- Feature not enabled -->
      <AlertCard
        v-if="!featureEnabled"
        variant="destructive"
      >
        <template #icon>
          <ExclamationTriangleIcon class="h-6 w-6 mr-4 text-destructive" />
        </template>
        {{ __("This feature is not enabled!") }}
        <template #body>
          {{ __("Please configure AI & AskDB in .env file to enable this feature.") }}
        </template>
      </AlertCard>

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
            <Icon
              name="askdb-logo"
              class="w-16 h-16 mr-2 text-foreground"
            />
            <h1 class="text-4xl font-bold text-foreground">
              {{ __("Ask DB") }}
            </h1>
          </div>
          <p class="text-sm text-center text-muted-foreground">
            {{ __("Ask DB is an AI based database query system. You can ask questions in natural language and it will try to answer it.") }}
          </p>

          <!-- Examples -->
          <div id="examples">
            <h1 class="mt-12 mb-4 text-xl font-bold text-center text-foreground">
              {{ __("You can ask questions like:") }}
            </h1>

            <div class="grid grid-cols-2 gap-4">
              <button
                v-for="example in examples"
                :key="example"
                :disabled="!featureEnabled"
                class="block w-full p-6 text-sm font-normal text-left text-foreground bg-card border border-border rounded-lg shadow-sm hover:bg-accent hover:border-primary/50 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
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
          class="space-y-8 text-foreground"
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
                class="px-4 py-2.5 bg-primary text-primary-foreground rounded-2xl"
                v-html="result.content"
              />
            </div>
            <div
              v-else
              class="flex w-full"
            >
              <div class="flex-shrink-0">
                <Icon
                  name="askdb-logo"
                  class="w-6 h-6 mr-3 text-primary"
                />
              </div>
              <div class="flex-1 min-w-0">
                <div
                  class="prose max-w-none lg:max-w-[45vw] dark:prose-invert"
                  v-html="result.content"
                />
                <p
                  v-if="result.usage"
                  class="text-xs text-muted-foreground mt-1 italic"
                >
                  {{ __("Prompt: :prompt tokens, Completion: :completion tokens", {
                    prompt: result.usage?.promptTokens,
                    completion: result.usage?.completionTokens
                  }) }}
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
        <form
          class="w-full"
          @submit.prevent="askDb"
        >
          <div class="relative">
            <textarea
              id="large-input"
              v-model="form.prompt"
              name="prompt"
              :placeholder="__('Enter your query in natural language..')"
              class="block w-full h-24 p-4 pr-14 text-foreground border border-border rounded-lg shadow-sm resize-none bg-card placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="form.loading || !featureEnabled"
              rows="2"
              @keydown="handleKeydown"
            />

            <button
              type="submit"
              :disabled="form.loading || !featureEnabled"
              class="absolute p-2.5 transition-colors duration-200 ease-in-out rounded-lg bottom-3 right-3 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="form.loading"
                class="animate-spin w-5 h-5"
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
              <svg
                v-else
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

          <div class="mt-2">
            <p
              v-if="form.error"
              class="text-sm text-right text-destructive"
            >
              {{ form.error }}
            </p>

            <p
              v-if="appDebug && form.verboseError"
              class="p-3 text-sm text-center text-destructive bg-destructive/10 rounded-lg"
            >
              {{ form.verboseError }}
            </p>
          </div>

          <div class="flex items-center justify-between mt-2">
            <p class="hidden lg:block text-xs text-muted-foreground">
              {{ __("This is an experimental feature and still in beta. It may not always provide correct result. Please use with caution.") }}
            </p>
            <Link
              as="button"
              method="delete"
              :href="route('admin.ask-db.reset')"
              :preserve-state="false"
              :preserve-scroll="false"
              class="text-xs text-muted-foreground hover:text-destructive transition-colors"
            >
              {{ __("Clear History") }}
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

