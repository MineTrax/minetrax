<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/Components/ui/dialog";
import XDatePicker from "@/Components/Form/XDatePicker.vue";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();
const { formatTimeAgoToNow } = useHelpers();

defineProps({
    inProgressList: {
        type: Object,
        required: true,
    },
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Settings"),
        current: false,
    },
    {
        text: __("Dangerzone"),
        current: true,
    }
];

const showConfirmModal = ref(false);
const modalTitle = ref("");
const modalDescription = ref("");
const modalRouteName = ref("");
const modalShowDatePicker = ref(false);
const modalBeforeDate = ref(null);
const modalHelpText = ref("");

function openConfirmModal(title, description, routeName, showDate = true, helpText = "") {
    modalTitle.value = title;
    modalDescription.value = description;
    modalRouteName.value = routeName;
    modalShowDatePicker.value = showDate;
    modalBeforeDate.value = null;
    modalHelpText.value = helpText;
    showConfirmModal.value = true;
}

function confirmAction() {
    showConfirmModal.value = false;
    router.delete(route(modalRouteName.value), {
        data: { before_date: modalBeforeDate.value || null },
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Dangerzone')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <div class="shadow rounded-lg">
          <div class="px-4 py-4 bg-card rounded-t-lg border-b border-border flex items-center justify-between">
            <span class="font-bold text-destructive">{{ __("Dangerzone") }}</span>
            <span class="text-sm italic text-muted-foreground">{{ __("Be cautious with your actions!") }}</span>
          </div>

          <div class="bg-card rounded-b-lg divide-y divide-border">
            <!-- Delete all Shouts -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Delete all Shouts") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Permanently delete all shouts from shoutbox.") }}
                </p>

                <div
                  v-if="inProgressList?.truncateShouts"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateShouts) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Delete Shouts'),
                    __('Are you sure you want to delete all Shouts?'),
                    'admin.setting.danger.truncate.shouts'
                  )"
                >
                  {{ __("Delete Shouts") }}
                </Button>
              </div>
            </div>

            <!-- Delete all Console Logs -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Delete all Console Logs") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Permanently delete all console logs of all servers.") }}
                </p>

                <div
                  v-if="inProgressList?.truncateConsolelogs"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateConsolelogs) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Delete Console Logs'),
                    __('Are you sure you want to delete all Console Logs?'),
                    'admin.setting.danger.truncate.consolelogs'
                  )"
                >
                  {{ __("Delete Consolelogs") }}
                </Button>
              </div>
            </div>

            <!-- Delete all Chat History -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Delete all Chat History") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Permanently delete all chat history for all servers.") }}
                </p>

                <div
                  v-if="inProgressList?.truncateChatlogs"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateChatlogs) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Delete Chat History'),
                    __('Are you sure you want to delete all recorded Chat History?'),
                    'admin.setting.danger.truncate.chatlogs'
                  )"
                >
                  {{ __("Delete Chat History") }}
                </Button>
              </div>
            </div>

            <!-- Reset Player Intel Stats -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Reset Player Intel Stats") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Reset all player statistics (kills, deaths, sessions, etc.) to zero while keeping player data intact.") }}
                </p>

                <div
                  v-if="inProgressList?.resetPlayerIntelStats"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.resetPlayerIntelStats) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Reset Player Stats'),
                    __('Are you sure you want to reset all Player Intel Stats?'),
                    'admin.setting.danger.reset.playerintelstats',
                    false
                  )"
                >
                  {{ __("Reset Player Stats") }}
                </Button>
              </div>
            </div>

            <!-- Delete Player Intel -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Delete Player Intel") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Permanently delete all player related stats for all servers. It will also unlink all players linked to user and then delete all player data.") }}
                </p>

                <div
                  v-if="inProgressList?.truncatePlayerIntelData"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncatePlayerIntelData) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Delete Player Intel'),
                    __('Are you sure you want to delete all Player Intel/Statistics?'),
                    'admin.setting.danger.truncate.playerintel',
                    true,
                    __('With a date, only event data is deleted; player records are kept.')
                  )"
                >
                  {{ __("Delete Player Intel") }}
                </Button>
              </div>
            </div>

            <!-- Delete Server Intel -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Delete Server Intel") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Permanently delete all the tracked Server Intel (Analytics) data for all servers. Eg: performance, activities data etc.") }}
                </p>

                <div
                  v-if="inProgressList?.truncateServerIntelData"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncateServerIntelData) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Delete Server Intel'),
                    __('Are you sure you want to delete all Server Analytics/Intel data?'),
                    'admin.setting.danger.truncate.serverintel'
                  )"
                >
                  {{ __("Delete Server Intel") }}
                </Button>
              </div>
            </div>

            <!-- Delete Player Punishments -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Delete Player Punishments (BanWarden)") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Permanently delete all player punishments related data.") }}
                </p>

                <div
                  v-if="inProgressList?.truncatePlayerPunishments"
                  class="text-sm mt-4 p-4 max-w-2xl border border-orange-500/50 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400"
                >
                  <p>
                    {{ __("Action already in progress. Please wait before starting again.") }}
                  </p>
                  <p>
                    {{ __("Last Run: :at", { at: formatTimeAgoToNow(inProgressList?.truncatePlayerPunishments) }) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <Button
                  variant="destructive"
                  @click="openConfirmModal(
                    __('Delete Player Punishments'),
                    __('Are you sure you want to delete all Player Punishments?'),
                    'admin.setting.danger.truncate.playerpunishments'
                  )"
                >
                  {{ __("Delete Player Punishments") }}
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Dialog
      :open="showConfirmModal"
      @update:open="showConfirmModal = $event"
    >
      <DialogContent
        class="sm:max-w-md overflow-visible"
        @open-auto-focus.prevent
        @interact-outside.prevent
      >
        <DialogHeader>
          <DialogTitle>{{ modalTitle }}</DialogTitle>
          <DialogDescription>{{ modalDescription }}</DialogDescription>
        </DialogHeader>

        <div
          v-if="modalShowDatePicker"
          class="py-4"
        >
          <XDatePicker
            v-model="modalBeforeDate"
            :label="__('Delete data before')"
            :help="modalHelpText || __('Leave empty to delete all data.')"
            :placeholder="__('Select date...')"
            :append-to-body="false"
          />
        </div>

        <div
          v-if="modalBeforeDate"
          class="text-sm p-3 border border-blue-500/50 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400"
        >
          {{ __("Only data created before :date will be deleted.", { date: modalBeforeDate }) }}
        </div>

        <DialogFooter class="gap-2">
          <Button
            variant="outline"
            @click="showConfirmModal = false"
          >
            {{ __("Cancel") }}
          </Button>
          <Button
            variant="destructive"
            @click="confirmAction"
          >
            {{ __("Confirm Delete") }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>
