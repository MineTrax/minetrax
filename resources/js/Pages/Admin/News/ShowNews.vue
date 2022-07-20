<template>
  <app-layout>
    <app-head :title="news.title" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-end mb-8">
        <div class="flex">
          <button
            class="mr-2 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-red transition ease-in-out duration-150"
            @click="confirmNewsDeletion(news.id)"
          >
            {{ __("Delete") }}
          </button>
          <inertia-link
            :href="route('admin.news.edit', news.id)"
            class="mr-2 inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:shadow-outline-yellow transition ease-in-out duration-150"
          >
            <span>{{ __("Edit") }}</span>
          </inertia-link>
          <inertia-link
            :href="route('admin.news.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Go Back") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow max-w-none bg-white px-10 py-5 overflow-hidden border-b border-gray-200 sm:rounded-lg">
              <span
                v-if="news.type.value === 0"
                class="bg-light-blue-400 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>
              <span
                v-else-if="news.type.value === 1"
                class="bg-orange-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>
              <span
                v-else-if="news.type.value === 2"
                class="bg-green-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>
              <span
                v-else
                class="bg-gray-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>

              <h1 class="font-bold text-4xl text-gray-900 mb-5">
                {{ news.title }}
              </h1>
              <img
                v-if="news.photo_url"
                class="float-right w-1/2 ml-10"
                :src="news.photo_url"
                alt="News Image"
              >
              <div class="flex mb-5">
                <img
                  :src="news.creator.profile_photo_url"
                  alt="Profile"
                  class="h-12 w-12 mr-3 rounded-full"
                >
                <div>
                  <p class="font-bold text-gray-700">
                    {{ news.creator.name }}
                  </p>
                  <p class="text-gray-500 text-sm">
                    {{ formatTimeAgoToNow(news.created_at) }}
                  </p>
                </div>
              </div>
              <div
                class="prose max-w-none"
                v-html="news.body_html"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <jet-confirmation-modal
      :show="newsBeingDeleted"
      @close="newsBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete News") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this News?") }}
      </template>

      <template #footer>
        <jet-secondary-button @click.native="newsBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click.native="deleteNews"
        >
          {{ __("Delete News") }}
        </jet-danger-button>
      </template>
    </jet-confirmation-modal>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetConfirmationModal from '@/Jetstream/ConfirmationModal';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import JetDangerButton from '@/Jetstream/DangerButton';

export default {

    components: {
        AppLayout,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        news: Object
    },

    data() {
        return {
            form: this.$inertia.form(),
            newsBeingDeleted: null
        };
    },

    methods: {
        confirmNewsDeletion(newsId) {
            this.newsBeingDeleted = newsId;
        },

        deleteNews() {
            this.form.delete(route('admin.news.delete', this.newsBeingDeleted), {
                preserveScroll: false,
                preserveState: false,
                onSuccess: () => (this.newsBeingDeleted = null),
            });
        },
    }
};
</script>
