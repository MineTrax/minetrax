<script setup>
import { useTranslations } from "@/Composables/useTranslations";
import { useFormErrors } from "@/Composables/useFormErrors";
import XInput from "@/Components/Form/XInput.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import { Button } from "@/Components/ui/button";
import Multiselect from "vue-multiselect";
import Draggable from "vuedraggable";
import { ArrowsUpDownIcon, TrashIcon } from "@heroicons/vue/24/outline";

const { __ } = useTranslations();
// Laravel keys a per-item array failure as `commands.0.servers`, not `commands`, so reading the
// bare field name renders nothing and a rejected save looks like one that silently did nothing.
const { fieldError } = useFormErrors();

const props = defineProps({
    form: Object,
    servers: Array,
});

const serverLabel = (server) => `${server.name} (${server.hostname})`;

function addCommand() {
    props.form.commands.push({
        command: "",
        servers: [],
        delay_seconds: 0,
        is_player_online_required: false,
        sort_order: props.form.commands.length,
    });
}

function removeCommand(index) {
    props.form.commands.splice(index, 1);
}
</script>

<template>
  <div class="shadow rounded-lg card-clip-safe mb-6">
    <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
      <XSwitch
        id="is_command_execution_enabled"
        v-model="form.is_command_execution_enabled"
        :label="__('Run commands after a successful purchase made with this code?')"
        :help="__('A thank-you for the referrer, or a bonus for the buyer. Runs once per order, not once per item.')"
        :error="form.errors.is_command_execution_enabled"
        name="is_command_execution_enabled"
      />
    </div>

    <div
      v-if="form.is_command_execution_enabled"
      class="px-4 py-5 bg-card sm:p-6 border-b border-border"
    >
      <h3 class="text-lg font-medium text-foreground mb-1">
        {{ __("Minecraft Server Commands") }}
      </h3>
      <p class="text-sm text-muted-foreground mb-1">
        {{ __("Run once for the whole order when a purchase using this code is paid for. There is no expiry or refund set: when a referred order unwinds the commission is clawed back instead.") }}
      </p>
      <p class="text-sm text-muted-foreground mb-4">
        {{ __("Available placeholders: {PLAYER_USERNAME}, {PLAYER_UUID}, {ORDER_UUID}, {REFERRAL_CODE}, {REFERRER_NAME}") }}
      </p>

      <div class="space-y-4">
        <Draggable
          v-model="form.commands"
          :swap-threshold="0.65"
          class="space-y-3"
          handle=".drag-handle"
        >
          <template #item="{ element: command, index }">
            <div class="p-4 bg-muted/50 rounded-lg space-y-4">
              <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12 lg:col-span-1 flex gap-2 lg:mt-6 lg:flex-col">
                  <div class="drag-handle cursor-move">
                    <ArrowsUpDownIcon class="w-5 h-5 text-muted-foreground hover:text-foreground" />
                  </div>
                  <button
                    type="button"
                    class="focus:outline-hidden group cursor-pointer"
                    @click="removeCommand(index)"
                  >
                    <TrashIcon class="w-5 h-5 text-muted-foreground group-hover:text-destructive" />
                  </button>
                </div>

                <div class="col-span-12 sm:col-span-9 lg:col-span-9">
                  <XInput
                    v-model="command.command"
                    :label="__('Command')"
                    :error="form.errors[`commands.${index}.command`]"
                    type="text"
                    name="command"
                  />
                </div>

                <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                  <XInput
                    v-model.number="command.delay_seconds"
                    :label="__('Delay (s)')"
                    :error="form.errors[`commands.${index}.delay_seconds`]"
                    type="number"
                    name="delay_seconds"
                    min="0"
                  />
                </div>

                <div class="col-span-12">
                  <label class="block text-sm font-medium text-foreground mb-2">{{ __("Run on servers") }}</label>
                  <Multiselect
                    v-model="command.servers"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="servers"
                    :custom-label="serverLabel"
                    track-by="id"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :searchable="true"
                    :placeholder="__('Leave empty to run on all servers')+'...'"
                  />
                  <p
                    v-if="fieldError(form.errors, `commands.${index}.servers`)"
                    class="text-xs text-destructive mt-1"
                  >
                    {{ fieldError(form.errors, `commands.${index}.servers`) }}
                  </p>
                </div>
              </div>

              <div class="border-t border-border pt-4">
                <XSwitch
                  :id="`command_online_${index}`"
                  v-model="command.is_player_online_required"
                  :label="__('Require player to be online')"
                  :help="__('This command only runs while the player is online on the target server. If they are offline it is queued and runs the moment they join.')"
                  :error="form.errors[`commands.${index}.is_player_online_required`]"
                  :name="`command_online_${index}`"
                />
              </div>
            </div>
          </template>
        </Draggable>

        <div class="flex justify-end mt-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            @click="addCommand"
          >
            {{ __("Add Command") }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
