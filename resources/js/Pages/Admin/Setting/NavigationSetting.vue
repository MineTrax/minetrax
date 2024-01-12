<script setup>
import LoadingButton from '@/Components/LoadingButton.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import Draggable from 'vuedraggable';
import Icon from '@/Components/Icon.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const footerStyleList = [
    'variant_2',
    'variant_1',
];

const props = defineProps({
    settings: Object,
    generalSettings: Object,
    availableNavItems: Array,
});

const leftNavList = ref(props.settings.custom_navbar_data?.left || []);
const middleNavList = ref(props.settings.custom_navbar_data?.middle || []);
const rightNavList = ref(props.settings.custom_navbar_data?.right || []);

const isCustomFooterPropDataAvailable = props.settings.custom_footer_data && Object.keys(props.settings.custom_footer_data).length > 0;

const customFooterData = ref(isCustomFooterPropDataAvailable ? props.settings.custom_footer_data : {
    style: null,
    site_moto: null,
    columns: [
        {
            title: null,
            items: [
                {
                    title: null,
                    url: null,
                },
            ]
        },
        {
            title: null,
            items: [
                {
                    title: null,
                    url: null,
                },
            ]
        },
        {
            title: null,
            items: [
                {
                    title: null,
                    url: null,
                },
            ]
        },
        {
            title: null,
            items: [
                {
                    title: null,
                    url: null,
                },
            ]
        }
    ],
});

function addFooterColumnItem(column) {
    column.items.push({
        title: null,
        url: null,
    });
}

function removeFooterColumnItem(column, idx) {
    column.items.splice(idx, 1);
}

const form = useForm({
    enable_sticky_header_menu: props.generalSettings.enable_sticky_header_menu,
    enable_custom_navbar: props.settings.enable_custom_navbar,
    custom_navbar_data: props.settings.custom_navbar_data,
    enable_custom_footer: props.settings.enable_custom_footer,
    custom_footer_data: props.settings.custom_footer_data,
});

function saveSetting() {
    form.custom_navbar_data = {
        left: leftNavList.value,
        middle: middleNavList.value,
        right: rightNavList.value,
    };

    form.custom_footer_data = customFooterData.value;

    form.post(route('admin.setting.navigation.update'), {
        preserveScroll: true,
        onSuccess: () => {
            location.reload();
        },
    });
}

function cloneNavItem(item) {
    let clone = JSON.parse(JSON.stringify(item));
    return {
        ...clone,
        key: item.key + '-' +  Math.random(),
    };
}

function removeNavItem(idx, list) {
    list.splice(idx, 1);
}
</script>



<template>
  <AdminLayout>
    <app-head
      :title="__('Navigation Settings')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full rounded">
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
                              class="dragArea grid grid-cols-5 gap-2 min-h-[5rem] cursor-move"
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
                                      v-if="['custom-page', 'route', 'dropdown', 'download'].includes(element.type)"
                                      v-model="element.title"
                                      label="Title"
                                      type="text"
                                    />

                                    <Icon
                                      class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                      name="close"
                                      @click="removeNavItem(index, leftNavList)"
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
                                              v-if="['custom-page', 'route', 'dropdown', 'download'].includes(el.type)"
                                              v-model="el.title"
                                              label="Title"
                                              type="text"
                                            />

                                            <Icon
                                              class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                              name="close"
                                              @click="removeNavItem(idx, element.children)"
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
                                      v-if="['custom-page', 'route', 'dropdown', 'download'].includes(element.type)"
                                      v-model="element.title"
                                      label="Title"
                                      type="text"
                                    />

                                    <Icon
                                      class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                      name="close"
                                      @click="removeNavItem(index, middleNavList)"
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
                                              v-if="['custom-page', 'route', 'dropdown', 'download'].includes(el.type)"
                                              v-model="el.title"
                                              label="Title"
                                              type="text"
                                            />

                                            <Icon
                                              class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                              name="close"
                                              @click="removeNavItem(idx, element.children)"
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
                                      v-if="['custom-page', 'route', 'dropdown', 'download'].includes(element.type)"
                                      v-model="element.title"
                                      label="Title"
                                      type="text"
                                    />

                                    <Icon
                                      class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                      name="close"
                                      @click="removeNavItem(index, rightNavList)"
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
                                              v-if="['custom-page', 'route', 'dropdown', 'download'].includes(el.type)"
                                              v-model="el.title"
                                              label="Title"
                                              type="text"
                                            />

                                            <Icon
                                              class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-gray-500 dark:text-gray-400"
                                              name="close"
                                              @click="removeNavItem(idx, element.children)"
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

                          <div class="flex items-center col-span-3 sm:col-span-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <x-checkbox
                              id="enable_custom_footer"
                              v-model="form.enable_custom_footer"
                              :label="__('Enable Custom Footer')"
                              :help="__('Let you customize the site footer and content in it.')"
                              name="enable_custom_footer"
                              :error="form.errors.enable_custom_footer"
                            />
                          </div>

                          <div
                            v-if="form.enable_custom_footer"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-select
                              id="custom_footer_style"
                              v-model="customFooterData.style"
                              name="custom_footer_style"
                              :error="form.errors['custom_footer_data.style']"
                              :label="__('Custom Footer Style')"
                              :placeholder="__('Select the style variant of the footer.')"
                              :disable-null="true"
                              :help="__('Select the style variant of the footer. Try variant_1 first and later change if you want.')"
                              :select-list="footerStyleList"
                            />
                          </div>

                          <div
                            v-if="form.enable_custom_footer"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-textarea
                              id="custom_footer_moto"
                              v-model="customFooterData.site_moto"
                              :auto-resize="true"
                              :label="__('Footer Site Moto')"
                              :help="__('This can be anything you want to display below Logo in footer. Eg: Think Different!')"
                              :error="form.errors['custom_footer_data.site_moto']"
                              name="custom_footer_moto"
                            />
                          </div>

                          <!-- Custom Footer Here -->
                          <template
                            v-if="form.enable_custom_footer"
                          >
                            <div
                              v-for="column,column_index in customFooterData.columns"
                              :key="column_index"
                              class="col-span-6 sm:col-span-6"
                            >
                              <x-input
                                :id="`column_${column_index}_header`"
                                v-model="column.title"
                                :label="__(`Footer Column ${column_index+1} Header Title`)"
                                type="text"
                                :name="`column_${column_index}_header`"
                                :error="form.errors[`custom_footer_data.columns.${column_index}.title`]"
                                help-error-flex="flex-col"
                              />
                              <div
                                v-for="item,item_index in column.items"
                                :key="column_index + '_' + item_index"
                                class="flex gap-2"
                              >
                                <x-input
                                  :id="`column_${column_index}_item_${item_index}_title`"
                                  v-model="item.title"
                                  :label="__(`Item ${item_index+1} Title`)"
                                  type="text"
                                  :name="`column_${column_index}_item_${item_index}_title`"
                                  :error="form.errors[`custom_footer_data.columns.${column_index}.items.${item_index}.title`]"
                                  help-error-flex="flex-col"
                                />
                                <x-input
                                  :id="`column_${column_index}_item_${item_index}_url`"
                                  v-model="item.url"
                                  :label="__(`Item ${item_index+1} Url`)"
                                  type="text"
                                  :name="`column_${column_index}_item_${item_index}_url`"
                                  :error="form.errors[`custom_footer_data.columns.${column_index}.items.${item_index}.url`]"
                                  help-error-flex="flex-col"
                                />

                                <div class="flex items-center justify-center">
                                  <button
                                    type="button"
                                    class="focus:outline-none group"
                                    @click="removeFooterColumnItem(column, item_index)"
                                  >
                                    <Icon
                                      class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                                      name="trash"
                                    />
                                  </button>
                                </div>
                              </div>

                              <button
                                class="mt-2 float-right inline-flex items-center px-2 py-1 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                                type="button"
                                @click="addFooterColumnItem(column)"
                              >
                                Add
                              </button>
                            </div>
                          </template>
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
  </AdminLayout>
</template>
