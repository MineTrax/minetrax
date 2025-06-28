<template>
    <Card class="transition-all duration-200 hover:shadow-md">
    <CardHeader class="pb-3">
      <!-- Header -->
      <div class="flex justify-between">
        <div class="flex">
          <img
            class="w-12 h-12 rounded-full ring-2 ring-border"
            :src="post.user.profile_photo_url"
            alt="profile photo"
          >
          <div class="ml-3 mt-0.5">
            <inertia-link
              class="cursor-pointer hover:underline text-card-foreground transition-colors duration-200"
              as="a"
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
                class="text-sm font-light leading-snug text-muted-foreground hover:text-primary focus:outline-none transition-colors duration-200"
              >
                {{ formatTimeAgoToNow(post.created_at) }}
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
          class="text-muted-foreground hover:text-destructive focus:outline-none"
        >
          <icon
            name="trash"
            class="h-5 w-5"
          />
        </inertia-link>
      </div>
    </CardHeader>

    <CardContent class="pt-0">
      <!-- Body -->
      <p
        class="leading-relaxed text-card-foreground break-words whitespace-pre-line"
        v-html="purifyAndLinkifyText(post.body)"
      />

      <!-- Media -->
      <div
        v-if="post.media_url_array.length"
        class="mt-4 grid gap-3"
        :class="{'grid-cols-2': post.media_url_array.length > 1}"
      >
        <div
          v-for="(url) in post.media_url_array"
          :key="url"
          class="relative overflow-hidden rounded-lg border"
        >
          <img
            :src="url"
            alt="Attachment"
            class="object-cover w-full h-full transition-transform duration-200 hover:scale-105"
            :class="post.media_url_array.length > 1 ? 'max-h-56': 'max-h-96'"
          >
        </div>
      </div>
    </CardContent>

    <CardFooter class="pt-0 justify-end">
      <!-- Footer Buttons -->
      <div class="flex items-center space-x-6 text-muted-foreground">
        <button
          v-if="!liked"
          class="flex items-center cursor-pointer group"
          @click="likePost"
        >
          <span
            class="group-hover:bg-pink-50 dark:group-hover:bg-pink-950/30 group-hover:text-pink-500 p-2 rounded-full transition-all duration-200"
          >
            <icon
              name="heart-hollow"
              class="h-5 w-5"
            />
          </span>
          <span
            class="ml-1 text-sm font-medium group-hover:text-pink-500 transition-colors duration-200"
          >{{ likes_count }}</span>
        </button>
        <button
          v-else
          class="flex items-center text-pink-500 cursor-pointer group"
          @click="unlikePost"
        >
          <span
            class="group-hover:bg-pink-50 dark:group-hover:bg-pink-950/30 p-2 rounded-full transition-all duration-200"
          >
            <icon
              name="heart-fill"
              class="w-5 h-5"
            />
          </span>
          <span
            class="ml-1 text-sm font-medium transition-colors duration-200"
          >{{ likes_count }}</span>
        </button>
        <button
          class="flex items-center cursor-pointer group"
          @click="showComments=!showComments"
        >
          <span
            class="group-hover:bg-primary/10 group-hover:text-primary p-2 rounded-full transition-all duration-200"
          >
            <icon
              name="comment"
              class="h-5 w-5"
            />
          </span>
          <span
            class="ml-1 text-sm font-medium group-hover:text-primary transition-colors duration-200"
          >{{ post.comments_count }}</span>
        </button>
      </div>
    </CardFooter>

    <comments
      v-if="showComments"
      :commentable="post"
      commentable-type="post"
    />
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Card, CardHeader, CardContent, CardFooter } from '@/Components/ui/card';
import Icon from '@/Components/Icon.vue';
import Comments from '@/Components/Comments.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { cn } from '@/lib/utils';

// Props
const props = defineProps({
  post: {
    type: Object,
    required: true
  },
  commentsSectionOpened: {
    type: Boolean,
    default: false
  }
});

// Composables
const { purifyAndLinkifyText, formatTimeAgoToNow, formatToDayDateString } = useHelpers();
const page = usePage();

// Reactive state
const liked = ref(props.post.love_reactant?.reactions?.length > 0);
const likes_count = ref(props.post?.love_reactant?.reaction_total?.count ?? 0);
const showComments = ref(props.commentsSectionOpened);

// Methods
const likePost = () => {
  if (!page.props.auth.user) {
    router.get(route('login'));
    return;
  }

  liked.value = true;
  likes_count.value++;

  axios.post(route('reaction.post.like', props.post.id))
    .then(() => {
      // Success handled by optimistic update
    })
    .catch(() => {
      // Revert on error
      liked.value = false;
      likes_count.value--;
    });
};

const unlikePost = () => {
  if (!page.props.auth.user) {
    router.get(route('login'));
    return;
  }

  liked.value = false;
  likes_count.value--;

  axios.post(route('reaction.post.unlike', props.post.id))
    .then(() => {
      // Success handled by optimistic update
    })
    .catch(() => {
      // Revert on error
      liked.value = true;
      likes_count.value++;
    });
};
</script>
