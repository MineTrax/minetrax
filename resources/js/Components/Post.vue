<template>
  <div
    class="px-5 py-4 bg-white rounded shadow dark:bg-cool-gray-800"
    :class="{ 'rounded-l-none border-l-4 border-light-blue-500': $page.props.auth.user && $page.props.auth.user.id === post.user.id }"
  >
    <!-- Header -->
    <div class="flex justify-between">
      <div class="flex">
        <img
          class="w-12 h-12 rounded-full"
          :src="post.user.profile_photo_url"
          alt="profile photo"
        >
        <div class="ml-2 mt-0.5">
          <inertia-link
            class="cursor-pointer hover:underline dark:text-gray-300"
            as="div"
            :href="route('user.public.get', post.user.username)"
          >
            <user-displayname
              :user="post.user"
              :show-username="true"
            />
          </inertia-link>
          <div class="flex">
            <inertia-link
              v-tippy
              as="a"
              :href="route('post.show', post.id)"
              :content="formatToDayDateString(post.created_at)"
              class="text-sm font-light leading-snug text-gray-500 dark:hover:text-light-blue-500 focus:outline-none hover:text-light-blue-500 dark:text-gray-300"
            >
              {{
                formatTimeAgoToNow(post.created_at)
              }}
            </inertia-link>
          </div>
        </div>
      </div>
      <inertia-link
        v-if="$page.props.auth.user && post.permissions.delete"
        v-confirm="{message: __('Are you sure you want to delete this Post?')}"
        v-tippy
        :title="__('Delete Post')"
        :preserve-scroll="true"
        :preserve-state="false"
        as="button"
        method="delete"
        :href="route('post.delete', post.id)"
        class="flex items-start text-gray-500 rounded-full hover:text-red-500 focus:outline-none"
      >
        <icon
          name="trash"
          class="h-6 w-6 p-0.5"
        />
      </inertia-link>
    </div>
    <!-- Body -->
    <p
      class="mt-2 leading-snug text-gray-800 break-words whitespace-pre-line dark:text-gray-200 md:leading-normal"
      v-html="purifyAndLinkifyText(post.body)"
    />

    <div
      v-if="post.media_url_array.length"
      class="grid gap-2"
      :class="{'grid-cols-2': post.media_url_array.length > 1}"
    >
      <div
        v-for="(url) in post.media_url_array"
        :key="url"
        class="relative"
      >
        <img
          :src="url"
          alt="Attachment"
          class="object-cover w-full h-full rounded-xl"
          :class="post.media_url_array.length > 1 ? 'max-h-56': 'max-h-96'"
        >
      </div>
    </div>

    <!-- Footer Buttons -->
    <div class="flex items-center justify-end mt-5 space-x-10 text-gray-500">
      <button
        v-if="!liked"
        class="flex cursor-pointer group focus:outline-none"
        @click="likePost"
      >
        <span
          class="group-hover:bg-pink-100 dark:group-hover:bg-cool-gray-900 group-hover:text-pink-500 p-1.5 rounded-full transition duration-300 ease-in-out"
        >
          <icon
            name="heart-hollow"
            class="h-6 w-6 p-0.5"
          />
        </span>
        <span
          class="text-gray-500 dark:text-gray-400  font-light py-1.5 group-hover:text-pink-500 transition duration-300 ease-in-out"
        >{{
          likes_count
        }}</span>
      </button>
      <button
        v-else
        class="flex text-pink-500 cursor-pointer group focus:outline-none"
        @click="unlikePost"
      >
        <span
          class="group-hover:bg-pink-100 dark:group-hover:bg-cool-gray-900 group-hover:text-pink-500 p-1.5 rounded-full transition duration-300 ease-in-out"
        >
          <icon
            name="heart-fill"
            class="w-6 h-6 p-0.5"
          />
        </span>
        <span
          class="text-pink-500 dark:text-gray-400 font-light py-1.5 group-hover:text-pink-500 transition duration-300 ease-in-out"
        >{{
          likes_count
        }}</span>
      </button>
      <button
        class="flex cursor-pointer group focus:outline-none"
        @click="showComments=!showComments"
      >
        <span
          class="group-hover:bg-light-blue-100 dark:group-hover:bg-cool-gray-900 group-hover:text-light-blue-500 p-1.5 rounded-full transition duration-300 ease-in-out"
        >
          <icon
            name="comment"
            class="h-6 w-6 p-0.5"
          />
        </span>
        <span
          class="text-gray-500 dark:text-gray-400 font-light py-1.5 flex items-center group-hover:text-light-blue-500 transition duration-300 ease-in-out"
        >{{ post.comments_count }}</span>
      </button>
    </div>

    <comments
      v-if="showComments"
      :post="post"
    />
  </div>
</template>

<script>

import Icon from '@/Components/Icon.vue';
import Comments from '@/Components/Comments.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    components: {Comments, Icon, UserDisplayname},
    props: {
        post: Object,
        commentsSectionOpened: {
            type: Boolean,
            default: false
        }
    },

    setup() {
        const {purifyAndLinkifyText, formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {purifyAndLinkifyText, formatTimeAgoToNow, formatToDayDateString};
    },

    data() {
        return {
            liked: this.post.love_reactant?.reactions?.length > 0,
            likes_count: this.post?.love_reactant?.reaction_total?.count ?? 0,
            showComments: this.commentsSectionOpened
        };
    },

    methods: {
        likePost() {
            if (!this.$page.props.auth.user) {
                this.$inertia.get(route('login'));
                return;
            }

            this.liked = true;
            this.likes_count++;
            axios.post(route('reaction.post.like', this.post.id)).then(() => {
            }).catch(() => {
                this.liked = false;
                this.likes_count--;
            });
        },
        unlikePost() {
            if (!this.$page.props.auth.user) {
                this.$inertia.get(route('login'));
                return;
            }

            this.liked = false;
            this.likes_count--;
            axios.post(route('reaction.post.unlike', this.post.id)).then(() => {
            }).catch(() => {
                this.liked = true;
                this.likes_count++;
            });
        }
    }
};
</script>
