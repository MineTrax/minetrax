<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';

const { __ } = useTranslations();

const props = defineProps({
    settings: Object,
    colorSchemeList: Object,
    fontList: Object,
    isVideoHomeHeroBgImagePathLight: Boolean,
    isVideoHomeHeroBgImagePathDark: Boolean,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Settings'),
        current: false,
    },
    {
        text: __('Theme Settings'),
        current: true,
    }
];

const form = useForm({
    color_mode: props.settings.color_mode,
    color_scheme: props.settings.color_scheme,
    primary_font: props.settings.primary_font,
    enable_home_hero_section: props.settings.enable_home_hero_section,
    home_hero_bg_size_css: props.settings.home_hero_bg_size_css,
    home_hero_bg_position_css: props.settings.home_hero_bg_position_css,
    home_hero_bg_repeat_css: props.settings.home_hero_bg_repeat_css,
    home_hero_bg_attachment_css: props.settings.home_hero_bg_attachment_css,
    home_hero_bg_height_css: props.settings.home_hero_bg_height_css,
    show_join_box_in_home_hero: props.settings.show_join_box_in_home_hero,
    home_hero_bg_image_light: null,
    home_hero_bg_image_dark: null,
    show_fg_image_box_in_home_hero: props.settings.show_fg_image_box_in_home_hero,
    show_discord_box_in_home_hero: props.settings.show_discord_box_in_home_hero,
    home_hero_bg_particles: props.settings.home_hero_bg_particles,
    home_hero_fg_image_light: null,
    home_hero_fg_image_dark: null,
    loading_gif: null,
    remove_loading_gif: false,
});

const backgroundPositionList = [
    'left top',
    'left center',
    'left bottom',
    'right top',
    'right center',
    'right bottom',
    'center top',
    'center center',
    'center bottom',
];

const backgroundRepeatList = [
    'no-repeat',
    'repeat',
    'repeat-x',
    'repeat-y',
    'space',
    'round',
];

const backgroundSizeList = [
    'none',
    'fill',
    'auto',
    'contain',
    'cover',
];

const backgroundAttachmentList = [
    'scroll',
    'fixed',
    'local',
];

function removeLoadingGif() {
    form.reset();
    form.loading_gif = null;
    form.remove_loading_gif = true;
    form.post(route('admin.setting.theme.update'), {
        preserveScroll: true,
    });
}

function saveThemeSetting() {
    form.post(route('admin.setting.theme.update'), {
        preserveScroll: true,
        onSuccess: () => {
            router.get(route('admin.setting.theme.show'), {}, {
                preserveState: false,
                preserveScroll: true,
                replace: true,
            });
        }
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Theme Settings')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form
          autocomplete="off"
          @submit.prevent="saveThemeSetting"
        >
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Theme Basics -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6">
                        <XSelect
                          id="color_mode"
                          v-model="form.color_mode"
                          name="color_mode"
                          :error="form.errors.color_mode"
                          :label="__('Default Color Mode')"
                          :placeholder="__('Select default color mode..')"
                          :disable-null="true"
                          :select-list="{ dark: __('Dark'), light: __('Light') }"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-3">
                        <XSelect
                          id="color_scheme"
                          v-model="form.color_scheme"
                          name="color_scheme"
                          :error="form.errors.color_scheme"
                          :label="__('Color Scheme')"
                          :placeholder="__('Select color scheme..')"
                          :disable-null="true"
                          :select-list="colorSchemeList"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-3">
                        <XSelect
                          id="primary_font"
                          v-model="form.primary_font"
                          name="primary_font"
                          :error="form.errors.primary_font"
                          :label="__('Font Family')"
                          :placeholder="__('Select font family..')"
                          :disable-null="true"
                          :select-list="fontList"
                        />
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Hero Section -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="space-y-6">
                      <XSwitch
                        id="enable_home_hero_section"
                        v-model="form.enable_home_hero_section"
                        :label="__('Hero Section at Homepage')"
                        :help="__('Enable hero image section on home page.')"
                        name="enable_home_hero_section"
                        :error="form.errors.enable_home_hero_section"
                      />

                      <template v-if="form.enable_home_hero_section">
                        <!-- Hero Foreground Images -->
                        <div class="grid grid-cols-6 gap-4">
                          <div class="col-span-6 sm:col-span-3">
                            <ImageUpload
                              id="home_hero_fg_image_light"
                              name="home_hero_fg_image_light"
                              :label="__('Hero Foreground Image Light')"
                              v-model="form.home_hero_fg_image_light"
                              :current-url="settings.home_hero_fg_image_path_light"
                              :error="form.errors.home_hero_fg_image_light"
                              :removable="false"
                              shape="rect"
                              :preview-class="'h-40 w-full'"
                              :upload-label="__('Upload')"
                              :change-label="__('Change')"
                              object-fit="contain"
                              :hint="__('jpg, jpeg, png, bmp, gif, svg, webp')"
                              :max-size-mb="2"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-3">
                            <ImageUpload
                              id="home_hero_fg_image_dark"
                              name="home_hero_fg_image_dark"
                              :label="__('Hero Foreground Image Dark')"
                              v-model="form.home_hero_fg_image_dark"
                              :current-url="settings.home_hero_fg_image_path_dark"
                              :error="form.errors.home_hero_fg_image_dark"
                              :removable="false"
                              shape="rect"
                              :preview-class="'h-40 w-full'"
                              :upload-label="__('Upload')"
                              :change-label="__('Change')"
                              object-fit="contain"
                              :hint="__('jpg, jpeg, png, bmp, gif, svg, webp')"
                              :max-size-mb="2"
                            />
                          </div>
                        </div>

                        <!-- Hero Background Images -->
                        <div class="grid grid-cols-6 gap-4">
                          <div class="col-span-6 sm:col-span-3">
                            <ImageUpload
                              id="home_hero_bg_image_light"
                              name="home_hero_bg_image_light"
                              :label="__('Hero Background Image Light')"
                              v-model="form.home_hero_bg_image_light"
                              :current-url="settings.home_hero_bg_image_path_light"
                              :error="form.errors.home_hero_bg_image_light"
                              :removable="false"
                              shape="rect"
                              :preview-class="'h-40 w-full'"
                              :upload-label="__('Upload')"
                              :change-label="__('Change')"
                              object-fit="cover"
                              :hint="__('jpg, jpeg, png, bmp, gif, svg, webp')"
                              :max-size-mb="2"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-3">
                            <ImageUpload
                              id="home_hero_bg_image_dark"
                              name="home_hero_bg_image_dark"
                              :label="__('Hero Background Image Dark')"
                              v-model="form.home_hero_bg_image_dark"
                              :current-url="settings.home_hero_bg_image_path_dark"
                              :error="form.errors.home_hero_bg_image_dark"
                              :removable="false"
                              shape="rect"
                              :preview-class="'h-40 w-full'"
                              :upload-label="__('Upload')"
                              :change-label="__('Change')"
                              object-fit="cover"
                              :hint="__('Max Size: 2MB')"
                              :max-size-mb="2"
                            />
                          </div>
                        </div>

                        <!-- Background CSS Settings -->
                        <div class="grid grid-cols-6 gap-4">
                          <div class="col-span-6 sm:col-span-3">
                            <XSelect
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

                          <div class="col-span-6 sm:col-span-3">
                            <XSelect
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

                          <div class="col-span-6 sm:col-span-3">
                            <XSelect
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

                          <div class="col-span-6 sm:col-span-3">
                            <XSelect
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

                          <div class="col-span-6 sm:col-span-3">
                            <XInput
                              id="home_hero_bg_height_css"
                              v-model="form.home_hero_bg_height_css"
                              :label="__('Hero background height')"
                              :error="form.errors.home_hero_bg_height_css"
                              type="text"
                              name="home_hero_bg_height_css"
                            />
                          </div>
                        </div>

                        <!-- Hero Section Options -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <XSwitch
                            id="show_join_box_in_home_hero"
                            v-model="form.show_join_box_in_home_hero"
                            :label="__('Show Join Box in Hero Section')"
                            :help="__('If enabled, will show server join details like player count & join hostname in hero section. (left side)')"
                            name="show_join_box_in_home_hero"
                            :error="form.errors.show_join_box_in_home_hero"
                          />

                          <XSwitch
                            id="show_fg_image_box_in_home_hero"
                            v-model="form.show_fg_image_box_in_home_hero"
                            :label="__('Show Foreground Image Box in Hero Section')"
                            :help="__('If enabled, will show middle foreground image box in hero section (middle).')"
                            name="show_fg_image_box_in_home_hero"
                            :error="form.errors.show_fg_image_box_in_home_hero"
                          />

                          <XSwitch
                            id="show_discord_box_in_home_hero"
                            v-model="form.show_discord_box_in_home_hero"
                            :label="__('Show Discord Box in Hero Section')"
                            :help="__('If enabled, will show discord box in hero section (right side). Make sure to add Discord Invite URL & Discord Server ID in General Settings.')"
                            name="show_discord_box_in_home_hero"
                            :error="form.errors.show_discord_box_in_home_hero"
                          />
                        </div>

                        <!-- Particles Effect -->
                        <div>
                          <XTextarea
                            id="home_hero_bg_particles"
                            v-model="form.home_hero_bg_particles"
                            :auto-resize="false"
                            :label="__('Particle Effect Options')"
                            :help="__('tsParticles Options json. See https://minetrax.github.io/docs/web/theme-settings for more info. (Set it empty to disable particles effect)')"
                            :error="form.errors.home_hero_bg_particles"
                            name="home_hero_bg_particles"
                          />
                        </div>
                      </template>
                    </div>
                  </fieldset>
                </div>

                <!-- Loading Animation -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="space-y-4">
                      <ImageUpload
                        id="loading_gif"
                        name="loading_gif"
                        :label="__('Animated Loader')"
                        v-model="form.loading_gif"
                        :current-url="settings.loading_gif"
                        :error="form.errors.loading_gif"
                        :removable="!!settings.loading_gif"
                        shape="rect"
                        :preview-class="'h-32 w-32'"
                        :upload-label="__('Upload')"
                        :change-label="__('Change')"
                        :remove-label="__('Remove Image')"
                        object-fit="contain"
                        accept="image/gif, image/svg+xml"
                        :hint="__('gif & svg only')"
                        :max-size-mb="1"
                        @remove="removeLoadingGif"
                      />
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                type="submit"
                :disabled="form.processing"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4"
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
                {{ __("Save Theme Settings") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
