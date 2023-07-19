<template>
  <div class="flex flex-col">
    <hr class="mt-0.5 dark:border-cool-gray-700">

    <!-- Loading Spinner -->
    <div
      v-if="loading || loadingMore"
      class="flex justify-center p-4"
    >
      <svg
        class="w-5 h-5 mr-3 -ml-1 animate-spin text-light-blue-600 dark:text-light-blue-400"
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
    </div>

    <!-- Load More Comment Button -->
    <div
      v-show="!loadingMore && !loading && showLoadMoreCommentsButton"
      class="flex mt-3"
    >
      <button
        class="text-sm font-semibold text-gray-500 dark:text-gray-400 focus:outline-none hover:underline"
        @click="loadMoreComments"
      >
        {{ __("View previous comments") }}
      </button>
    </div>

    <!-- User Comments -->
    <div
      v-if="!loading && comments"
      class="flex flex-col mt-3 space-y-2"
    >
      <div
        v-for="comment in comments.data"
        :key="comment.id"
        class="flex"
      >
        <div class="items-start order-2 max-w-lg mx-2 space-y-2 text-sm">
          <div
            class="flex flex-col px-4 py-2 text-gray-700 bg-gray-100 rounded-tl-lg rounded-2xl dark:bg-cool-gray-600 dark:bg-opacity-25 dark:text-gray-200"
            :class="{'border border-gray-300 dark:border-gray-700': $page.props.auth.user && $page.props.auth.user.id === comment.user_id}"
          >
            <inertia-link
              as="div"
              class="hover:cursor-pointer hover:underline"
              :href="route('user.public.get', comment.commentator.username)"
            >
              <user-displayname
                :user="comment.commentator"
                :show-username="true"
                text-class="font-sm"
              >
                <span
                  v-tippy
                  class="inline ml-1 text-xs text-gray-500 dark:text-gray-400 focus:outline-none"
                  :title="formatToDayDateString(comment.created_at)"
                >
                  {{ formatTimeAgoToNow(comment.created_at) }}
                </span>
              </user-displayname>
            </inertia-link>
            <span v-html="purifyAndLinkifyText(comment.comment)" />
          </div>
        </div>
        <img
          :src="comment.commentator.profile_photo_url"
          alt="My profile"
          class="order-1 w-8 h-8 mt-2 rounded-full"
        >
        <inertia-link
          v-if="$page.props.auth.user && comment.permissions.delete"
          v-confirm="{message: __('Are you sure you want to delete this comment?')}"
          :preserve-scroll="true"
          :preserve-state="false"
          as="button"
          method="delete"
          :href="route('post.comment.delete', [post.id, comment.id])"
          class="order-3 focus:outline-none"
        >
          <icon
            name="trash"
            class="w-4 h-4 text-gray-200 hover:text-red-400 dark:text-gray-500 dark:hover:text-red-500"
          />
        </inertia-link>
      </div>
    </div>

    <!-- Comments Input Box -->
    <div
      v-if="$page.props.auth.user"
      class="flex mt-1"
    >
      <img
        :src="$page.props.auth.user.profile_photo_url"
        alt="My profile"
        class="order-1 w-8 h-8 mt-2 rounded-full"
      >
      <div class="flex-grow order-2 mx-2 text-sm">
        <form @submit.prevent="submitComment">
          <input
            ref="comment"
            v-model="commentBody"
            :disabled="submitting"
            :placeholder="__('Write a comment...')"
            aria-label="comment"
            type="text"
            class="block w-full mt-1 bg-gray-100 border border-gray-100 rounded-full dark:bg-cool-gray-900 focus:border-gray-300 dark:border-gray-800 dark:focus:border-gray-700 dark:text-gray-200 focus:ring-0 sm:text-sm disabled:opacity-50"
            @keypress.enter="submitComment"
          >
        </form>
        <span
          v-if="bodyerror"
          class="ml-2 text-xs text-red-500"
        >{{ bodyerror }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    components: {Icon, UserDisplayname},

    props: {
        post: Object
    },

    setup() {
        const {purifyAndLinkifyText, formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {purifyAndLinkifyText, formatTimeAgoToNow, formatToDayDateString};
    },

    data() {
        return {
            comments: null,
            loading: true,
            loadingMore: false,
            commentBody: '',
            submitting: false,
            bodyerror: null,
            showLoadMoreCommentsButton: true
        };
    },

    created() {
        axios.get(route('post.comment.index', this.post.id)).then(response => {
            this.comments = response.data;
            this.comments.data.reverse();
        }).finally(() => {
            this.loading = false;
        });
    },

    methods: {
        loadMoreComments() {
            if (!this.comments.next_page_url) {
                this.showLoadMoreCommentsButton = false;
                return Promise.resolve();
            }

            this.loadingMore = true;
            return axios(this.comments.next_page_url).then(response => {
                this.comments = {
                    ...response.data,
                    data: [...response.data.data.reverse(), ...this.comments.data]
                };
            }).finally(() => this.loadingMore = false);
        },

        submitComment() {
            if (this.submitting) return;

            this.submitting = true;
            this.bodyerror = null;
            axios.post(route('post.comment.store', this.post.id), {
                comment: this.commentBody
            }).then(response => {
                if (response.status === 200) {
                    this.comments.data.push(response.data.data);
                    // Set Body to empty
                    this.commentBody = '';
                }
            }).catch(error => {
                if (error.response.status === 422)
                    this.bodyerror = error.response.data.errors.comment[0];
                else
                    this.bodyerror = error.response.data.message;
            }).finally(() => {
                this.submitting = false;
                // Wait for next tick coz component is re-rendered (it check if component is visible with v-if)
                this.$nextTick(() => {
                    this.$refs.comment.focus();
                });
            });
        }
    }
};
</script>
