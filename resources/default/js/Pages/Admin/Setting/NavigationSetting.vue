<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import XSwitch from '@/Components/Form/XSwitch.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import Draggable from 'vuedraggable';
import Icon from '@/Components/Icon.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const footerStyleList = [
    'variant_2',
    'variant_1',
];

const props = defineProps({
    settings: Object,
    generalSettings: Object,
    availableNavItems: Array,
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
        text: __('Navigation Settings'),
        current: true,
    }
];

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
        key: item.key + '-' + Math.random(),
    };
}

function removeNavItem(idx, list) {
    list.splice(idx, 1);
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Navigation Settings')" />

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
          @submit.prevent="saveSetting"
        >
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Navigation Bar Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Navigation Bar") }}
                    </legend>

                    <div class="space-y-6">
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <XSwitch
                          id="enable_sticky_header_menu"
                          v-model="form.enable_sticky_header_menu"
                          :label="__('Sticky Navigation Menu')"
                          :help="__('Navigation menu will be fixed on top when scroll.')"
                          name="enable_sticky_header_menu"
                          :error="form.errors.enable_sticky_header_menu"
                        />

                        <XSwitch
                          id="enable_custom_navbar"
                          v-model="form.enable_custom_navbar"
                          :label="__('Enable Custom Navigation Bar')"
                          :help="__('Let you customize the top navigation bar.')"
                          name="enable_custom_navbar"
                          :error="form.errors.enable_custom_navbar"
                        />
                      </div>

                      <!-- Available Items -->
                      <div>
                        <h3 class="text-foreground mb-2 font-semibold text-sm">
                          {{ __("Available Items") }}
                        </h3>
                        <Draggable
                          :sort="false"
                          class="dragArea grid grid-cols-5 gap-2 min-h-[5rem] cursor-move bg-muted/30 rounded-lg p-3"
                          :list="availableNavItems"
                          :group="{ name: 'navbar', pull: 'clone', put: false }"
                          :clone="cloneNavItem"
                          item-key="key"
                        >
                          <template #item="{ element }">
                            <div class="bg-muted rounded p-2 text-foreground shadow-sm text-center">
                              <p class="text-xs text-muted-foreground">
                                {{ element.type }}
                              </p>
                              <p class="text-foreground text-sm">
                                {{ element.name }}
                              </p>
                            </div>
                          </template>
                        </Draggable>
                      </div>

                      <!-- Nav Columns -->
                      <div class="flex gap-4 border-t border-border pt-4">
                        <div class="flex-1">
                          <h3 class="text-center text-foreground font-semibold text-sm mb-2">
                            {{ __("Left") }}
                          </h3>
                          <Draggable
                            :swap-threshold="0.65"
                            class="dragArea flex flex-col gap-3 min-h-[8rem] h-full bg-muted/30 rounded-lg p-3"
                            :list="leftNavList"
                            :group="{ name: 'navbar' }"
                            item-key="key"
                          >
                            <template #item="{ element, index }">
                              <div class="bg-muted shadow-sm relative rounded p-2 text-foreground text-center">
                                <p class="text-xs text-muted-foreground">
                                  {{ element.type }}
                                </p>
                                <p class="text-sm">{{ element.name }}</p>
                                <XInput
                                  v-if="['custom-page', 'route', 'dropdown', 'download', 'recruitment'].includes(element.type)"
                                  v-model="element.title"
                                  :label="__('Title')"
                                  type="text"
                                />

                                <Icon
                                  class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-muted-foreground hover:text-destructive"
                                  name="close"
                                  @click="removeNavItem(index, leftNavList)"
                                />

                                <!-- Dropdown children -->
                                <div v-if="element.type === 'dropdown'">
                                  <Draggable
                                    :swap-threshold="0.65"
                                    class="dragArea flex flex-col gap-2 rounded border border-border p-2 min-h-[5rem] bg-card mt-2"
                                    :list="element.children"
                                    :group="{ name: 'navbar' }"
                                    item-key="key"
                                  >
                                    <template #item="{ element: el, index: idx }">
                                      <div class="bg-muted relative rounded p-2 text-foreground text-center">
                                        <p class="text-xs text-muted-foreground">
                                          {{ el.type }}
                                        </p>
                                        <p class="text-sm">
                                          {{ el.name }}
                                        </p>
                                        <XInput
                                          v-if="['custom-page', 'route', 'dropdown', 'download', 'recruitment'].includes(el.type)"
                                          v-model="el.title"
                                          :label="__('Title')"
                                          type="text"
                                        />

                                        <Icon
                                          class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-muted-foreground hover:text-destructive"
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

                        <div class="flex-1">
                          <h3 class="text-center text-foreground font-semibold text-sm mb-2">
                            {{ __("Middle") }}
                          </h3>
                          <Draggable
                            :swap-threshold="0.65"
                            class="dragArea flex flex-col gap-3 min-h-[8rem] h-full bg-muted/30 rounded-lg p-3"
                            :list="middleNavList"
                            :group="{ name: 'navbar' }"
                            item-key="key"
                          >
                            <template #item="{ element, index }">
                              <div class="bg-muted shadow-sm relative rounded p-2 text-foreground text-center">
                                <p class="text-xs text-muted-foreground">
                                  {{ element.type }}
                                </p>
                                <p class="text-sm">{{ element.name }}</p>
                                <XInput
                                  v-if="['custom-page', 'route', 'dropdown', 'download', 'recruitment'].includes(element.type)"
                                  v-model="element.title"
                                  :label="__('Title')"
                                  type="text"
                                />

                                <Icon
                                  class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-muted-foreground hover:text-destructive"
                                  name="close"
                                  @click="removeNavItem(index, middleNavList)"
                                />

                                <!-- Dropdown children -->
                                <div v-if="element.type === 'dropdown'">
                                  <Draggable
                                    :swap-threshold="0.65"
                                    class="dragArea flex flex-col gap-2 rounded border border-border p-2 min-h-[5rem] bg-card mt-2"
                                    :list="element.children"
                                    :group="{ name: 'navbar' }"
                                    item-key="key"
                                  >
                                    <template #item="{ element: el, index: idx }">
                                      <div class="bg-muted relative rounded p-2 text-foreground text-center">
                                        <p class="text-xs text-muted-foreground">
                                          {{ el.type }}
                                        </p>
                                        <p class="text-sm">
                                          {{ el.name }}
                                        </p>
                                        <XInput
                                          v-if="['custom-page', 'route', 'dropdown', 'download', 'recruitment'].includes(el.type)"
                                          v-model="el.title"
                                          :label="__('Title')"
                                          type="text"
                                        />

                                        <Icon
                                          class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-muted-foreground hover:text-destructive"
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

                        <div class="flex-1">
                          <h3 class="text-center text-foreground font-semibold text-sm mb-2">
                            {{ __("Right") }}
                          </h3>
                          <Draggable
                            :swap-threshold="0.65"
                            class="dragArea flex flex-col gap-3 min-h-[8rem] h-full bg-muted/30 rounded-lg p-3"
                            :list="rightNavList"
                            :group="{ name: 'navbar' }"
                            item-key="key"
                          >
                            <template #item="{ element, index }">
                              <div class="bg-muted shadow-sm relative rounded p-2 text-foreground text-center">
                                <p class="text-xs text-muted-foreground">
                                  {{ element.type }}
                                </p>
                                <p class="text-sm">{{ element.name }}</p>
                                <XInput
                                  v-if="['custom-page', 'route', 'dropdown', 'download', 'recruitment'].includes(element.type)"
                                  v-model="element.title"
                                  :label="__('Title')"
                                  type="text"
                                />

                                <Icon
                                  class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-muted-foreground hover:text-destructive"
                                  name="close"
                                  @click="removeNavItem(index, rightNavList)"
                                />

                                <!-- Dropdown children -->
                                <div v-if="element.type === 'dropdown'">
                                  <Draggable
                                    :swap-threshold="0.65"
                                    class="dragArea flex flex-col gap-2 rounded border border-border p-2 min-h-[5rem] bg-card mt-2"
                                    :list="element.children"
                                    :group="{ name: 'navbar' }"
                                    item-key="key"
                                  >
                                    <template #item="{ element: el, index: idx }">
                                      <div class="bg-muted relative rounded p-2 text-foreground text-center">
                                        <p class="text-xs text-muted-foreground">
                                          {{ el.type }}
                                        </p>
                                        <p class="text-sm">
                                          {{ el.name }}
                                        </p>
                                        <XInput
                                          v-if="['custom-page', 'route', 'dropdown', 'download', 'recruitment'].includes(el.type)"
                                          v-model="el.title"
                                          :label="__('Title')"
                                          type="text"
                                        />

                                        <Icon
                                          class="w-4 h-4 absolute right-2 top-2 cursor-pointer text-muted-foreground hover:text-destructive"
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

                      <p class="pt-4 text-muted-foreground text-sm text-center italic">
                        {{ __("Tip: Start by dragging items from above 'Available Items' to down here.") }}
                      </p>
                    </div>
                  </fieldset>
                </div>

                <!-- Footer Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Footer") }}
                    </legend>

                    <div class="space-y-6">
                      <XSwitch
                        id="enable_custom_footer"
                        v-model="form.enable_custom_footer"
                        :label="__('Enable Custom Footer')"
                        :help="__('Let you customize the site footer and content in it.')"
                        name="enable_custom_footer"
                        :error="form.errors.enable_custom_footer"
                      />

                      <template v-if="form.enable_custom_footer">
                        <XSelect
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

                        <XTextarea
                          id="custom_footer_moto"
                          v-model="customFooterData.site_moto"
                          :auto-resize="true"
                          :label="__('Footer Site Moto')"
                          :help="__('This can be anything you want to display below Logo in footer. Eg: Think Different!')"
                          :error="form.errors['custom_footer_data.site_moto']"
                          name="custom_footer_moto"
                        />

                        <!-- Footer Columns -->
                        <div class="space-y-6">
                          <div
                            v-for="(column, column_index) in customFooterData.columns"
                            :key="column_index"
                            class="bg-muted/30 rounded-lg p-4 space-y-4"
                          >
                            <XInput
                              :id="`column_${column_index}_header`"
                              v-model="column.title"
                              :label="__('Footer Column :index Header Title', { index: column_index + 1 })"
                              type="text"
                              :name="`column_${column_index}_header`"
                              :error="form.errors[`custom_footer_data.columns.${column_index}.title`]"
                              help-error-flex="flex-col"
                            />

                            <div
                              v-for="(item, item_index) in column.items"
                              :key="column_index + '_' + item_index"
                              class="flex gap-2 items-start"
                            >
                              <div class="flex-1">
                                <XInput
                                  :id="`column_${column_index}_item_${item_index}_title`"
                                  v-model="item.title"
                                  :label="__('Item :index Title', { index: item_index + 1 })"
                                  type="text"
                                  :name="`column_${column_index}_item_${item_index}_title`"
                                  :error="form.errors[`custom_footer_data.columns.${column_index}.items.${item_index}.title`]"
                                  help-error-flex="flex-col"
                                />
                              </div>
                              <div class="flex-1">
                                <XInput
                                  :id="`column_${column_index}_item_${item_index}_url`"
                                  v-model="item.url"
                                  :label="__('Item :index URL', { index: item_index + 1 })"
                                  type="text"
                                  :name="`column_${column_index}_item_${item_index}_url`"
                                  :error="form.errors[`custom_footer_data.columns.${column_index}.items.${item_index}.url`]"
                                  help-error-flex="flex-col"
                                />
                              </div>

                              <button
                                type="button"
                                class="mt-7 focus:outline-none group"
                                @click="removeFooterColumnItem(column, item_index)"
                              >
                                <Icon
                                  class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                                  name="trash"
                                />
                              </button>
                            </div>

                            <div class="flex justify-end">
                              <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addFooterColumnItem(column)"
                              >
                                {{ __("Add Item") }}
                              </Button>
                            </div>
                          </div>
                        </div>
                      </template>
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
                {{ __("Save Navigation Settings") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
