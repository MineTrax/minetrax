<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import { useTranslations } from '@/Composables/useTranslations';
import Form from 'vform';

const { __ } = useTranslations();

const props = defineProps({
    settings: Object,
    variables_for_rating_static: Object,
    variables_for_score_static: Object,
    variables_for_score_dynamic: Object,
    variables_for_rating_dynamic: Object,
    math_functions_for_rating: Object,
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
        text: __('Player Settings'),
        current: true,
    }
];

const showHelpForRating = ref(false);
const showHelpForScore = ref(false);

const form = useForm({
    is_custom_rating_enabled: props.settings.is_custom_rating_enabled,
    custom_rating_expression: props.settings.custom_rating_expression,
    last_seen_day_for_active: props.settings.last_seen_day_for_active,
    is_custom_score_enabled: props.settings.is_custom_score_enabled,
    custom_score_expression: props.settings.custom_score_expression,
    show_player_intel_to: props.settings.show_player_intel_to,
});

const rating_expression_validation_form = new Form({
    player_username: null,
    custom_rating_expression: null,
});
const validator_rating_value = ref(null);
const validator_rating_exception = ref(null);
const isValidatingRating = ref(false);

const score_expression_validation_form = new Form({
    player_username: null,
    custom_score_expression: null,
});
const validator_score_value = ref(null);
const validator_score_exception = ref(null);
const isValidatingScore = ref(false);

function savePlayerSetting() {
    form.post(route('admin.setting.player.update'), {
        preserveScroll: true,
        onFinish: () => {
            validator_rating_value.value = null;
            validator_rating_exception.value = null;
            validator_score_value.value = null;
            validator_score_exception.value = null;
            rating_expression_validation_form.errors.clear();
            score_expression_validation_form.errors.clear();
        },
        onSuccess: () => {
            form.clearErrors();
            rating_expression_validation_form.errors.clear();
            score_expression_validation_form.errors.clear();
        }
    });
}

function validateRatingExpression() {
    isValidatingRating.value = true;
    rating_expression_validation_form.custom_rating_expression = form.custom_rating_expression;
    rating_expression_validation_form.post(route('admin.setting.player.validate-rating-expression'))
        .then(data => {
            validator_rating_value.value = data.data.rating;
            validator_rating_exception.value = null;
        })
        .catch(err => {
            if (err.response.status !== 422) {
                validator_rating_value.value = null;
                validator_rating_exception.value = err.response.data.message;
            } else {
                validator_rating_value.value = null;
                validator_rating_exception.value = null;
            }
        })
        .finally(() => {
            isValidatingRating.value = false;
            form.clearErrors();
        });
}

function validateScoreExpression() {
    isValidatingScore.value = true;
    score_expression_validation_form.custom_score_expression = form.custom_score_expression;
    score_expression_validation_form.post(route('admin.setting.player.validate-score-expression'))
        .then(data => {
            validator_score_value.value = data.data.score;
            validator_score_exception.value = null;
        })
        .catch(err => {
            if (err.response.status !== 422) {
                validator_score_value.value = null;
                validator_score_exception.value = err.response.data.message;
            } else {
                validator_score_value.value = null;
                validator_score_exception.value = null;
            }
        })
        .finally(() => {
            isValidatingScore.value = false;
            form.clearErrors();
        });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Player Settings')" />

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
          @submit.prevent="savePlayerSetting"
        >
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Rating Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Player Rating") }}
                    </legend>

                    <div class="space-y-6">
                      <div class="col-span-6 sm:col-span-4">
                        <XInput
                          id="last_seen_day_for_active"
                          v-model="form.last_seen_day_for_active"
                          :help="__('Number of days past today for player last seen to be count as active. Non active players will not be included in rating. Enter -1 to disable this feature.')"
                          :label="__('Last activity day for rating')"
                          :error="form.errors.last_seen_day_for_active"
                          type="text"
                          name="last_seen_day_for_active"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <XSwitch
                        id="is_custom_rating_enabled"
                        v-model="form.is_custom_rating_enabled"
                        :label="__('Enable Custom Player Rating Algorithm')"
                        :help="__('Use your own algorithm for rating players. Enable this only after there is at-least one player in the database.')"
                        name="is_custom_rating_enabled"
                        :error="form.errors.is_custom_rating_enabled"
                      />

                      <div
                        v-show="form.is_custom_rating_enabled"
                        class="space-y-4"
                      >
                        <XTextarea
                          id="custom_rating_expression"
                          v-model="form.custom_rating_expression"
                          :label="__('Rating Algorithm')"
                          :placeholder="__('Eg: ( $total_score - $total_deaths ) / 3')"
                          :help="__('Tip: For better experience Rating will be rounded from 0 to 10')"
                          :error="form.errors.custom_rating_expression || rating_expression_validation_form.errors.get('custom_rating_expression')"
                          name="custom_rating_expression"
                          :rows="4"
                          textarea-class="min-h-[3rem]"
                        />

                        <!-- Toggle Show Available Variables -->
                        <div class="text-foreground">
                          <button
                            type="button"
                            class="-mt-3 text-xs float-right text-primary focus:outline-none underline hover:text-primary"
                            @click="showHelpForRating = !showHelpForRating"
                          >
                            {{ !showHelpForRating ? __('Need help with this?') : __('-hide help') }}
                          </button>

                          <div
                            v-if="showHelpForRating"
                            class="flex flex-col"
                          >
                            <span class="font-bold">{{ __("Available Variables") }}</span>
                            <ul class="list-disc list px-4">
                              <li
                                v-for="(description, variable) in variables_for_rating_static"
                                :key="variable"
                                class="text-xs list-item"
                              >
                                <span class="font-bold">{{ variable }} </span>
                                -
                                <span>{{ description }}</span>
                              </li>
                            </ul>
                          </div>

                          <div
                            v-if="showHelpForRating"
                            class="flex flex-col mt-2"
                          >
                            <span class="font-bold">{{ __("Available Dynamic Variables (per server)") }}</span>
                            <ul class="list-disc list px-4">
                              <li
                                v-for="(description, variable) in variables_for_rating_dynamic"
                                :key="variable"
                                class="text-xs list-item"
                              >
                                <span class="font-bold">{{ variable }} </span>
                                -
                                <span>{{ description }}</span>
                              </li>
                            </ul>
                          </div>

                          <div
                            v-if="showHelpForRating"
                            class="flex flex-col mt-2"
                          >
                            <span class="font-bold">{{ __("Available Functions") }}</span>
                            <ul class="list-disc list px-4">
                              <li
                                v-for="(description, func) in math_functions_for_rating"
                                :key="func"
                                class="text-xs list-item"
                              >
                                <span class="font-bold">{{ func }} </span>
                                -
                                <span>{{ description }}</span>
                              </li>
                            </ul>
                          </div>
                        </div>

                        <!-- Test Algorithm -->
                        <div class="flex flex-col text-sm w-3/5 p-4 border-2 border-border rounded bg-muted/30">
                          <span class="text-foreground text-center text-sm font-semibold mb-4 -mt-7 bg-card w-28">
                            {{ __("Test Algorithm") }}
                          </span>
                          <div class="flex">
                            <XInput
                              id="player_username_rating"
                              v-model="rating_expression_validation_form.player_username"
                              :label="__('Player Username')"
                              :help="__('Username of an existing player to get against')"
                              type="text"
                              name="player_username"
                              :error="rating_expression_validation_form.errors.get('player_username')"
                              help-error-flex="flex-col"
                              :disabled="isValidatingRating"
                            />
                            <LoadingButton
                              type="button"
                              variant="outline"
                              class="ml-2 h-9 mt-7"
                              :loading="isValidatingRating"
                              @click="validateRatingExpression"
                            >
                              {{ __("Validate") }}
                            </LoadingButton>
                          </div>
                          <p
                            v-if="validator_rating_value"
                            class="p-2 mt-1 text-center bg-green-500 font-semibold rounded text-white"
                          >
                            {{ __("Success! Rating for this Player will be:") }} <span class="font-bold">{{ validator_rating_value }}</span>
                          </p>
                          <p
                            v-if="validator_rating_exception"
                            class="p-2 mt-1 text-center bg-destructive font-semibold rounded text-white"
                          >
                            {{ __("Oops!") }}&nbsp;{{ validator_rating_exception }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Score Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Player Score") }}
                    </legend>

                    <div class="space-y-6">
                      <XSwitch
                        id="is_custom_score_enabled"
                        v-model="form.is_custom_score_enabled"
                        :label="__('Enable Custom Player Score Algorithm')"
                        :help="__('Use your own algorithm for player score. Enable this only after there is at-least one player in the database.')"
                        name="is_custom_score_enabled"
                        :error="form.errors.is_custom_score_enabled"
                      />

                      <div
                        v-show="form.is_custom_score_enabled"
                        class="space-y-4"
                      >
                        <XTextarea
                          id="custom_score_expression"
                          v-model="form.custom_score_expression"
                          :label="__('Score Algorithm')"
                          :placeholder="__('Eg: ( $total_player_kills - $total_deaths ) / 2')"
                          :help="__('Note: Max possible score value is INT_MAX (2147483647)')"
                          :error="form.errors.custom_score_expression || score_expression_validation_form.errors.get('custom_score_expression')"
                          name="custom_score_expression"
                          :rows="4"
                          textarea-class="min-h-[3rem]"
                        />

                        <!-- Toggle Show Available Variables -->
                        <div class="text-foreground">
                          <button
                            type="button"
                            class="-mt-3 text-xs float-right text-primary focus:outline-none underline hover:text-primary"
                            @click="showHelpForScore = !showHelpForScore"
                          >
                            {{ !showHelpForScore ? __('Need help with this?') : __('-hide help') }}
                          </button>

                          <div
                            v-if="showHelpForScore"
                            class="flex flex-col"
                          >
                            <span class="font-bold">{{ __("Available Variables") }}</span>
                            <ul class="list-disc list px-4">
                              <li
                                v-for="(description, variable) in variables_for_score_static"
                                :key="variable"
                                class="text-xs list-item"
                              >
                                <span class="font-bold">{{ variable }} </span>
                                -
                                <span>{{ description }}</span>
                              </li>
                            </ul>
                          </div>

                          <div
                            v-if="showHelpForScore"
                            class="flex flex-col mt-2"
                          >
                            <span class="font-bold">{{ __("Available Dynamic Variables (per server)") }}</span>
                            <ul class="list-disc list px-4">
                              <li
                                v-for="(description, variable) in variables_for_score_dynamic"
                                :key="variable"
                                class="text-xs list-item"
                              >
                                <span class="font-bold">{{ variable }} </span>
                                -
                                <span>{{ description }}</span>
                              </li>
                            </ul>
                          </div>

                          <div
                            v-if="showHelpForScore"
                            class="flex flex-col mt-2"
                          >
                            <span class="font-bold">{{ __("Available Functions") }}</span>
                            <ul class="list-disc list px-4">
                              <li
                                v-for="(description, func) in math_functions_for_rating"
                                :key="func"
                                class="text-xs list-item"
                              >
                                <span class="font-bold">{{ func }} </span>
                                -
                                <span>{{ description }}</span>
                              </li>
                            </ul>
                          </div>
                        </div>

                        <!-- Test Algorithm -->
                        <div class="flex flex-col text-sm w-3/5 p-4 border-2 border-border rounded bg-muted/30">
                          <span class="text-foreground text-center text-sm font-semibold mb-4 -mt-7 bg-card w-28">
                            {{ __("Test Algorithm") }}
                          </span>
                          <div class="flex">
                            <XInput
                              id="player_username_score"
                              v-model="score_expression_validation_form.player_username"
                              :label="__('Player Username')"
                              :help="__('Username of an existing player to get against')"
                              type="text"
                              name="player_username"
                              :error="score_expression_validation_form.errors.get('player_username')"
                              help-error-flex="flex-col"
                              :disabled="isValidatingScore"
                            />
                            <LoadingButton
                              type="button"
                              variant="outline"
                              class="ml-2 h-9 mt-7"
                              :loading="isValidatingScore"
                              @click="validateScoreExpression"
                            >
                              {{ __("Validate") }}
                            </LoadingButton>
                          </div>
                          <p
                            v-if="validator_score_value"
                            class="p-2 mt-1 text-center bg-green-500 font-semibold rounded text-white"
                          >
                            {{ __("Success! Score for this Player will be:") }} <span class="font-bold">{{ validator_score_value }}</span>
                          </p>
                          <p
                            v-if="validator_score_exception"
                            class="p-2 mt-1 text-center bg-destructive font-semibold rounded text-white"
                          >
                            {{ __("Oops!") }}&nbsp;{{ validator_score_exception }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Player Intel Visibility -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Show Player Intel To") }}
                    </legend>

                    <div class="grid md:grid-cols-2 gap-4">
                      <label
                        for="show_player_intel_to_none"
                        class="flex items-start p-4 border border-border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                        :class="{ 'border-primary bg-primary/5': form.show_player_intel_to === 'none' }"
                      >
                        <input
                          id="show_player_intel_to_none"
                          v-model="form.show_player_intel_to"
                          type="radio"
                          value="none"
                          class="w-4 h-4 mt-0.5 accent-primary border-input focus:ring-primary"
                          name="show_player_intel_to"
                        >
                        <div class="ml-3">
                          <span class="font-medium text-foreground">{{ __("Only Superadmin") }}</span>
                          <p class="text-xs text-muted-foreground mt-1">
                            {{ __("Only superadmin role will be able to view player intel data for anyone.") }}
                          </p>
                        </div>
                      </label>

                      <label
                        for="show_player_intel_to_staff"
                        class="flex items-start p-4 border border-border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                        :class="{ 'border-primary bg-primary/5': form.show_player_intel_to === 'staff' }"
                      >
                        <input
                          id="show_player_intel_to_staff"
                          v-model="form.show_player_intel_to"
                          type="radio"
                          value="staff"
                          class="w-4 h-4 mt-0.5 accent-primary border-input focus:ring-primary"
                          name="show_player_intel_to"
                        >
                        <div class="ml-3">
                          <span class="font-medium text-foreground">{{ __("Staff Role and above") }}</span>
                          <p class="text-xs text-muted-foreground mt-1">
                            {{ __("Any staff role & superadmin will be able to view player intel for any player.") }}
                          </p>
                        </div>
                      </label>

                      <label
                        for="show_player_intel_to_self"
                        class="flex items-start p-4 border border-border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                        :class="{ 'border-primary bg-primary/5': form.show_player_intel_to === 'self' }"
                      >
                        <input
                          id="show_player_intel_to_self"
                          v-model="form.show_player_intel_to"
                          type="radio"
                          value="self"
                          class="w-4 h-4 mt-0.5 accent-primary border-input focus:ring-primary"
                          name="show_player_intel_to"
                        >
                        <div class="ml-3">
                          <span class="font-medium text-foreground">{{ __("Linked Account and above") }}</span>
                          <p class="text-xs text-muted-foreground mt-1">
                            {{ __("User who linked player can view player intel for the linked players. Superadmin & Staff role can view all players.") }}
                          </p>
                        </div>
                      </label>

                      <label
                        for="show_player_intel_to_login"
                        class="flex items-start p-4 border border-border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                        :class="{ 'border-primary bg-primary/5': form.show_player_intel_to === 'login' }"
                      >
                        <input
                          id="show_player_intel_to_login"
                          v-model="form.show_player_intel_to"
                          type="radio"
                          value="login"
                          class="w-4 h-4 mt-0.5 accent-primary border-input focus:ring-primary"
                          name="show_player_intel_to"
                        >
                        <div class="ml-3">
                          <span class="font-medium text-foreground">{{ __("Any Authenticated User") }}</span>
                          <p class="text-xs text-muted-foreground mt-1">
                            {{ __("Any authenticated user can view player intel data for any player.") }}
                          </p>
                        </div>
                      </label>

                      <label
                        for="show_player_intel_to_all"
                        class="flex items-start p-4 border border-border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                        :class="{ 'border-primary bg-primary/5': form.show_player_intel_to === 'all' }"
                      >
                        <input
                          id="show_player_intel_to_all"
                          v-model="form.show_player_intel_to"
                          type="radio"
                          value="all"
                          class="w-4 h-4 mt-0.5 accent-primary border-input focus:ring-primary"
                          name="show_player_intel_to"
                        >
                        <div class="ml-3">
                          <span class="font-medium text-foreground">{{ __("Public") }}</span>
                          <p class="text-xs text-muted-foreground mt-1">
                            {{ __("Any user or guest visiting the website can view player intel for any player.") }}
                          </p>
                        </div>
                      </label>
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
                {{ __("Save Player Settings") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
