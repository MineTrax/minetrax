<template>
  <div class="flex flex-col">
    <hr class="mt-0.5 dark:border-cool-gray-700">

    <!-- Loading Spinner -->
    <div
      v-if="loading || loadingMore"
      class="flex p-4 justify-center"
    >
      <svg
        class="animate-spin -ml-1 mr-3 h-5 w-5 text-light-blue-600 dark:text-light-blue-400"
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
        class="text-sm text-gray-500 dark:text-gray-400 font-semibold focus:outline-none hover:underline"
        @click="loadMoreComments"
      >
        View previous
        comments
      </button>
    </div>

    <!-- User Comments -->
    <div
      v-if="!loading && comments"
      class="flex mt-3 flex-col space-y-2"
    >
      <div
        v-for="comment in comments.data"
        :key="comment.id"
        class="flex"
      >
        <div class="space-y-2 text-sm max-w-lg mx-2 order-2 items-start">
          <div
            class="px-4 py-2 flex flex-col rounded-2xl inline-block rounded-tl-lg bg-gray-100 text-gray-700 dark:bg-cool-gray-600 dark:bg-opacity-25 dark:text-gray-200"
            :class="{'border border-gray-300 dark:border-gray-700': $page.props.user && $page.props.user.id === comment.user_id}"
          >
            <inertia-link
              as="div"
              :href="route('user.public.get', comment.commentator.username)"
            >
              <span
                class="cursor-pointer font-semibold text-gray-800"
                :style="[comment.commentator.roles[0].color ? {color: comment.commentator.roles[0].color} : null]"
              >{{
                comment.commentator.name
              }} </span>
              <span class="cursor-pointer text-sm">@{{ comment.commentator.username }}</span>
              <span
                v-tippy
                class="ml-1 text-gray-500 dark:text-gray-400 text-xs focus:outline-none"
                :title="format(new Date(comment.created_at), 'E, do MMM yyyy, h:mm aaa')"
              >
                {{ formatDistanceToNowStrict(new Date(comment.created_at), {addSuffix: true}) }}
              </span>
              <icon
                v-if="comment.commentator.verified_at"
                v-tippy
                name="verified-check-fill"
                title="Verified Account"
                class="mb-1 focus:outline-none h-5 w-5 fill-current inline text-light-blue-400"
              />
              <icon
                v-if="comment.commentator.is_staff"
                v-tippy
                name="shield-check-fill"
                title="Staff Member"
                class="mb-1 focus:outline-none h-5 w-5 fill-current inline text-pink-400"
              />
            </inertia-link>
            <span v-html="purifyAndLinkifyText(comment.comment)" />
          </div>
        </div>
        <img
          :src="comment.commentator.profile_photo_url"
          alt="My profile"
          class="w-8 h-8 rounded-full order-1 mt-2"
        >
        <inertia-link
          v-if="$page.props.user && comment.permissions.delete"
          :preserve-scroll="true"
          :preserve-state="false"
          as="button"
          method="delete"
          :href="route('post.comment.delete', [post.id, comment.id])"
          class="focus:outline-none order-3"
        >
          <icon
            name="trash"
            class="text-gray-200 hover:text-red-400 dark:text-gray-500 dark:hover:text-red-500 w-4 h-4"
          />
        </inertia-link>
      </div>
    </div>

    <!-- Comments Input Box -->
    <div
      v-if="$page.props.user"
      class="flex mt-1"
    >
      <img
        :src="$page.props.user.profile_photo_url"
        alt="My profile"
        class="w-8 h-8 rounded-full order-1 mt-2"
      >
      <div class="text-sm mx-2 order-2 flex-grow">
        <form @submit.prevent="submitComment">
          <input
            ref="comment"
            v-model="commentBody"
            :disabled="submitting"
            placeholder="Write a comment..."
            aria-label="comment"
            type="text"
            class="mt-1 border border-gray-100 bg-gray-100 dark:bg-cool-gray-900 focus:border-gray-300 dark:border-gray-800 dark:focus:border-gray-700 dark:text-gray-200 focus:ring-0 block w-full sm:text-sm rounded-full disabled:opacity-50"
            @keypress.enter="submitComment"
          >
        </form>
        <span
          v-if="bodyerror"
          class="text-xs ml-2 text-red-500"
        >{{ bodyerror }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon';
import {formatDistanceToNowStrict, format} from 'date-fns';

export default {
    components: {Icon},

    props: {
        post: Object
    },

    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            format: format,
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
        }).finally(e => {
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
            }).finally(x => this.loadingMore = false);
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
