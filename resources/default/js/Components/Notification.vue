<template>
    <div :class="[
        'group relative px-4 py-3 border-l-4 transition-all duration-200 hover:bg-accent/50 cursor-pointer',
        getNotificationBorderColor(),
        notification.read_at || gettingMarkedAsRead ? 'opacity-60' : '',
    ]" @click="markAsRead">
        <inertia-link :href="getNotificationUrl()" class="block">
            <div class="flex items-start gap-3">
                <!-- Priority/Type Indicator Dot -->
                <div :class="[
                    'w-2 h-2 rounded-full mt-2 flex-shrink-0',
                    getNotificationDotColor()
                ]"></div>

                <!-- Notification Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-foreground truncate">
                                {{ getNotificationTitle() }}
                            </h4>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                            <!-- Mark as read button - only visible on hover for unread notifications -->
                            <button type="button" v-if="!notification.read_at" @click.prevent.stop="markAsReadSilently"
                                :class="[
                                    'p-1 rounded-full text-muted-foreground transition-all duration-200',
                                    gettingMarkedAsRead
                                        ? 'opacity-100 cursor-not-allowed'
                                        : 'opacity-0 group-hover:opacity-100 hover:bg-accent hover:text-primary'
                                ]" :disabled="gettingMarkedAsRead"
                                :title="gettingMarkedAsRead ? __('Marking as read...') : __('Mark as read')">
                                <LoaderIcon v-if="gettingMarkedAsRead" class="w-4 h-4 animate-spin" />
                                <CheckCheckIcon v-else class="w-4 h-4" />
                            </button>
                            <span class="text-xs text-muted-foreground">
                                {{ formatTimeAgoToNow(notification.created_at) }}
                            </span>
                            <!-- Unread indicator -->
                            <div v-if="!notification.read_at" class="w-2 h-2 bg-primary rounded-full"></div>
                        </div>
                    </div>
                    <!-- Description spans full width -->
                    <p class="text-sm text-muted-foreground mt-1 line-clamp-2">
                        {{ getNotificationDescription() }}
                    </p>
                </div>
            </div>
        </inertia-link>
    </div>
</template>

<script setup>
import { useHelpers } from '@/Composables/useHelpers'
import { useTranslations } from '@/Composables/useTranslations'
import { CheckCheckIcon, LoaderIcon } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps({
    notification: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['marked-as-read'])

const { formatTimeAgoToNow } = useHelpers()
const { __ } = useTranslations()
const gettingMarkedAsRead = ref(false)

const markAsRead = () => {
    if (!props.notification.read_at) {
        axios.post(route('notification.mark-as-read'), {
            notifications: [props.notification.id],
        }).then(() => {
            emit('marked-as-read', props.notification.id)
        })
    }
}

const markAsReadSilently = (e) => {
    e.preventDefault()
    e.stopPropagation()

    if (!props.notification.read_at && !gettingMarkedAsRead.value) {
        gettingMarkedAsRead.value = true

        // Mark as read silently without navigation
        axios.post(route('notification.mark-as-read'), {
            notifications: [props.notification.id],
        }).then(() => {
            // Update the notification object to reflect read state
            props.notification.read_at = new Date().toISOString()
            emit('marked-as-read', props.notification.id)
        }).catch(e => {
            console.error('Error marking notification as read: ', e)
            // Reset loading state on error
            gettingMarkedAsRead.value = false
        }).finally(() => {
            // Keep loading state briefly for visual feedback
            setTimeout(() => {
                gettingMarkedAsRead.value = false
            }, 300)
        })
    }
}

const getNotificationBorderColor = () => {
    const type = props.notification.type
    const priorities = {
        'App\\Notifications\\UserYouAreBanned': 'border-red-500',
        'App\\Notifications\\UserYouAreMuted': 'border-orange-500',
        'App\\Notifications\\PostCommentedByUser': 'border-blue-500',
        'App\\Notifications\\PostLikedByUser': 'border-green-500',
        'App\\Notifications\\NewsCommentedByUserNotification': 'border-blue-500',
        'App\\Notifications\\CustomFormSubmissionCreatedNotification': 'border-purple-500',
        'App\\Notifications\\RecruitmentSubmissionCreatedNotification': 'border-yellow-500',
        'App\\Notifications\\RecruitmentSubmissionStatusChangedNotification': 'border-indigo-500',
        'App\\Notifications\\RecruitmentSubmissionCommentCreatedNotification': 'border-cyan-500',
    }
    return priorities[type] || 'border-gray-400'
}

const getNotificationDotColor = () => {
    const type = props.notification.type
    const priorities = {
        'App\\Notifications\\UserYouAreBanned': 'bg-red-500',
        'App\\Notifications\\UserYouAreMuted': 'bg-orange-500',
        'App\\Notifications\\PostCommentedByUser': 'bg-blue-500',
        'App\\Notifications\\PostLikedByUser': 'bg-green-500',
        'App\\Notifications\\NewsCommentedByUserNotification': 'bg-blue-500',
        'App\\Notifications\\CustomFormSubmissionCreatedNotification': 'bg-purple-500',
        'App\\Notifications\\RecruitmentSubmissionCreatedNotification': 'bg-yellow-500',
        'App\\Notifications\\RecruitmentSubmissionStatusChangedNotification': 'bg-indigo-500',
        'App\\Notifications\\RecruitmentSubmissionCommentCreatedNotification': 'bg-cyan-500',
    }
    return priorities[type] || 'bg-gray-400'
}

const getNotificationTitle = () => {
    const type = props.notification.type
    const data = props.notification.data

    switch (type) {
        case 'App\\Notifications\\PostCommentedByUser':
            return __('Someone commented on your post')
        case 'App\\Notifications\\PostLikedByUser':
            return __('Someone liked your post')
        case 'App\\Notifications\\UserYouAreMuted':
            return __('You have been muted')
        case 'App\\Notifications\\UserYouAreBanned':
            return __('You have been banned')
        case 'App\\Notifications\\CustomFormSubmissionCreatedNotification':
            return __('New form submission received')
        case 'App\\Notifications\\NewsCommentedByUserNotification':
            return __('New comment on news article')
        case 'App\\Notifications\\RecruitmentSubmissionCreatedNotification':
            return __('New application received')
        case 'App\\Notifications\\RecruitmentSubmissionStatusChangedNotification':
            const status = data.status
            if (status === 'approved') return __('Application approved! 🎉')
            if (status === 'rejected') return __('Application rejected')
            if (status === 'onhold') return __('Application put on hold')
            if (status === 'inprogress') return __('Application being processed')
            if (status === 'withdrawn') return __('Application withdrawn')
            return __('Application status updated')
        case 'App\\Notifications\\RecruitmentSubmissionCommentCreatedNotification':
            return data.for_staff ? __('New message on application') : __('Message received on your application')
        default:
            return __('You have a notification')
    }
}

const getNotificationDescription = () => {
    const type = props.notification.type
    const data = props.notification.data

    switch (type) {
        case 'App\\Notifications\\PostCommentedByUser':
            return __(':user left a comment on your post. Click to view and reply.', { user: data.causer.name })
        case 'App\\Notifications\\PostLikedByUser':
            return __(':user liked your post. Thanks for sharing great content!', { user: data.causer.name })
        case 'App\\Notifications\\UserYouAreMuted':
            return __('You have been muted by :user. You cannot send messages during this period.', { user: data.causer.name })
        case 'App\\Notifications\\UserYouAreBanned':
            return __('You have been banned by :user. Please contact support if you believe this is an error.', { user: data.causer.name })
        case 'App\\Notifications\\CustomFormSubmissionCreatedNotification':
            const submitter = data.causer ? data.causer.name : __('an anonymous user')
            return __('A new form submission has been received from :user. Click to review the details.', { user: submitter })
        case 'App\\Notifications\\NewsCommentedByUserNotification':
            return __(':user commented on a news article. Check out what they had to say!', { user: data.causer.name })
        case 'App\\Notifications\\RecruitmentSubmissionCreatedNotification':
            return __(':user has submitted a new application. Click to review their submission.', { user: data.causer.name })
        case 'App\\Notifications\\RecruitmentSubmissionStatusChangedNotification':
            const status = data.status
            if (status === 'approved') return __('Great news! :user has approved your application. Welcome to the team!', { user: data.causer.name })
            if (status === 'rejected') return __('Unfortunately, :user has rejected your application. Click to view feedback.', { user: data.causer.name })
            if (status === 'onhold') return __(':user has put your application on hold for further review.', { user: data.causer.name })
            if (status === 'inprogress') return __(':user is now reviewing your application. Stay tuned for updates!', { user: data.causer.name })
            if (status === 'withdrawn') return __('The application has been withdrawn by :user.', { user: data.causer.name })
            return __(':user has updated your application status. Click to see the latest changes.', { user: data.causer.name })
        case 'App\\Notifications\\RecruitmentSubmissionCommentCreatedNotification':
            return data.for_staff
                ? __('A new message has been received on an application. Click to view and respond.')
                : __(':user sent you a message regarding your application. Click to read and respond.', { user: data.causer.name })
        default:
            return __('You have received a new notification. Click to view more details.')
    }
}

const getNotificationUrl = () => {
    const type = props.notification.type
    const data = props.notification.data

    switch (type) {
        case 'App\\Notifications\\PostCommentedByUser':
        case 'App\\Notifications\\PostLikedByUser':
            return route('post.show', data.post_id)
        case 'App\\Notifications\\UserYouAreMuted':
        case 'App\\Notifications\\UserYouAreBanned':
            return route('profile.show')
        case 'App\\Notifications\\CustomFormSubmissionCreatedNotification':
            return route('admin.custom-form-submission.show', data.id)
        case 'App\\Notifications\\NewsCommentedByUserNotification':
            return route('news.show', data.news.slug)
        case 'App\\Notifications\\RecruitmentSubmissionCreatedNotification':
            return route('admin.recruitment-submission.show', data.id)
        case 'App\\Notifications\\RecruitmentSubmissionStatusChangedNotification':
            return data.for_staff
                ? route('admin.recruitment-submission.show', data.id)
                : route('recruitment-submission.show', {
                    recruitment: data.recruitment.slug,
                    submission: data.id,
                })
        case 'App\\Notifications\\RecruitmentSubmissionCommentCreatedNotification':
            return data.for_staff
                ? route('admin.recruitment-submission.show', data.submission_id)
                : route('recruitment-submission.show', {
                    recruitment: data.recruitment.slug,
                    submission: data.submission_id,
                })
        default:
            return route('notification.index')
    }
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
