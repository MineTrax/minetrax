<template>
  <div
    class="px-5 py-4 bg-white dark:bg-cool-gray-800 shadow rounded"
    :class="{ 'rounded-l-none border-l-4 border-light-blue-500': $page.props.user && $page.props.user.id === post.user.id }"
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
            <span
              class="text-black font-bold leading-snug text-black dark:text-gray-100"
              :style="[post.user.roles[0].color ? {color: post.user.roles[0].color} : null]"
            > {{
              post.user.name
            }}

            </span> <span class="text-gray-500 dark:text-gray-300"> @{{ post.user.username }}</span>
            <icon
              v-if="post.user.verified_at"
              v-tippy
              name="verified-check-fill"
              :title="__('Verified Account')"
              class="mb-1 focus:outline-none h-6 w-6 fill-current inline text-light-blue-400"
            />
            <icon
              v-if="post.user.is_staff"
              v-tippy
              name="shield-check-fill"
              :title="__('Staff Member')"
              class="mb-1 focus:outline-none h-6 w-6 fill-current inline text-pink-400"
            />
          </inertia-link>
          <div class="flex">
            <inertia-link
              v-tippy
              as="a"
              :href="route('post.show', post.id)"
              :content="formatToDayDateString(post.created_at)"
              class="focus:outline-none hover:text-light-blue-500 text-sm text-gray-500 dark:text-gray-300 font-light leading-snug"
            >
              {{
                formatTimeAgoToNow(post.created_at)
              }}
            </inertia-link>
          </div>
        </div>
      </div>
      <inertia-link
        v-if="$page.props.user && post.permissions.delete"
        v-confirm="{message: __('Are you sure you want to delete this Post?')}"
        v-tippy
        :title="__('Delete Post')"
        :preserve-scroll="true"
        :preserve-state="false"
        as="button"
        method="delete"
        :href="route('post.delete', post.id)"
        class="text-gray-500 hover:text-red-500 rounded-full flex items-start focus:outline-none"
      >
        <icon
          name="trash"
          class="h-6 w-6 p-0.5"
        />
      </inertia-link>
    </div>
    <!-- Body -->
    <p
      class="text-gray-800 dark:text-gray-200 mt-2 leading-snug md:leading-normal whitespace-pre-line break-words"
      v-html="purifyAndLinkifyText(post.body)"
    />

    <div
      v-if="post.media_url_array.length"
      class="grid gap-2"
      :class="{'grid-cols-2': post.media_url_array.length > 1}"
    >
      <div
        v-for="(url) in post.media_url_array"
        class="relative"
      >
        <img
          :src="url"
          alt="Attachment"
          class="object-cover rounded-xl h-full w-full"
          :class="post.media_url_array.length > 1 ? 'max-h-56': 'max-h-96'"
        >
      </div>
    </div>

    <!-- Footer Buttons -->
    <div class="flex space-x-10 justify-end items-center mt-5 text-gray-500">
      <button
        v-if="!liked"
        class="flex group cursor-pointer focus:outline-none"
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
        class="flex text-pink-500 group cursor-pointer focus:outline-none"
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
        class="flex group cursor-pointer focus:outline-none"
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

export default {
    components: {Comments, Icon},
    props: {
        post: Object,
        commentsSectionOpened: {
            type: Boolean,
            default: false
        }
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
            if (!this.$page.props.user) {
                this.$inertia.get(route('login'));
                return;
            }

            this.liked = true;
            this.likes_count++;
            axios.post(route('reaction.post.like', this.post.id)).then(response => {
            }).catch(err => {
                this.liked = false;
                this.likes_count--;
            });
        },
        unlikePost() {
            if (!this.$page.props.user) {
                this.$inertia.get(route('login'));
                return;
            }

            this.liked = false;
            this.likes_count--;
            axios.post(route('reaction.post.unlike', this.post.id)).then(response => {
            }).catch(err => {
                this.liked = true;
                this.likes_count++;
            });
        }
    }
};
</script>
