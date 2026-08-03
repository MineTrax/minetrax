<script setup>
import { computed, ref, watch } from "vue";
import { router, usePoll } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/Components/ui/dialog";
import XDatePicker from "@/Components/Form/XDatePicker.vue";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();
const { formatTimeAgoToNow } = useHelpers();

const props = defineProps({
    inProgressList: {
        type: Object,
        required: true,
    },
    hasRecentBackup: {
        type: Boolean,
        required: true,
    },
    backupRuns: {
        type: Array,
        required: true,
    },
});

const lastBackup = computed(() => {
    return props.backupRuns.find(run => run.status === "completed") ?? null;
});

const backupStatusText = computed(() => {
    if (props.hasRecentBackup && lastBackup.value) {
        return __("Last backup: :time", { time: formatTimeAgoToNow(lastBackup.value.completed_at) });
    }

    return __("No recent backup found. Please create a database backup before running destructive actions.");
});

const hasPendingBackup = computed(() => props.backupRuns.some(run => run.status === "pending"));
const isReloading = ref(false);

const { start: startPolling, stop: stopPolling } = usePoll(5000, {
    only: ["backupRuns", "hasRecentBackup"],
}, {
    autoStart: false,
});

watch(hasPendingBackup, (pending) => {
    if (pending) {
        startPolling();
    } else {
        stopPolling();
    }
}, { immediate: true });

function reloadBackupHistory() {
    isReloading.value = true;

    router.reload({
        only: ["backupRuns", "hasRecentBackup"],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isReloading.value = false;
        },
    });
}

function formatBytes(bytes) {
    if (bytes === null || bytes === undefined || bytes === 0) {
        return "-";
    }

    const sizes = ["B", "KB", "MB", "GB", "TB"];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));

    return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + " " + sizes[i];
}

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
const modalRouteParams = ref(null);
const modalMethod = ref("delete");
const modalShowDatePicker = ref(false);
const modalBeforeDate = ref(null);
const modalHelpText = ref("");

function openConfirmModal(title, description, routeName, showDate = true, helpText = "", method = "delete", routeParams = null) {
    modalTitle.value = title;
    modalDescription.value = description;
    modalRouteName.value = routeName;
    modalRouteParams.value = routeParams;
    modalMethod.value = method;
    modalShowDatePicker.value = showDate;
    modalBeforeDate.value = null;
    modalHelpText.value = helpText;
    showConfirmModal.value = true;
}

function openBackupConfirmModal() {
    openConfirmModal(
        __("Export Database Backup"),
        __("Are you sure you want to create a database-only backup? This may take a few minutes for large databases."),
        "admin.setting.danger.backup.database",
        false,
        "",
        "post"
    );
}

function openDeleteBackupConfirmModal(run) {
    openConfirmModal(
        __("Delete Backup"),
        __("Are you sure you want to delete this backup? The file will be permanently removed."),
        "admin.setting.danger.backup.delete",
        false,
        "",
        "delete",
        run.id
    );
}

function confirmAction() {
    showConfirmModal.value = false;

    const data = { before_date: modalBeforeDate.value || null };
    const routeParams = modalRouteParams.value;

    if (modalMethod.value === "post") {
        router.post(route(modalRouteName.value, routeParams), data);
    } else {
        router.delete(route(modalRouteName.value, routeParams), { data });
    }
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

      <div
        v-if="!hasRecentBackup"
        class="mb-6 p-4 border border-orange-500/50 rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400"
      >
        <p class="font-semibold">
          {{ __("Database Backup Required") }}
        </p>
        <p class="mt-1 text-sm">
          {{ __("Destructive actions in this section are disabled until a database backup has been created within the last 24 hours. Console log and chat history deletion are exempt.") }}
        </p>
      </div>

      <div class="mt-6">
        <div class="shadow rounded-lg">
          <div class="px-4 py-4 bg-card rounded-t-lg border-b border-border flex items-center justify-between">
            <span class="font-bold text-destructive">{{ __("Dangerzone") }}</span>
            <span class="text-sm italic text-muted-foreground">{{ __("Be cautious with your actions!") }}</span>
          </div>

          <div class="bg-card rounded-b-lg divide-y divide-border">
            <!-- Export Database Backup -->
            <div class="flex justify-between p-5">
              <div class="flex flex-col">
                <h3 class="text-base font-semibold leading-6 text-foreground">
                  {{ __("Export Database Backup") }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ __("Create a database-only backup. This runs in the background and may take a few minutes for large databases.") }}
                </p>
                <p class="mt-2 text-sm text-muted-foreground">
                  {{ backupStatusText }}
                </p>
              </div>
              <div class="flex items-start">
                <Button @click="openBackupConfirmModal">
                  {{ __("Export Backup") }}
                </Button>
              </div>
            </div>

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
                  :disabled="!hasRecentBackup"
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
                  :disabled="!hasRecentBackup"
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
                  :disabled="!hasRecentBackup"
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
                  :disabled="!hasRecentBackup"
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
                  :disabled="!hasRecentBackup"
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

      <!-- Backup History -->
      <div
        v-if="backupRuns.length > 0"
        class="mt-6"
      >
        <div class="shadow rounded-lg">
          <div class="px-4 py-4 bg-card rounded-t-lg border-b border-border flex items-center justify-between">
            <span class="font-bold text-foreground">{{ __("Backup History") }}</span>
            <Button
              size="sm"
              variant="outline"
              :disabled="isReloading"
              @click="reloadBackupHistory"
            >
              {{ __("Reload") }}
            </Button>
          </div>

          <div class="bg-card rounded-b-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
              <thead>
                <tr>
                  <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    {{ __("Filename") }}
                  </th>
                  <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    {{ __("Created At") }}
                  </th>
                  <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    {{ __("Size") }}
                  </th>
                  <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    {{ __("Status") }}
                  </th>
                  <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    {{ __("Actions") }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border">
                <tr
                  v-for="run in backupRuns"
                  :key="run.id"
                >
                  <td class="px-5 py-4 text-sm text-foreground whitespace-nowrap">
                    {{ run.filename ?? "-" }}
                  </td>
                  <td class="px-5 py-4 text-sm text-muted-foreground whitespace-nowrap">
                    {{ run.completed_at ? formatTimeAgoToNow(run.completed_at) : formatTimeAgoToNow(run.started_at) }}
                  </td>
                  <td class="px-5 py-4 text-sm text-muted-foreground whitespace-nowrap">
                    {{ formatBytes(run.file_size) }}
                  </td>
                  <td class="px-5 py-4 text-sm whitespace-nowrap">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-500/10 text-green-600 dark:text-green-400': run.status === 'completed',
                        'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400': run.status === 'pending',
                        'bg-red-500/10 text-red-600 dark:text-red-400': run.status === 'failed',
                      }"
                    >
                      {{ run.status }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-sm text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                      <a
                        v-if="run.status === 'completed'"
                        :href="route('admin.setting.danger.backup.download', run.id)"
                      >
                        <Button size="sm">
                          {{ __("Download") }}
                        </Button>
                      </a>
                      <Button
                        size="sm"
                        variant="destructive"
                        @click="openDeleteBackupConfirmModal(run)"
                      >
                        {{ __("Delete") }}
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
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
            :variant="modalMethod === 'post' ? 'default' : 'destructive'"
            @click="confirmAction"
          >
            {{ modalMethod === 'post' ? __("Confirm") : __("Confirm Delete") }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>
