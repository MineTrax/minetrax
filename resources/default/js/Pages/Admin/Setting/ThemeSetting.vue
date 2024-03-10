<template>
  <AdminLayout>
    <app-head
      :title="__('Theme Settings')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full rounded">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              {{ __("Theme Settings") }}
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <form
                    autocomplete="off"
                    @submit.prevent="saveThemeSetting"
                  >
                    <div class="shadow overflow-hidden sm:rounded-md">
                      <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                          <!-- ColorMode -->
                          <div class="col-span-6 sm:col-span-6">
                            <x-select
                              id="color_mode"
                              v-model="form.color_mode"
                              name="color_mode"
                              :error="form.errors.color_mode"
                              :label="__('Default Color Mode')"
                              :placeholder="__('Select default color mode..')"
                              :disable-null="true"
                              :select-list="{dark: __('Dark'), light: __('Light')}"
                            />
                          </div>

                          <!-- ColorTheme -->
                          <div
                            v-show="false"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="theme_name"
                              v-model="form.theme_name"
                              name="theme_name"
                              :error="form.errors.theme_name"
                              :label="__('Color Theme')"
                              :placeholder="__('Select theme..')"
                              :disable-null="true"
                              :select-list="themeList"
                            />
                          </div>


                          <!-- Primary Font -->
                          <div
                            v-show="false"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="primary_font"
                              v-model="form.primary_font"
                              name="primary_font"
                              :error="form.errors.primary_font"
                              :label="__('Primary Font')"
                              :placeholder="__('Select primary font..')"
                              :disable-null="true"
                              :select-list="fontList"
                            />
                          </div>

                          <!-- Secondary Font -->
                          <div
                            v-show="false"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="secondary_font"
                              v-model="form.secondary_font"
                              name="secondary_font"
                              :error="form.errors.secondary_font"
                              :label="__('Secondary Font')"
                              :placeholder="__('Select secondary font..')"
                              :disable-null="true"
                              :select-list="fontList"
                            />
                          </div>

                          <!-- Home Hero Section -->
                          <div class="flex items-center col-span-6 sm:col-span-6 border-t border-gray-300 dark:border-gray-700 pt-4">
                            <x-checkbox
                              id="enable_home_hero_section"
                              v-model="form.enable_home_hero_section"
                              :label="__('Hero Section at Homepage')"
                              :help="__('Enable hero image section on home page.')"
                              name="enable_home_hero_section"
                              :error="form.errors.enable_home_hero_section"
                            />
                          </div>

                          <!-- Hero Foreground Image Light -->
                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <input
                              id="home_hero_fg_image_light"
                              ref="home_hero_fg_image_light"
                              type="file"
                              accept="image/*"
                              class="hidden"
                              @change="updateHomeHeroFgImageLightPreview"
                            >

                            <label
                              for="home_hero_fg_image_light"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >{{ __("Hero Foreground Image Light") }}</label>

                            <div
                              v-show="!homeHeroFgImageLightPreview"
                              class="mt-2 flex justify-center items-center h-40"
                            >
                              <img
                                v-if="settings.home_hero_fg_image_path_light"
                                :src="settings.home_hero_fg_image_path_light"
                                alt="home_hero_fg_image_light"
                                class="rounded h-40"
                              >
                              <span
                                v-else
                                class="text-gray-400 italic text-xs"
                              >
                                {{ __("No Foreground Image.") }}
                              </span>
                            </div>

                            <div
                              v-show="homeHeroFgImageLightPreview"
                              class="mt-2"
                            >
                              <span
                                class="block rounded h-40"
                                :style="`background-size: contain; background-repeat: no-repeat; background-position: center center; background-image: url(${homeHeroFgImageLightPreview});`"
                              />
                            </div>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="selectHomeHeroFgImageLight"
                            >
                              {{ __("Select A New Image") }}
                            </jet-secondary-button>

                            <div class="mt-2 text-xs text-gray-400">
                              {{ __("Allowed") }}: jpg, jpeg, png, bmp, gif, svg, webp
                            </div>

                            <jet-input-error
                              :message="form.errors.home_hero_fg_image_light"
                              class="mt-2"
                            />
                          </div>


                          <!-- Hero Foreground Image Dark -->
                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <input
                              id="home_hero_fg_image_dark"
                              ref="home_hero_fg_image_dark"
                              type="file"
                              accept="image/*"
                              class="hidden"
                              @change="updateHomeHeroFgImageDarkPreview"
                            >

                            <label
                              for="home_hero_fg_image_dark"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >{{ __("Hero Foreground Image Dark") }}</label>

                            <div
                              v-show="!homeHeroFgImageDarkPreview"
                              class="mt-2 flex justify-center items-center h-40"
                            >
                              <img
                                v-if="settings.home_hero_fg_image_path_dark"
                                :src="settings.home_hero_fg_image_path_dark"
                                alt="home_hero_fg_image_dark"
                                class="rounded h-40 object-center"
                              >
                              <span
                                v-else
                                class="text-gray-400 italic text-xs"
                              >
                                {{ __("No Foreground Image.") }}
                              </span>
                            </div>

                            <div
                              v-show="homeHeroFgImageDarkPreview"
                              class="mt-2"
                            >
                              <span
                                class="block rounded h-40"
                                :style="`background-size: contain; background-repeat: no-repeat; background-position: center center; background-image: url(${homeHeroFgImageDarkPreview});`"
                              />
                            </div>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="selectHomeHeroFgImageDark"
                            >
                              {{ __("Select A New Image") }}
                            </jet-secondary-button>

                            <div class="mt-2 text-xs text-gray-400">
                              {{ __("Allowed") }}: jpg, jpeg, png, bmp, gif, svg, webp
                            </div>

                            <jet-input-error
                              :message="form.errors.home_hero_fg_image_dark"
                              class="mt-2"
                            />
                          </div>


                          <!-- Hero Image Light -->
                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <input
                              id="home_hero_bg_image_light"
                              ref="home_hero_bg_image_light"
                              type="file"
                              accept="image/*, video/*"
                              class="hidden"
                              @change="updateHomeHeroBgImageLightPreview"
                            >

                            <label
                              for="home_hero_bg_image_light"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >{{ __("Hero Background Image Light") }}</label>

                            <div
                              v-show="!homeHeroBgImageLightPreview"
                              class="mt-2"
                            >
                              <img
                                v-if="!isVideoHomeHeroBgImagePathLight"
                                :src="settings.home_hero_bg_image_path_light"
                                alt="home_hero_bg_image_light"
                                class="rounded h-40 object-cover w-full"
                              >
                              <video
                                v-else
                                id="home_hero_bg_image_light_video"
                                class="rounded h-40 object-cover w-full"
                                autoplay
                                loop
                                muted
                              >
                                <source
                                  :src="settings.home_hero_bg_image_path_light"
                                  type="video/webm"
                                >
                              </video>
                            </div>

                            <div
                              v-show="homeHeroBgImageLightPreview"
                              class="mt-2"
                            >
                              <span
                                v-if="!homeHeroBgImageLightPreviewIsVideo"
                                class="block rounded h-40"
                                :style="`background-size: ${form.home_hero_bg_size_css}; background-repeat: ${form.home_hero_bg_repeat_css}; background-position: ${form.home_hero_bg_position_css}; background-image: url(${homeHeroBgImageLightPreview});`"
                              />

                              <div
                                v-else
                                class="h-40 flex items-center justify-center text-gray-400 text-sm italic"
                              >
                                {{ __("Upload preview not available for this type. Please save to see the changes.") }}
                              </div>
                            </div>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="selectHomeHeroBgImageLight"
                            >
                              {{ __("Select A New Image") }}
                            </jet-secondary-button>

                            <div class="mt-2 text-xs text-gray-400">
                              {{ __("Allowed") }}: jpg, jpeg, png, bmp, gif, svg, webp, webm
                            </div>

                            <jet-input-error
                              :message="form.errors.home_hero_bg_image_light"
                              class="mt-2"
                            />
                          </div>


                          <!-- Hero Image Dark -->
                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <input
                              id="home_hero_bg_image_dark"
                              ref="home_hero_bg_image_dark"
                              type="file"
                              accept="image/*, video/*"
                              class="hidden"
                              @change="updateHomeHeroBgImageDarkPreview"
                            >

                            <label
                              for="home_hero_bg_image_dark"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >{{ __("Hero Background Image Dark") }}</label>

                            <div
                              v-show="!homeHeroBgImageDarkPreview"
                              class="mt-2"
                            >
                              <img
                                v-if="!isVideoHomeHeroBgImagePathDark"
                                :src="settings.home_hero_bg_image_path_dark"
                                alt="home_hero_bg_image_dark"
                                class="rounded h-40 object-cover w-full"
                              >
                              <video
                                v-else
                                id="home_hero_bg_image_light_video"
                                class="rounded h-40 object-cover w-full"
                                autoplay
                                loop
                                muted
                              >
                                <source
                                  :src="settings.home_hero_bg_image_path_dark"
                                  type="video/webm"
                                >
                              </video>
                            </div>

                            <div
                              v-show="homeHeroBgImageDarkPreview"
                              class="mt-2"
                            >
                              <span
                                v-if="!homeHeroBgImageDarkPreviewIsVideo"
                                class="block rounded h-40"
                                :style="`background-size: ${form.home_hero_bg_size_css}; background-repeat: ${form.home_hero_bg_repeat_css}; background-position: ${form.home_hero_bg_position_css}; background-image: url(${homeHeroBgImageDarkPreview});`"
                              />

                              <div
                                v-else
                                class="h-40 flex items-center justify-center text-gray-400 text-sm italic"
                              >
                                {{ __("Upload preview not available for this type. Please save to see the changes.") }}
                              </div>
                            </div>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="selectHomeHeroBgImageDark"
                            >
                              {{ __("Select A New Image") }}
                            </jet-secondary-button>


                            <div class="mt-2 text-xs text-gray-400">
                              {{ __("Max Size") }}: 2 MB
                            </div>

                            <jet-input-error
                              :message="form.errors.home_hero_bg_image_dark"
                              class="mt-2"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="home_hero_bg_size_css"
                              v-model="form.home_hero_bg_size_css"
                              name="home_hero_bg_size_css"
                              :error="form.errors.home_hero_bg_size_css"
                              :label="__('Hero background size')"
                              :placeholder="__('Select background size..')"
                              :disable-null="true"
                              :select-list="backgroundSizeList"
                            />
                          </div>


                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="home_hero_bg_position_css"
                              v-model="form.home_hero_bg_position_css"
                              name="home_hero_bg_position_css"
                              :error="form.errors.home_hero_bg_position_css"
                              :label="__('Hero background position')"
                              :placeholder="__('Select background position..')"
                              :disable-null="true"
                              :select-list="backgroundPositionList"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="home_hero_bg_repeat_css"
                              v-model="form.home_hero_bg_repeat_css"
                              name="home_hero_bg_repeat_css"
                              :error="form.errors.home_hero_bg_repeat_css"
                              :label="__('Hero background repeat')"
                              :placeholder="__('Select background repeat..')"
                              :disable-null="true"
                              :select-list="backgroundRepeatList"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-select
                              id="home_hero_bg_attachment_css"
                              v-model="form.home_hero_bg_attachment_css"
                              name="home_hero_bg_attachment_css"
                              :error="form.errors.home_hero_bg_attachment_css"
                              :label="__('Hero background attachment')"
                              :placeholder="__('Select background attachment type..')"
                              :disable-null="true"
                              :select-list="backgroundAttachmentList"
                            />
                          </div>


                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-3 sm:col-span-3"
                          >
                            <x-input
                              id="home_hero_bg_height_css"
                              v-model="form.home_hero_bg_height_css"
                              :label="__('Hero background height')"
                              :error="form.errors.home_hero_bg_height_css"
                              type="text"
                              name="home_hero_bg_height_css"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="flex items-center col-span-3 sm:col-span-3"
                          >
                            <x-checkbox
                              id="show_join_box_in_home_hero"
                              v-model="form.show_join_box_in_home_hero"
                              :label="__('Show Join Box in Hero Section')"
                              :help="__('If enabled, will show server join details like player count & join hostname in hero section. (left side)')"
                              name="show_join_box_in_home_hero"
                              :error="form.errors.show_join_box_in_home_hero"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="flex items-center col-span-3 sm:col-span-3"
                          >
                            <x-checkbox
                              id="show_fg_image_box_in_home_hero"
                              v-model="form.show_fg_image_box_in_home_hero"
                              :label="__('Show Foreground Image Box in Hero Section')"
                              :help="__('If enabled, will show middle foreground image box in hero section (middle).')"
                              name="show_fg_image_box_in_home_hero"
                              :error="form.errors.show_fg_image_box_in_home_hero"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="flex items-center col-span-3 sm:col-span-3"
                          >
                            <x-checkbox
                              id="show_discord_box_in_home_hero"
                              v-model="form.show_discord_box_in_home_hero"
                              :label="__('Show Discord Box in Hero Section')"
                              :help="__('If enabled, will show discord box in hero section (right side). Make sure to add Discord Invite URL & Discord Server ID in General Settings.')"
                              name="show_discord_box_in_home_hero"
                              :error="form.errors.show_discord_box_in_home_hero"
                            />
                          </div>

                          <div
                            v-if="form.enable_home_hero_section"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-textarea
                              id="home_hero_bg_particles"
                              v-model="form.home_hero_bg_particles"
                              :auto-resize="false"
                              :label="__('Particle Effect Options')"
                              :help="__('tsParticles Options json. See https://minetrax.github.io/docs/web/theme-settings for more info. (Set it empty to disable particles effect)')"
                              :error="form.errors.home_hero_bg_particles"
                              name="home_hero_bg_particles"
                            />
                          </div>

                          <!-- Loading Gif -->
                          <div
                            class="col-span-6 sm:col-span-6 border-t border-gray-300 dark:border-gray-700 pt-4"
                          >
                            <input
                              id="loading_gif"
                              ref="loading_gif"
                              type="file"
                              class="hidden"
                              accept="image/gif, image/svg+xml"
                              @change="updateLoadingGifPreview"
                            >

                            <label
                              for="loading_gif"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >{{ __("Animated Loading Image") }}</label>

                            <div
                              v-show="!loadingGifPreview"
                              class="mt-2 flex items-center p-6"
                            >
                              <img
                                v-if="settings.loading_gif"
                                :src="settings.loading_gif"
                                alt="loading_gif"
                                class="h-14 w-14"
                              >
                              <span
                                v-else
                                class="text-gray-400 italic text-xs"
                              >
                                {{ __("No Animated Loading Image.") }}
                              </span>
                            </div>

                            <div
                              v-show="loadingGifPreview"
                              class="mt-2 p-4"
                            >
                              <span
                                class="block rounded h-14 w-14"
                                :style="`background-size: contain; background-repeat: no-repeat; background-position: center center; background-image: url(${loadingGifPreview});`"
                              />
                            </div>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="selectLoadingGif"
                            >
                              {{ __("Select A New Image") }}
                            </jet-secondary-button>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="removeLoadingGif"
                            >
                              {{ __("Remove Image") }}
                            </jet-secondary-button>

                            <div class="mt-2 text-xs text-gray-400">
                              {{ __("Allowed") }}:  gif & svg
                            </div>

                            <jet-input-error
                              :message="form.errors.loading_gif"
                              class="mt-2"
                            />
                          </div>
                        </div>
                      </div>
                      <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                        <loading-button
                          :loading="form.processing"
                          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          {{ __("Save Theme Settings") }}
                        </loading-button>
                      </div>
                    </div>
                  </form>

                  <div
                    v-if="false"
                    class="flex p-5 justify-center items-center text-red-500 italic"
                  >
                    {{ __("Not implemented from here! Theme can be be customized by accessing 'resources' folder.") }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import LoadingButton from '@/Components/LoadingButton.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import XInput from '@/Components/Form/XInput.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XCheckbox,
        XSelect,
        XTextarea,
        LoadingButton,
        XInput,
        JetInputError,
        JetSecondaryButton,
    },
    props: {
        settings: Object,
        themeList: Object,
        fontList: Object,
        isVideoHomeHeroBgImagePathLight: Boolean,
        isVideoHomeHeroBgImagePathDark: Boolean,
    },

    data() {
        return {
            form: useForm({
                color_mode: this.settings.color_mode,
                theme_name: this.settings.theme_name,
                primary_font: this.settings.primary_font,
                secondary_font: this.settings.secondary_font,
                enable_home_hero_section: this.settings.enable_home_hero_section,
                home_hero_bg_size_css: this.settings.home_hero_bg_size_css,
                home_hero_bg_position_css: this.settings.home_hero_bg_position_css,
                home_hero_bg_repeat_css: this.settings.home_hero_bg_repeat_css,
                home_hero_bg_attachment_css: this.settings.home_hero_bg_attachment_css,
                home_hero_bg_height_css: this.settings.home_hero_bg_height_css,
                show_join_box_in_home_hero: this.settings.show_join_box_in_home_hero,
                home_hero_bg_image_light: null,
                home_hero_bg_image_dark: null,
                show_fg_image_box_in_home_hero: this.settings.show_fg_image_box_in_home_hero,
                show_discord_box_in_home_hero: this.settings.show_discord_box_in_home_hero,
                home_hero_bg_particles: this.settings.home_hero_bg_particles,
                home_hero_fg_image_light: null,
                home_hero_fg_image_dark: null,
                loading_gif: null,
                remove_loading_gif: false,
            }),
            homeHeroFgImageLightPreview: null,
            homeHeroFgImageDarkPreview: null,
            homeHeroBgImageLightPreview: null,
            homeHeroBgImageLightPreviewIsVideo: false,
            homeHeroBgImageDarkPreview: null,
            homeHeroBgImageDarkPreviewIsVideo: false,
            loadingGifPreview: null,
            backgroundPositionList: [
                'left top',
                'left center',
                'left bottom',
                'right top',
                'right center',
                'right bottom',
                'center top',
                'center center',
                'center bottom',
            ],
            backgroundRepeatList: [
                'no-repeat',
                'repeat',
                'repeat-x',
                'repeat-y',
                'space',
                'round',
            ],
            backgroundSizeList: [
                'none',
                'fill',
                'auto',
                'contain',
                'cover',
            ],
            backgroundAttachmentList: [
                'scroll',
                'fixed',
                'local',
            ],
        };
    },

    methods: {
        updateHomeHeroBgImageLightPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.homeHeroBgImageLightPreview = e.target.result;

                if (this.$refs.home_hero_bg_image_light.files[0].type.includes('video')) {
                    this.homeHeroBgImageLightPreviewIsVideo = true;
                } else {
                    this.homeHeroBgImageLightPreviewIsVideo = false;
                }
            };

            reader.readAsDataURL(this.$refs.home_hero_bg_image_light.files[0]);
        },
        selectHomeHeroBgImageLight() {
            this.$refs.home_hero_bg_image_light.click();
        },

        updateHomeHeroBgImageDarkPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.homeHeroBgImageDarkPreview = e.target.result;

                if (this.$refs.home_hero_bg_image_dark.files[0].type.includes('video')) {
                    this.homeHeroBgImageDarkPreviewIsVideo = true;
                } else {
                    this.homeHeroBgImageDarkPreviewIsVideo = false;
                }
            };

            reader.readAsDataURL(this.$refs.home_hero_bg_image_dark.files[0]);
        },
        selectHomeHeroBgImageDark() {
            this.$refs.home_hero_bg_image_dark.click();
        },

        updateHomeHeroFgImageDarkPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.homeHeroFgImageDarkPreview = e.target.result;
            };

            reader.readAsDataURL(this.$refs.home_hero_fg_image_dark.files[0]);
        },
        selectHomeHeroFgImageDark() {
            this.$refs.home_hero_fg_image_dark.click();
        },

        updateHomeHeroFgImageLightPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.homeHeroFgImageLightPreview = e.target.result;
            };

            reader.readAsDataURL(this.$refs.home_hero_fg_image_light.files[0]);
        },
        selectHomeHeroFgImageLight() {
            this.$refs.home_hero_fg_image_light.click();
        },

        updateLoadingGifPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.loadingGifPreview = e.target.result;
            };

            reader.readAsDataURL(this.$refs.loading_gif.files[0]);
        },
        selectLoadingGif() {
            this.$refs.loading_gif.click();
        },

        saveThemeSetting() {
            if (this.$refs.home_hero_bg_image_light) {
                this.form.home_hero_bg_image_light = this.$refs.home_hero_bg_image_light.files[0];
            }

            if (this.$refs.home_hero_bg_image_dark) {
                this.form.home_hero_bg_image_dark = this.$refs.home_hero_bg_image_dark.files[0];
            }

            if (this.$refs.home_hero_fg_image_light) {
                this.form.home_hero_fg_image_light = this.$refs.home_hero_fg_image_light.files[0];
            }

            if (this.$refs.home_hero_fg_image_dark) {
                this.form.home_hero_fg_image_dark = this.$refs.home_hero_fg_image_dark.files[0];
            }

            if (this.$refs.loading_gif) {
                this.form.loading_gif = this.$refs.loading_gif.files[0];
            }

            this.form.post(route('admin.setting.theme.update'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.replace(route('admin.setting.theme.show'), {
                        preserveState: false,
                        preserveScroll: true,
                    });
                }
            });
        },

        removeLoadingGif() {
            this.form.reset();
            this.form.loading_gif = null;
            this.form.remove_loading_gif = true;
            this.form.post(route('admin.setting.theme.update'), {
                preserveScroll: true,
                onSuccess: () => (this.loadingGifPreview = null),
            });
        }
    }
};
</script>
