<template>
  <div class="flex flex-col px-3">
    <hr
      v-if="commentableType == 'post'"
      class="mt-0.5"
    >

    <!-- Loading Spinner -->
    <div
      v-if="loading || loadingMore"
      class="flex justify-center p-4"
    >
      <svg
        class="w-5 h-5 mr-3 -ml-1 animate-spin text-primary dark:text-primary"
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
      v-show="!loadingMore && !loading && showLoadMoreCommentsButton && comments && comments.next_page_url"
      class="flex mt-3"
    >
      <button
        class="text-sm font-semibold text-foreground dark:text-foreground focus:outline-none hover:underline"
        @click="loadMoreComments"
      >
        {{ __("View previous comments") }}
      </button>
    </div>

    <!-- Show no comments -->
    <div
      v-if="!loading && comments && comments.data.length === 0"
      class="flex justify-center pt-4 text-sm text-muted-foreground"
    >
      {{ __("No comments yet") }}
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
            class="flex flex-col px-4 py-2 text-foreground bg-surface-100 rounded-tl-lg rounded-2xl dark:bg-surface-600 dark:bg-opacity-25 dark:text-foreground"
            :class="{'border border-foreground dark:border-foreground': $page.props.auth.user && $page.props.auth.user.id === comment.user_id}"
          >
            <inertia-link
              as="a"
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
                  class="inline ml-1 text-xs text-foreground dark:text-foreground focus:outline-none"
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
          :href="route(`${commentableType}.comment.delete`, [commentable.id, comment.id])"
          class="order-3 focus:outline-none"
        >
          <icon
            name="trash"
            class="w-4 h-4 text-foreground hover:text-error-400 dark:text-foreground dark:hover:text-error-500"
          />
        </inertia-link>
      </div>
    </div>

    <!-- Comments Input Box -->
    <div
      v-if="$page.props.auth.user"
      class="flex my-2"
    >
      <img
        :src="$page.props.auth.user.profile_photo_url"
        alt="My profile"
        class="order-1 w-8 h-8 rounded-full"
      >
      <div class="flex-grow order-2 mx-2 text-sm">
        <form @submit.prevent="submitComment">
          <Input
            ref="comment"
            v-model="commentBody"
            :disabled="submitting"
            :placeholder="__('Write a comment...')"
            aria-label="comment"
            type="text"
            @keypress.enter="submitComment"
          />
        </form>
        <span
          v-if="bodyerror"
          class="ml-2 text-xs text-error-500"
        >{{ bodyerror }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { Input } from '@/Components/ui/input';

export default {
    components: {Icon, UserDisplayname, Input},

    props: {
        commentable: Object,
        commentableType: String
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
        axios.get(route(`${this.commentableType}.comment.index`, this.commentable.id)).then(response => {
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
            axios.post(route(`${this.commentableType}.comment.store`, this.commentable.id), {
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
