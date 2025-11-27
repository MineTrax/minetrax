<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/vue3';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();

const props = defineProps({
    server: {
        type: Object,
        required: true,
    },
    apiKey: {
        type: String,
        required: true,
    },
    apiSecret: {
        type: String,
        required: true,
    },
    apiHost: {
        type: String,
        required: true,
    },
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Servers'),
        url: route('admin.server.index'),
        current: false,
    },
    {
        text: __('Server Created'),
        current: true,
    }
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Server Created Successfully!')" />

    <div class="px-10 py-8 mx-auto max-w-4xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="bg-card shadow rounded-lg p-6 mt-6">
        <div class="flex flex-col items-center justify-center">
          <CheckCircleIcon
            class="h-32 text-success-500"
            aria-hidden="true"
          />
          <h1 class="text-2xl font-bold text-success-500">
            {{ __("Server Added Successfully!") }}
          </h1>

          <h2 class="uppercase font-bold mt-3 text-foreground">
            {{ __("Follow below steps to add the Plugin!") }}
          </h2>
        </div>

        <div class="flex flex-col space-y-4 mt-6 prose-lg prose dark:prose-invert max-w-none">
          <p>
            {{ __("Download latest version of the MineTrax.jar Plugin and upload it into 'plugins' folder of your server.") }}
            <a
              target="_blank"
              class="text-primary hover:text-primary whitespace-nowrap"
              href="https://github.com/MineTrax/plugin/releases/latest"
            >{{ __("Click here to Download") }}</a>
          </p>

          <p>
            {{ __("Restart your server once so that the plugin can generate the config file inside") }} <kbd class="bg-muted px-1.5 py-0.5 rounded text-sm">plugins/Minetrax/config.yml</kbd>.
          </p>

          <div>
            {{ __("Open the config file and update the following details in it as provided below") }}:
            <pre class="bg-muted rounded-lg p-4 text-sm overflow-x-auto">enabled: true
api-host: {{ apiHost }}
api-key: {{ apiKey }}
api-secret: {{ apiSecret }}
server-id: {{ server.id }}
webquery-host: 0.0.0.0
webquery-port: {{ server.webquery_port }}</pre>
          </div>

          <p>
            {{ __("Restart your server again and you are all set!") }}
          </p>
        </div>

        <div class="flex justify-end mt-6">
          <Button as-child>
            <Link :href="route('admin.server.index')">
              {{ __("Go back to Server List") }}
            </Link>
          </Button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
