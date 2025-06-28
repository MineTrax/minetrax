<template>
    <div class="space-y-4 w-full">
        <Card v-if="$page.props.auth.user && !username">
            <CardContent class="p-2">
                <form class="text-xl flex flex-col items-end space-y-2" @submit.prevent="submitPost">
                    <Textarea ref="post-content" v-model="body" aria-label="post content"
                        :placeholder="bodyPlaceholderText" name="post" rows="3" @keypress.enter.shift="submitPost"
                        @input="resizeTextArea" />

                    <div v-if="imagesDisplay.length" class="grid gap-1 grid-cols-3 w-full">
                        <div v-for="(img, index) in imagesDisplay" :key="index" class="relative">
                            <button type="button"
                                class="m-1 absolute top-0 left-0 bg-black bg-opacity-75 rounded-full cursor-pointer hover:bg-opacity-100 text-white"
                                @click="removeMedia(index)">
                                <icon name="close" class="p-1 text-foreground dark:text-foreground" />
                            </button>
                            <img :src="img.url" alt="Attachment" class="object-cover rounded-xl h-48" />
                        </div>
                    </div>

                    <div class="flex justify-between w-full items-center">
                        <input ref="file_selector" name="images" type="file" class="hidden" multiple accept="image/*"
                            @change="handleImageChangeListener" />
                        <button v-tippy :title="__('Add Media')" type="button"
                            class="inline-flex items-center justify-center h-10 w-10 hover:bg-background rounded-full focus:outline-none"
                            @click="openImageSelector">
                            <icon name="photograph" class="h-6 w-6 text-primary" />
                        </button>
                        <span><jet-input-error :message="bodyerror" class="mt-2" /></span>
                        <LoadingButton :loading="postsubmitting" type="submit">
                            {{ __("Post") }}
                        </LoadingButton>
                    </div>
                </form>
            </CardContent>
        </Card>

        <div v-if="loading" class="space-y-4">
            <Card>
                <CardContent class="px-5 py-4">
                    <div class="flex space-x-4">
                        <Skeleton class="rounded-full h-12 w-12" />
                        <div class="flex-1 space-y-4 py-1">
                            <Skeleton class="h-4 rounded w-3/4" />
                            <div class="space-y-2">
                                <Skeleton class="h-4 rounded" />
                                <Skeleton class="h-4 rounded w-5/6" />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="px-5 py-4">
                    <div class="flex space-x-4">
                        <Skeleton class="rounded-full h-12 w-12" />
                        <div class="flex-1 space-y-4 py-1">
                            <Skeleton class="h-4 rounded w-3/4" />
                            <div class="space-y-2">
                                <Skeleton class="h-4 rounded" />
                                <Skeleton class="h-4 rounded w-5/6" />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <InfiniteScroll :load-more="loadMorePosts">
            <transition-group name="list" tag="div" class="space-y-4">
                <Post v-for="post in posts.data" :key="post.id" :post="post" />
                <Card v-if="!loading && showEmptyPost && posts.data.length <= 0" :key="999999999"
                    class="flex items-center justify-center italic p-4">
                    {{
                        __(":username hasn't posted anything yet.", {
                            username: username,
                        })
                    }}
                </Card>
            </transition-group>
        </InfiniteScroll>
    </div>
</template>

<script>
import InfiniteScroll from "@/Components/InfiniteScroll.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import Post from "@/Components/Post.vue";
import { sample } from "lodash/collection";
import Icon from "@/Components/Icon.vue";
import { Card, CardContent } from "@/Components/ui/card";
import { Textarea } from "@/Components/ui/textarea";
import { Skeleton } from "@/Components/ui/skeleton";

export default {
    components: {
        Icon,
        LoadingButton,
        InfiniteScroll,
        JetInputError,
        Post,
        Card,
        CardContent,
        Textarea,
        Skeleton,
    },

    props: {
        username: {
            type: [String],
            default: null,
        },
        showEmptyPost: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            loading: true,
            posts: {},
            body: "",
            bodyerror: null,
            postsubmitting: false,
            bodyPlaceholderText: sample([
                this.__("Whats up"),
                this.__("Whats happening?"),
                this.__("Having fun? Tell us about it."),
                this.__("Are you alex or steve?"),
                this.__("Have you seen Herobrine?"),
            ]),
            imagesDisplay: [],
        };
    },

    created() {
        let routeToHit = route("post.index");
        if (this.username) {
            routeToHit = route("post.user.index", this.username);
        }
        axios
            .get(routeToHit)
            .then((response) => {
                this.posts = response.data;
            })
            .finally((e) => {
                this.loading = false;
            });
    },

    methods: {
        loadMorePosts() {
            if (!this.posts.next_page_url) {
                return Promise.resolve();
            }

            return axios(this.posts.next_page_url).then((response) => {
                this.posts = {
                    ...response.data,
                    data: [...this.posts.data, ...response.data.data],
                };
            });
        },

        submitPost() {
            // Prevents accidental double posting
            if (this.postsubmitting) return;

            this.postsubmitting = true;
            this.bodyerror = null;

            let formData = new FormData();
            formData.append("body", this.body);

            for (let i = 0; i < this.imagesDisplay.length; i++) {
                formData.append("media[" + i + "]", this.imagesDisplay[i].file);
            }

            axios
                .post(route("post.store"), formData)
                .then((response) => {
                    if (response.status === 200) {
                        this.posts.data.unshift(response.data.data);
                        // Set Body to empty and resize the textarea to initial
                        this.body = "";
                        this.imagesDisplay = [];
                        const textarea = this.$refs["post-content"];
                        textarea.style.height = "initial";
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.bodyerror = Object.values(
                            error.response.data.errors
                        )[0][0];
                    } else {
                        this.bodyerror = error.response.data.message;
                    }
                })
                .finally(() => (this.postsubmitting = false));
        },

        resizeTextArea() {
            const textarea = this.$refs["post-content"];
            textarea.style.height = "initial";
            textarea.style.height = `${textarea.scrollHeight}px`;
        },

        openImageSelector() {
            this.$refs.file_selector.click();
        },

        handleImageChangeListener(ev) {
            const images = ev.target.files;

            Array.from(images).forEach((img) => {
                let reader = new FileReader();
                reader.readAsDataURL(img);

                reader.onload = (e) => {
                    this.imagesDisplay.push({
                        url: e.target.result,
                        file: img,
                    });
                };
            });
        },

        removeMedia(index) {
            this.imagesDisplay.splice(index, 1);
        },
    },
};
</script>

<style scoped>
.list-item {
    display: inline-block;
    margin-right: 10px;
}

.list-enter-active,
.list-leave-active {
    transition: all 1s;
}

.list-enter-from,
.list-leave-to

/* .list-leave-active below version 2.1.8 */
    {
    opacity: 0;
    transform: translateY(30px);
}
</style>
