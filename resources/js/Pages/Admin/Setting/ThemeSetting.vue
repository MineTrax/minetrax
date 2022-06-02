<template>
  <app-layout>
    <app-head title="Theme Settings" />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="w-64 flex-shrink-0 pr-10">
        <div class="flex flex-col">
          <div class="uppercase mb-2 text-xs tracking-wide text-gray-600 dark:text-gray-400 font-bold">
            SETTINGS
          </div>

          <setting-link
            :href="route('admin.setting.general.show')"
            :active="route().current('admin.setting.general.show')"
          >
            General
          </setting-link>
          <setting-link
            :href="route('admin.setting.theme.show')"
            :active="route().current('admin.setting.theme.show')"
          >
            Theme
          </setting-link>
          <setting-link
            :href="route('admin.setting.plugin.show')"
            :active="route().current('admin.setting.plugin.show')"
          >
            Plugin
          </setting-link>
          <setting-link
            :href="route('admin.setting.player.show')"
            :active="route().current('admin.setting.player.show')"
          >
            Player
          </setting-link>
        </div>
      </div>

      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              Theme Settings
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
                          <div class="col-span-6 sm:col-span-3">
                            <x-select
                              id="color_mode"
                              v-model="form.color_mode"
                              name="color_mode"
                              :error="form.errors.color_mode"
                              label="Default Color Mode"
                              placeholder="Select default color mode.."
                              :disable-null="true"
                              :select-list="{dark: 'Dark', light: 'Light'}"
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
                              label="Color Theme"
                              placeholder="Select theme.."
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
                              label="Primary Font"
                              placeholder="Select primary font.."
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
                              label="Secondary Font"
                              placeholder="Select secondary font.."
                              :disable-null="true"
                              :select-list="fontList"
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
                          Save Theme Settings
                        </loading-button>
                      </div>
                    </div>
                  </form>

                  <div
                    v-if="false"
                    class="flex p-5 justify-center items-center text-red-500 italic"
                  >
                    Not implemented from here! Theme can be be customized by accessing "resources" folder.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetSectionBorder from '@/Jetstream/SectionBorder';
import JetInputError from '@/Jetstream/InputError';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import LoadingButton from '@/Components/LoadingButton';
import XInput from '@/Components/Input';
import Icon from '@/Components/Icon';
import SettingLink from '@/Jetstream/SettingLink';
import JetLabel from '@/Jetstream/Label';
import XSelect from '@/Components/Form/XSelect';

export default {
    components: {
        XSelect,
        SettingLink,
        AppLayout,
        JetSectionBorder,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        JetLabel,
        Icon,
        XInput
    },
    props: {
        settings: Object,
        themeList: Object,
        fontList: Object
    },

    data() {
        return {
            form: this.$inertia.form({
                color_mode: this.settings.color_mode,
                theme_name: this.settings.theme_name,
                primary_font: this.settings.primary_font,
                secondary_font: this.settings.secondary_font
            }),
        };
    },

    methods: {
        saveThemeSetting() {
            this.form.post(route('admin.setting.theme.update'), {
                preserveScroll: true,
            });
        }
    }
};
</script>
