<template>
  <app-layout>
    <app-head
      :title="__('Navigation Settings')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <SettingSidebar />

      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              {{ __("Navigation Settings") }}
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <form
                    autocomplete="off"
                    @submit.prevent="saveSetting"
                  >
                    <div class="shadow overflow-hidden sm:rounded-md">
                      <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                          <div class="flex items-center col-span-3 sm:col-span-3">
                            <x-checkbox
                              id="enable_sticky_header_menu"
                              v-model="form.enable_sticky_header_menu"
                              :label="__('Sticky Navigation Menu')"
                              :help="__('Navigation menu will be fixed on top when scroll.')"
                              name="enable_sticky_header_menu"
                              :error="form.errors.enable_sticky_header_menu"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <x-checkbox
                              id="is_custom_rating_enabled"
                              v-model="form.enable_custom_navbar"
                              :label="__('Enable Custom Navigation Bar')"
                              :help="__('Let you customize the top navigation bar.')"
                              name="is_custom_rating_enabled"
                              :error="form.errors.enable_custom_navbar"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <h3 class="text-gray-600 dark:text-gray-300 mb-1 font-bold">
                              {{ __("Available Items") }}
                            </h3>
                            <Draggable
                              :sort="false"
                              class="dragArea grid grid-cols-5 gap-2 min-h-[5rem]"
                              :list="availableNavItems"
                              :group="{ name: 'navbar', pull: 'clone', put: false }"
                              :clone="cloneNavItem"
                              item-key="key"
                            >
                              <template #item="{ element }">
                                <div class="bg-gray-200 dark:bg-gray-700 rounded p-2 text-gray-400 shadow-sm text-center">
                                  <p class="text-sm text-gray-500">
                                    {{ element.type }}
                                  </p>
                                  <p class="text-gray-700 dark:text-gray-400">
                                    {{ element.name }}
                                  </p>
                                </div>
                              </template>
                            </Draggable>
                          </div>


                          <div class="flex col-span-6 border-t border-gray-200 dark:border-gray-700 pt-4 gap-5">
                            <div class="grow">
                              <h3 class="text-center text-gray-600 dark:text-gray-200 font-bold">
                                Left
                              </h3>
                              <Draggable
                                :swap-threshold="0.65"
                                class="dragArea flex flex-col gap-4 min-h-[5rem] h-full"
                                :list="leftNavList"
                                :group="{ name: 'navbar' }"
                                item-key="key"
                              >
                                <template #item="{ element, index }">
                                  <div class="bg-gray-200 shadow-sm dark:bg-gray-700 relative rounded p-2 text-gray-700 dark:text-gray-400 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-500">
                                      {{ element.type }}
                                    </p>
                                    <p>{{ element.name }}</p>
                                    <x-input
                                      v-if="['custom-page', 'route', 'dropdown'].includes(element.type)"
                                      v-model="element.title"
                                      label="Title"
                                      type="text"
                                    />

                                    <Icon
                                      class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                      name="close"
                                      @click="removeItem(index, leftNavList)"
                                    />

                                    <!--Draggable list if this is dropdown-->
                                    <div v-if="element.type === 'dropdown'">
                                      <Draggable
                                        :swap-threshold="0.65"
                                        class="dragArea flex flex-col gap-2 rounded border border-gray-200 dark:border-gray-700 p-2 min-h-[5rem] bg-white dark:bg-cool-gray-800"
                                        :list="element.children"
                                        :group="{ name: 'navbar' }"
                                        item-key="key"
                                      >
                                        <template #item="{ element: el, index: idx }">
                                          <div class="bg-gray-200 dark:bg-gray-700 relative rounded p-2 text-gray-700 dark:text-gray-400 text-center">
                                            <p class="text-sm text-gray-500">
                                              {{ el.type }}
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-400">
                                              {{ el.name }}
                                            </p>
                                            <x-input
                                              v-if="['custom-page', 'route', 'dropdown'].includes(el.type)"
                                              v-model="el.title"
                                              label="Title"
                                              type="text"
                                            />

                                            <Icon
                                              class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                              name="close"
                                              @click="removeItem(idx, element.children)"
                                            />
                                          </div>
                                        </template>
                                      </Draggable>
                                    </div>
                                  </div>
                                </template>
                              </Draggable>
                            </div>
                            <div class="grow">
                              <h3 class="text-center text-gray-600 dark:text-gray-200 font-bold">
                                Middle
                              </h3>
                              <Draggable
                                :swap-threshold="0.65"
                                class="dragArea flex flex-col gap-4 min-h-[5rem] h-full"
                                :list="middleNavList"
                                :group="{ name: 'navbar' }"
                                item-key="key"
                              >
                                <template #item="{ element, index }">
                                  <div class="bg-gray-200 shadow-sm dark:bg-gray-700 relative rounded p-2 text-gray-700 dark:text-gray-400 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-500">
                                      {{ element.type }}
                                    </p>
                                    <p>{{ element.name }}</p>
                                    <x-input
                                      v-if="['custom-page', 'route', 'dropdown'].includes(element.type)"
                                      v-model="element.title"
                                      label="Title"
                                      type="text"
                                    />

                                    <Icon
                                      class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                      name="close"
                                      @click="removeItem(index, middleNavList)"
                                    />

                                    <!--Draggable list if this is dropdown-->
                                    <div v-if="element.type === 'dropdown'">
                                      <Draggable
                                        :swap-threshold="0.65"
                                        class="dragArea flex flex-col gap-2 rounded border border-gray-200 dark:border-gray-700 p-2 min-h-[5rem] bg-white dark:bg-cool-gray-800"
                                        :list="element.children"
                                        :group="{ name: 'navbar' }"
                                        item-key="key"
                                      >
                                        <template #item="{ element: el, index: idx }">
                                          <div class="bg-gray-200 dark:bg-gray-700 relative rounded p-2 text-gray-700 dark:text-gray-400 text-center">
                                            <p class="text-sm text-gray-500">
                                              {{ el.type }}
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-400">
                                              {{ el.name }}
                                            </p>
                                            <x-input
                                              v-if="['custom-page', 'route', 'dropdown'].includes(el.type)"
                                              v-model="el.title"
                                              label="Title"
                                              type="text"
                                            />

                                            <Icon
                                              class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                              name="close"
                                              @click="removeItem(idx, element.children)"
                                            />
                                          </div>
                                        </template>
                                      </Draggable>
                                    </div>
                                  </div>
                                </template>
                              </Draggable>
                            </div>
                            <div class="grow">
                              <h3 class="text-center text-gray-600 dark:text-gray-200 font-bold">
                                Right
                              </h3>
                              <Draggable
                                :swap-threshold="0.65"
                                class="dragArea flex flex-col gap-4 min-h-[5rem] h-full"
                                :list="rightNavList"
                                :group="{ name: 'navbar' }"
                                item-key="key"
                              >
                                <template #item="{ element, index }">
                                  <div class="bg-gray-200 shadow-sm dark:bg-gray-700 relative rounded p-2 text-gray-700 dark:text-gray-400 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-500">
                                      {{ element.type }}
                                    </p>
                                    <p>{{ element.name }}</p>
                                    <x-input
                                      v-if="['custom-page', 'route', 'dropdown'].includes(element.type)"
                                      v-model="element.title"
                                      label="Title"
                                      type="text"
                                    />

                                    <Icon
                                      class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                      name="close"
                                      @click="removeItem(index, rightNavList)"
                                    />

                                    <!--Draggable list if this is dropdown-->
                                    <div v-if="element.type === 'dropdown'">
                                      <Draggable
                                        :swap-threshold="0.65"
                                        class="dragArea flex flex-col gap-2 rounded border border-gray-200 dark:border-gray-700 p-2 min-h-[5rem] bg-white dark:bg-cool-gray-800"
                                        :list="element.children"
                                        :group="{ name: 'navbar' }"
                                        item-key="key"
                                      >
                                        <template #item="{ element: el, index: idx }">
                                          <div class="bg-gray-200 dark:bg-gray-700 relative rounded p-2 text-gray-700 dark:text-gray-400 text-center">
                                            <p class="text-sm text-gray-500">
                                              {{ el.type }}
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-400">
                                              {{ el.name }}
                                            </p>
                                            <x-input
                                              v-if="['custom-page', 'route', 'dropdown'].includes(el.type)"
                                              v-model="el.title"
                                              label="Title"
                                              type="text"
                                            />

                                            <Icon
                                              class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                              name="close"
                                              @click="removeItem(idx, element.children)"
                                            />
                                          </div>
                                        </template>
                                      </Draggable>
                                    </div>
                                  </div>
                                </template>
                              </Draggable>
                            </div>
                          </div>

                          <div class="flex col-span-6 text-gray-500 text-sm justify-center italic">
                            {{ __("Tip: Start by dragging items from above 'Available Items' to down here.") }}
                          </div>
                        </div>
                      </div>
                      <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                        <loading-button
                          :loading="form.processing"
                          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          {{ __("Save Navigation Settings") }}
                        </loading-button>
                      </div>
                    </div>
                  </form>
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
import AppLayout from '@/Layouts/AppLayout.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XInput from '@/Components/Form/XInput.vue';
import SettingSidebar from '@/Shared/SettingSidebar.vue';
import Draggable from 'vuedraggable';
import Icon from '@/Components/Icon.vue';

export default {
    components: {
        Icon,
        Draggable,
        SettingSidebar,
        XCheckbox,
        AppLayout,
        LoadingButton,
        XInput,
    },
    props: {
        settings: Object,
        generalSettings: Object,
        availableNavItems: Array,
    },

    data() {
        return {
            form: this.$inertia.form({
                enable_sticky_header_menu: this.generalSettings.enable_sticky_header_menu,
                enable_custom_navbar: this.settings.enable_custom_navbar,
                custom_navbar_data: this.settings.custom_navbar_data,
            }),
            leftNavList: this.settings.custom_navbar_data?.left || [],
            middleNavList: this.settings.custom_navbar_data?.middle || [],
            rightNavList: this.settings.custom_navbar_data?.right || [],
        };
    },

    methods: {
        saveSetting() {
            this.form.custom_navbar_data = {
                left: this.leftNavList,
                middle: this.middleNavList,
                right: this.rightNavList,
            };
            this.form.post(route('admin.setting.navigation.update'), {
                preserveScroll: true,
                onSuccess: () => {
                    location.reload();
                },
            });
        },
        cloneNavItem(item) {
            let clone = JSON.parse(JSON.stringify(item));
            return {
                ...clone,
                key: item.key + '-' +  Math.random(),
            };
        },
        removeItem(idx, list) {
            list.splice(idx, 1);
        },
    }
};
</script>
