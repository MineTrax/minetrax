<script setup>
import { onMounted, onUnmounted, ref, nextTick, watch } from 'vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import { PaperAirplaneIcon, ShieldCheckIcon } from '@heroicons/vue/24/solid';
import { useHelpers } from '@/Composables/useHelpers';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import Icon from '@/Components/Icon.vue';
import RecruitmentStatusBadge from './RecruitmentStatusBadge.vue';

const { purifyAndLinkifyText, formatTimeAgoToNow, formatToDayDateString } =
    useHelpers();
const { can } = useAuthorizable();

const props = defineProps({
    submission: {
        type: Object,
        required: true,
    },
    forAdmin: {
        type: Boolean,
        default: false,
    },
});

const currentMessage = ref('');
let messages = ref([]);
let messageQueryInterval = ref(null);
let isLoading = ref(true);
let isSendingMessage = ref(false);
let error = ref(null);
const inputbox = ref(null);
const indexRoute = props.forAdmin
    ? route('admin.recruitment-submission.message.index', {
        submission: props.submission.id,
    })
    : route('recruitment-submission.message.index', {
        recruitment: props.submission.recruitment.slug,
        submission: props.submission.id,
    });
const postRoute = props.forAdmin
    ? route('admin.recruitment-submission.message.store', {
        submission: props.submission.id,
    })
    : route('recruitment-submission.message.store', {
        recruitment: props.submission.recruitment.slug,
        submission: props.submission.id,
    });

const scrollToBottom = () => {
    nextTick(() => {
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth',
        });
    });
};
watch(messages, () => {
    scrollToBottom();
});

onMounted(async () => {
    isLoading.value = true;
    axios
        .get(indexRoute)
        .then((data) => {
            messages.value = data.data;
        })
        .finally(() => {
            isLoading.value = false;
            scrollToBottom();
        });

    messageQueryInterval.value = setInterval(
        () => pollServerForNewMessages(),
        5000
    );
});

onUnmounted(() => {
    clearInterval(messageQueryInterval.value);
});

const sendMessage = (type = null) => {
    if (isSendingMessage.value) return;

    isSendingMessage.value = true;
    error.value = null;
    axios
        .post(postRoute, {
            message: currentMessage.value,
            type: type,
        })
        .then((data) => {
            if (data.status === 200) {
                messages.value.push(data.data.data);
                scrollToBottom();
            }
        })
        .catch((e) => {
            error.value =
                e.response?.data?.message ||
                e.message ||
                'Something went wrong.';
        })
        .finally(() => {
            currentMessage.value = '';
            isSendingMessage.value = false;
            nextTick(() => {
                inputbox.value.focus();
            });
        });
};

const pollServerForNewMessages = () => {
    const afterId =
        messages.value.length > 0
            ? messages.value[messages.value.length - 1].id
            : 0;

    const indexRouteWithAfter = props.forAdmin
        ? route('admin.recruitment-submission.message.index', {
            submission: props.submission.id,
            after: afterId,
        })
        : route('recruitment-submission.message.index', {
            recruitment: props.submission.recruitment.slug,
            submission: props.submission.id,
            after: afterId,
        });

    axios.get(indexRouteWithAfter).then((data) => {
        const newMessages = data.data;
        if (newMessages.length > 0) {
            messages.value = [...messages.value, ...newMessages];
        }
    });
};
</script>

<template>
  <div class="flex flex-col p-3 space-y-4">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
      {{ __("Messages Box") }}
    </h3>

    <span
      v-if="
        forAdmin && !submission.recruitment.is_allow_messages_from_users
      "
      class="p-1.5 text-sm text-red-500 bg-red-100 border border-red-500 rounded dark:text-red-400 dark:bg-red-200 dark:bg-opacity-10"
    >
      {{
        __(
          "Messages feature is disabled for this recruitment. Applicant can't send messages."
        )
      }}
    </span>

    <div
      v-if="isLoading"
      class="flex items-center justify-center p-4 min-h-64"
    >
      <LoadingSpinner :loading="isLoading" />
    </div>

    <div
      id="messagesContainer"
      class="overflow-y-auto min-h-64 max-h-96"
    >
      <div
        v-if="!isLoading && messages && messages.length === 0"
        class="flex justify-center pt-4 text-sm text-gray-500 dark:text-gray-400"
      >
        {{ __("No messages found.") }}
      </div>

      <div
        v-if="!isLoading && messages && messages.length > 0"
        class="flex flex-col mt-3 mr-4 space-y-2"
      >
        <div
          v-for="comment in messages"
          :key="comment.id"
          class="flex space-y-2"
        >
          <template
            v-if="comment.type.value === 'recruitment_action'"
          >
            <div
              class="italic text-sm text-gray-700 dark:text-gray-300"
            >
              <InertiaLink
                as="span"
                class="hover:cursor-pointer inline-block"
                :href="
                  route(
                    'user.public.get',
                    comment.commentator.username
                  )
                "
              >
                <UserDisplayname
                  :user="comment.commentator"
                  :show-username="false"
                  :show-badges="false"
                  text-class="font-sm"
                />
              </InertiaLink>
              <span class="mr-1">
                {{ __(" changed application status to ") }}
              </span>
              <RecruitmentStatusBadge :status="comment.comment" />
              <span
                v-tippy
                class="inline ml-1 text-xs text-gray-500 dark:text-gray-400 focus:outline-none"
                :title="
                  formatToDayDateString(comment.created_at)
                "
              >
                {{ formatTimeAgoToNow(comment.created_at) }}
              </span>
            </div>
          </template>
          <template v-else>
            <img
              :src="comment.commentator.profile_photo_url"
              alt="My profile"
              class="w-8 h-8 mt-2 rounded-full"
              :class="{
                'order-4': comment.type.value === 'recruitment_applicant_message'
              }"
            >
            <div
              v-if="
                comment.type.value ===
                  'recruitment_staff_whisper'
              "
              v-tippy
              class="flex mt-3.5 ml-1"
              :class="{
                'order-3': comment.type.value === 'recruitment_applicant_message'
              }"
              :title="
                __(
                  'This is a whisper message only visible to staff'
                )
              "
            >
              <ShieldCheckIcon
                class="w-5 h-5 text-yellow-400 dark:text-yellow-300"
              />
            </div>
            <div
              class="items-start w-full mx-2 space-y-2 text-sm"
              :class="{
                'order-2 text-right': comment.type.value === 'recruitment_applicant_message'
              }"
            >
              <div
                class="flex flex-col px-4 py-2 text-gray-700 rounded-2xl dark:bg-opacity-25 dark:text-gray-200"
                :class="
                  {
                    'bg-orange-100 dark:bg-yellow-300': comment.type.value === 'recruitment_staff_whisper',
                    'bg-gray-200 dark:bg-cool-gray-500': comment.type.value === 'recruitment_staff_message',
                    'bg-gray-100 dark:bg-cool-gray-600': comment.type.value === 'recruitment_applicant_message'
                  }"
              >
                <InertiaLink
                  as="div"
                  class="hover:cursor-pointer hover:underline"
                  :href="
                    route(
                      'user.public.get',
                      comment.commentator.username
                    )
                  "
                >
                  <UserDisplayname
                    :user="comment.commentator"
                    :show-username="true"
                    text-class="font-sm"
                  >
                    <span
                      v-tippy
                      class="inline ml-1 text-xs text-gray-500 dark:text-gray-400 focus:outline-none"
                      :title="
                        formatToDayDateString(
                          comment.created_at
                        )
                      "
                    >
                      {{
                        formatTimeAgoToNow(
                          comment.created_at
                        )
                      }}
                    </span>
                  </UserDisplayname>
                </InertiaLink>
                <p
                  class="leading-snug break-words whitespace-pre-line md:leading-normal"
                  v-html="
                    purifyAndLinkifyText(comment.comment)
                  "
                />
              </div>
            </div>
            <InertiaLink
              v-if="
                can('delete recruitment_submission_messages') &&
                  forAdmin
              "
              v-confirm="{
                message: __(
                  'Are you sure you want to delete this comment?'
                ),
              }"
              :preserve-scroll="true"
              :preserve-state="false"
              as="button"
              method="delete"
              :href="
                route(
                  'admin.recruitment-submission.message.delete',
                  [submission.id, comment.id]
                )
              "
              class="focus:outline-none"
              :class="{
                'order-1': comment.type.value === 'recruitment_applicant_message'
              }"
            >
              <Icon
                name="trash"
                class="w-4 h-4 text-gray-200 hover:text-red-400 dark:text-gray-500 dark:hover:text-red-500"
              />
            </InertiaLink>
          </template>
        </div>
      </div>
    </div>

    <div
      id="send-message"
      class="mt-4"
    >
      <!-- Comment input textarea with two buttons -->
      <XTextarea
        ref="inputbox"
        v-model="currentMessage"
        :disabled="
          !submission.i_can_send_message
        "
        label="Write your message here..."
        class="w-full"
        :error="error"
      />
      <div class="flex justify-end mt-2">
        <LoadingButton
          v-if="forAdmin"
          v-tippy
          :disabled="!submission.i_can_send_message"
          :title="
            __(
              'Send whisper to other staff members. This message will be private and only visible to staff members.'
            )
          "
          class="px-4 py-2 mr-2 font-bold text-yellow-500 bg-transparent rounded hover:text-yellow-400 dark:text-white disabled:cursor-not-allowed disabled:opacity-25"
          :loading="isSendingMessage"
          @click="sendMessage('recruitment_staff_whisper')"
        >
          <ShieldCheckIcon
            v-if="!isSendingMessage"
            class="w-5 h-5"
          />
        </LoadingButton>
        <LoadingButton
          :disabled="!submission.i_can_send_message"
          class="px-4 py-2 text-white rounded bg-sky-500 hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-25"
          :loading="isSendingMessage"
          @click="
            forAdmin
              ? sendMessage('recruitment_staff_message')
              : sendMessage()
          "
        >
          <PaperAirplaneIcon
            v-if="!isSendingMessage"
            class="w-5 h-5"
          />
        </LoadingButton>
      </div>
    </div>
  </div>
</template>
