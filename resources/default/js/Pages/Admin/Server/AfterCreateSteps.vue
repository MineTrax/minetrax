<template>
  <AdminLayout>
    <app-head
      :title="__('Server Created Successfully!')"
    />

    <div class="py-12 px-10 max-w-4xl mx-auto">
      <div class="bg-white shadow rounded p-6 dark:bg-surface-800">
        <div class="flex flex-col items-center justify-center">
          <CheckCircleIcon
            class="h-32 text-success-500"
            aria-hidden="true"
          />
          <h1 class="text-2xl font-bold text-success-500">
            {{ __("Server Added Successfully!") }}
          </h1>


          <h1 class="uppercase font-bold mt-3 dark:text-foreground text-foreground">
            {{ __("Follow below steps to add the Plugin!") }}
          </h1>
        </div>


        <div class="flex flex-col space-y-4 mt-6 prose-lg prose dark:prose-invert">
          <p>
            {{ __("Download latest version of the MineTrax.jar Plugin and upload it into 'plugins' folder of your server.") }}
            <a
              target="_blank"
              class="text-primary hover:text-primary whitespace-nowrap"
              href="https://github.com/MineTrax/plugin/releases/latest"
            >{{ __("Click here to Download") }}</a>
          </p>

          <p>
            {{ __("Restart your server once so that the plugin can generate the config file inside") }} <kbd>plugins/Minetrax/config.yml</kbd>.
          </p>
          <br>

          <div>
            {{ __("Open the config file and update the following details in it as provided below") }}:
            <pre class="dark:bg-surface-900">enabled: true
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

        <div class="flex justify-end mt-4">
          <InertiaLink
            as="a"
            class="text-primary hover:text-primary whitespace-nowrap"
            :href="route('admin.server.index')"
          >
            {{ __("Go back to Server List") }}
          </InertiaLink>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';

defineProps({
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
</script>
