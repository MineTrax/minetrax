<template>
  <div class="space-y-4 w-full">
    <div
      v-if="$page.props.auth.user && !username"
      class="p-3 sm:px-5 bg-white dark:bg-cool-gray-800 rounded shadow"
    >
      <form
        class="mt-4 text-xl flex flex-col items-end space-y-2"
        @submit.prevent="submitPost"
      >
        <textarea
          ref="post-content"
          v-model="body"
          class="mt-1 overflow-hidden block w-full shadow-sm sm:text-sm border-gray-300 rounded-md resize-none focus:border-light-blue-300 focus:ring text-sm focus:ring-light-blue-200 focus:ring-opacity-50 dark:bg-cool-gray-900 dark:text-gray-200 dark:border-gray-800"
          aria-label="post content"
          :placeholder="bodyPlaceholderText"
          name="post"
          rows="3"
          @keypress.enter.shift="submitPost"
          @input="resizeTextArea"
        />

        <div
          v-if="imagesDisplay.length"
          class="grid gap-1 grid-cols-3 w-full"
        >
          <div
            v-for="(img, index) in imagesDisplay"
            :key="index"
            class="relative"
          >
            <button
              type="button"
              class="m-1 absolute top-0 left-0 bg-black bg-opacity-75 rounded-full cursor-pointer hover:bg-opacity-100 text-white"
              @click="removeMedia(index)"
            >
              <icon
                name="close"
                class="p-1 text-gray-500 dark:text-gray-400"
              />
            </button>
            <img
              :src="img.url"
              alt="Attachment"
              class="object-cover rounded-xl h-48"
            >
          </div>
        </div>

        <div class="flex justify-between w-full items-center">
          <input
            ref="file_selector"
            name="images"
            type="file"
            class="hidden"
            multiple
            accept="image/*"
            @change="handleImageChangeListener"
          >
          <button
            v-tippy
            :title="__('Add Media')"
            type="button"
            class="inline-flex items-center justify-center h-10 w-10 hover:bg-light-blue-100 dark:hover:bg-cool-gray-900 rounded-full focus:outline-none"
            @click="openImageSelector"
          >
            <icon
              name="photograph"
              class="h-6 w-6 text-light-blue-400"
            />
          </button>
          <span><jet-input-error
            :message="bodyerror"
            class="mt-2"
          /></span>
          <loading-button
            class="justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-bold tracking-wide leading-5 rounded-full text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
            :loading="postsubmitting"
            type="submit"
          >
            {{ __("Post") }}
          </loading-button>
        </div>
      </form>
    </div>

    <div
      v-if="loading"
      class="space-y-4"
    >
      <div class="px-5 py-4 bg-white dark:bg-cool-gray-800 shadow rounded-lg">
        <div class="animate-pulse flex space-x-4">
          <div class="rounded-full bg-gray-300 dark:bg-cool-gray-700 h-12 w-12" />
          <div class="flex-1 space-y-4 py-1">
            <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
            <div class="space-y-2">
              <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded" />
              <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
            </div>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 bg-white dark:bg-cool-gray-800 shadow rounded-lg">
        <div class="animate-pulse flex space-x-4">
          <div class="rounded-full bg-gray-300 dark:bg-cool-gray-700 h-12 w-12" />
          <div class="flex-1 space-y-4 py-1">
            <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
            <div class="space-y-2">
              <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded" />
              <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <infinite-scroll :load-more="loadMorePosts">
      <transition-group
        name="list"
        tag="div"
        class="space-y-4"
      >
        <post
          v-for="post in posts.data"
          :key="post.id"
          :post="post"
        />
        <div
          v-if="!loading && showEmptyPost && posts.data.length <= 0"
          :key="999999999"
          class="flex bg-white dark:bg-cool-gray-800 items-center justify-center italic text-gray-500 dark:text-gray-400 p-4 rounded shadow"
        >
          {{ __(":username hasn't posted anything yet.", {username: username}) }}
        </div>
      </transition-group>
    </infinite-scroll>
  </div>
</template>

<script>

import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import Post from '@/Components/Post.vue';
import {sample} from 'lodash/collection';
import Icon from '@/Components/Icon.vue';

export default {
    components: {Icon, LoadingButton, InfiniteScroll, JetInputError, Post},

    props: {
        username: {
            type: [String],
            default: null
        },
        showEmptyPost: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            loading: true,
            posts: {},
            body: '',
            bodyerror: null,
            postsubmitting: false,
            bodyPlaceholderText: sample([
                this.__('Whats up'),
                this.__('Whats happening?'),
                this.__('Having fun? Tell us about it.'),
                this.__('Are you alex or steve?'),
                this.__('Have you seen Herobrine?')
            ]),
            imagesDisplay: []
        };
    },

    created() {
        let routeToHit = route('post.index');
        if (this.username) {
            routeToHit = route('post.user.index', this.username);
        }
        axios.get(routeToHit).then(response => {
            this.posts = response.data;
        }).finally(e => {
            this.loading = false;
        });
    },

    methods: {
        loadMorePosts() {
            if (!this.posts.next_page_url) {
                return Promise.resolve();
            }

            return axios(this.posts.next_page_url).then(response => {
                this.posts = {
                    ...response.data,
                    data: [...this.posts.data, ...response.data.data]
                };
            });
        },

        submitPost() {

            // Prevents accidental double posting
            if (this.postsubmitting) return;

            this.postsubmitting = true;
            this.bodyerror = null;

            let formData = new FormData();
            formData.append('body', this.body);

            for (let i = 0; i < this.imagesDisplay.length; i++) {
                formData.append('media[' + i + ']', this.imagesDisplay[i].file);
            }

            axios.post(route('post.store'), formData).then(response => {
                if (response.status === 200) {
                    this.posts.data.unshift(response.data.data);
                    // Set Body to empty and resize the textarea to initial
                    this.body = '';
                    this.imagesDisplay = [];
                    const textarea = this.$refs['post-content'];
                    textarea.style.height = 'initial';
                }
            }).catch(error => {
                if (error.response.status === 422) {
                    this.bodyerror = Object.values(error.response.data.errors)[0][0];
                } else {
                    this.bodyerror = error.response.data.message;
                }
            }).finally(() => this.postsubmitting = false);
        },

        resizeTextArea() {
            const textarea = this.$refs['post-content'];
            textarea.style.height = 'initial';
            textarea.style.height = `${textarea.scrollHeight}px`;
        },

        openImageSelector() {
            this.$refs.file_selector.click();
        },

        handleImageChangeListener(ev) {
            const images = ev.target.files;

            Array.from(images).forEach(img => {
                let reader = new FileReader();
                reader.readAsDataURL(img);

                reader.onload = (e) => {
                    this.imagesDisplay.push({
                        url: e.target.result,
                        file: img
                    });
                };
            });
        },

        removeMedia(index) {
            this.imagesDisplay.splice(index, 1);
        }
    }
};
</script>

<style scoped>
.list-item {
    display: inline-block;
    margin-right: 10px;
}

.list-enter-active, .list-leave-active {
    transition: all 1s;
}

.list-enter-from, .list-leave-to /* .list-leave-active below version 2.1.8 */
{
    opacity: 0;
    transform: translateY(30px);
}
</style>
