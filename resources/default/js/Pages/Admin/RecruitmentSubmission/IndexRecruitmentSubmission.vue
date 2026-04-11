<script setup>
import AppHead from "@/Components/AppHead.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import Icon from "@/Components/Icon.vue";
import UserDisplayname from "@/Components/UserDisplayname.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { EyeIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { Link, router } from "@inertiajs/vue3";
import { pickBy } from "lodash";
import { ref, watch } from "vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    forms: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    submissions: {
        type: Object,
    },
    closed: {
        type: Boolean,
        default: false,
    },
});

const headerRow = [
    {
        key:"id",
        label: __("ID"),
        sortable: true,
        class:"text-left w-[5%]",
        filterable: {
            key:"id",
            type:"text",
        }
    },
    {
        key:"user_id",
        sortable: true,
        label: __("Applicant"),
        class:"w-3/12",
        filterable: {
            key:"user.name",
            type:"text",
        }
    },
    {
        key:"recruitment_id",
        label: __("Application"),
        sortable: true,
    },
    {
        key:"status",
        label: __("Status"),
        sortable: true,
        filterable: {
            type:"multiselect",
            options: ["pending","inprogress","approved","rejected","withdrawn","onhold"],
        }
    },
    {
        key:"last_act_at",
        sortable: true,
        label: __("Last Actor"),
        class:"text-right",
        filterable: {
            key:"lastActor.name",
            type:"text",
        }
    },
    {
        key:"last_comment_at",
        sortable: true,
        label: __("Last Comment"),
        class:"text-right",
        filterable: {
            key:"lastCommentor.name",
            type:"text",
        }
    },
    {
        key:"created_at",
        label: __("Created At"),
        class:"text-right w-1/12 whitespace-nowrap",
        sortable: true,
    },
    {
        key:"updated_at",
        label: __("Updated At"),
        class:"text-right w-1/12 whitespace-nowrap",
        sortable: true,
    },
    {
        key:"actions",
        label: __("Actions"),
        sortable: false,
        class:"w-1/12 text-right",
    },
];

// Form Selector
let selectedForms = ref(
    props.filters?.forms?.length ? props.filters?.forms[0] : null
);

watch(selectedForms, (newSelectedForms) => {
    const query = {
        forms: newSelectedForms ? [newSelectedForms] : null,
    };

    router.get(route(route().current()), pickBy(query));
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Applications"),
        current: false,
    },
    {
        text: props.closed ? __("Closed Requests") : __("Open Requests"),
        current: true,
    }
];
</script>

<template>
  <AdminLayout>
    <AppHead
      :title="
        closed
          ? __('Closed Request - Applications')
          : __('Open Requests - Applications')
      "
    />

    <div class="px-10 py-8 mx-auto space-y-4">
      <div class="flex items-center justify-between">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />

        <x-select
          id="selectForms"
          v-model="selectedForms"
          name="selectForms"
          :select-list="forms"
          :placeholder="__('All Applications')"
          class="w-48 max-w-48 border rounded bg-card"
        />
      </div>

      <div>
        <DataTable
          class="bg-card rounded-lg shadow"
          :header="headerRow"
          :data="submissions"
          :filters="filters"
          :row-href="(item) => route('admin.recruitment-submission.show', item.id)"
        >
          <template #default="{ item }">
            <td
              class="text-sm px-4 font-medium text-left text-foreground whitespace-nowrap dark:text-foreground"
            >
              {{ item.id }}
            </td>

            <td class="px-4">
              <div class="flex items-center">
                <div class="shrink-0 h-10 w-10 mr-2">
                  <img
                    class="h-10 w-10 rounded-full"
                    :src="item.user.profile_photo_url"
                    alt="Avatar"
                  >
                </div>
                <div class="flex-col">
                  <div
                    class="text-sm font-semibold text-foreground dark:text-foreground whitespace-nowrap truncate"
                    :style="[
                      item.user.roles[0].color
                        ? {
                          color: item.user.roles[0]
                            .color,
                        }
                        : null,
                    ]"
                  >
                    {{ item.user.name }}
                    <Icon
                      v-if="item.user.verified_at"
                      v-tippy
                      name="verified-check-fill"
                      :title="__('Verified Account')"
                      class="inline mb-1 fill-current focus:outline-hidden text-primary w-5 h-5"
                    />
                    <Icon
                      v-if="item.user.is_staff"
                      v-tippy
                      name="shield-check-fill"
                      :title="__('Staff Member')"
                      class="inline mb-1 text-amber-400 fill-current focus:outline-hidden w-5 h-5"
                    />
                    <Icon
                      v-if="item.user.muted_at"
                      v-tippy
                      name="volume-off-fill"
                      :title="__('Muted User')"
                      class="inline mb-1 text-destructive fill-current focus:outline-hidden w-5 h-5"
                    />
                  </div>
                  <div class="text-sm text-foreground">
                    @{{ item.user.username }}
                  </div>
                </div>
              </div>
            </td>

            <DtRowItem>
              <p
                v-tippy
                :title="item.recruitment.title"
                class="truncate w-32"
              >
                {{ item.recruitment.title }}
              </p>
            </DtRowItem>

            <DtRowItem>
              <CommonStatusBadge :status="item.status.value" />
            </DtRowItem>

            <DtRowItem
              class="text-right whitespace-nowrap"
            >
              <UserDisplayname
                v-if="item.last_actor"
                text-class="text-sm text-foreground dark:text-foreground"
                :user="item.last_actor"
                :show-badges="true"
              >
                <div class="text-xs text-foreground dark:text-foreground">
                  {{ formatTimeAgoToNow(item.last_act_at) }}
                </div>
              </UserDisplayname>
              <span
                v-else
                class="text-foreground text-sm italic"
              >
                {{ __('None') }}
              </span>
            </DtRowItem>

            <DtRowItem
              class="text-right whitespace-nowrap"
            >
              <UserDisplayname
                v-if="item.last_commentor"
                text-class="text-sm text-foreground dark:text-foreground"
                :user="item.last_commentor"
                :show-badges="true"
              >
                <div class="text-xs text-foreground dark:text-foreground">
                  {{ formatTimeAgoToNow(item.last_comment_at) }}
                </div>
              </UserDisplayname>
              <span
                v-else
                class="text-foreground text-sm italic"
              >
                {{ __('None') }}
              </span>
            </DtRowItem>

            <DtRowItem
              v-tippy
              class="text-right whitespace-nowrap"
              :content="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </DtRowItem>

            <DtRowItem
              v-tippy
              class="text-right whitespace-nowrap"
              :content="formatToDayDateString(item.updated_at)"
            >
              {{ formatTimeAgoToNow(item.updated_at) }}
            </DtRowItem>

            <td
              class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
            >
              <ButtonGroup>
                <Button
                  variant="outline"
                  size="icon"
                  as-child
                  class="text-primary hover:text-primary"
                >
                  <Link
                    v-tippy
                    as="a"
                    :href="
                      route(
                        'admin.recruitment-submission.show',
                        item.id
                      )
                    "
                    :title="__('View Submission')"
                  >
                    <EyeIcon />
                  </Link>
                </Button>
                <Button
                  v-if="
                    can('delete recruitment_submissions') &&
                      closed
                  "
                  variant="outline"
                  size="icon"
                  as-child
                  class="text-destructive hover:text-destructive"
                >
                  <Link
                    v-confirm="{
                      message:
                        'Delete this Request? This action cannot be undone.',
                    }"
                    v-tippy
                    as="button"
                    method="DELETE"
                    :href="
                      route(
                        'admin.recruitment-submission.delete',
                        item.id
                      )
                    "
                    :title="__('Delete Submission')"
                  >
                    <TrashIcon />
                  </Link>
                </Button>
              </ButtonGroup>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AdminLayout>
</template>
