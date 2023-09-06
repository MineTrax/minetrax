<template>
  <AdminLayout>
    <app-head
      :title="__('Player Settings')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full rounded">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              {{ __("Player Settings") }}
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <form
                    autocomplete="off"
                    @submit.prevent="savePlayerSetting"
                  >
                    <div class="shadow overflow-hidden sm:rounded-md">
                      <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                          <div class="col-span-6 sm:col-spam-4">
                            <x-input
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

                          <div class="col-span-6 sm:col-span-6">
                            <x-checkbox
                              id="is_custom_rating_enabled"
                              v-model="form.is_custom_rating_enabled"
                              :label="__('Enable Custom Player Rating Algorithm')"
                              :help="__('Use your own algorithm for rating players. Enable this only after there is at-least one player in the database.')"
                              name="is_custom_rating_enabled"
                              :error="form.errors.is_custom_rating_enabled"
                            />
                          </div>

                          <div
                            v-show="form.is_custom_rating_enabled"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-textarea
                              id="custom_rating_expression"
                              v-model="form.custom_rating_expression"
                              :label="__('Rating Algorithm')"
                              :help="__('Eg: ( $total_score - $total_deaths ) / 3 . Tip: For better experience Rating will be rounded from 0 to 10')"
                              :error="form.errors.custom_rating_expression || rating_expression_validation_form.errors.get('custom_rating_expression')"
                              name="custom_rating_expression"
                            />
                          </div>

                          <!-- Toggle Show Available Variables -->
                          <div
                            v-if="form.is_custom_rating_enabled"
                            class="col-span-6 text-gray-800"
                          >
                            <button
                              type="button"
                              class="-mt-7 text-xs float-right text-light-blue-500 focus:outline-none underline hover:text-light-blue-400"
                              @click="showHelpForRating = !showHelpForRating"
                            >
                              {{ !showHelpForRating ? __('Need help with this?') : __('-hide help') }}
                            </button>
                            <div
                              v-if="showHelpForRating"
                              class="flex flex-col dark:text-gray-400"
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
                              class="flex flex-col mt-2 dark:text-gray-400"
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
                              class="flex flex-col mt-2 dark:text-gray-400"
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

                          <!-- Show option to verify/test expression against a player -->
                          <div
                            v-if="form.is_custom_rating_enabled"
                            class="col-span-6 sm:col-span-6 flex flex-col text-sm w-3/5 p-4 border-2 border-gray-200 dark:border-gray-700 rounded"
                          >
                            <span
                              class="text-gray-500 dark:text-gray-400 text-center text-sm font-semibold mb-4 -mt-7 bg-white dark:bg-cool-gray-800 w-28"
                            >{{ __("Test Algorithm") }}</span>
                            <div class="flex">
                              <x-input
                                id="player_username"
                                v-model="rating_expression_validation_form.player_username"
                                :label="__('Player Username')"
                                :help="__('Username of an existing player to get against')"
                                type="text"
                                name="player_username"
                                :error="rating_expression_validation_form.errors.get('player_username')"
                                help-error-flex="flex-col"
                              />
                              <loading-button
                                :loading="rating_expression_validation_form.busy"
                                class="ml-2 h-14 inline-flex justify-center py-2 px-4 border border-2 border-light-blue-500 shadow-sm text-sm font-bold rounded-md text-light-blue-500 hover:bg-light-blue-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                                type="button"
                                @click="validateRatingExpression"
                              >
                                {{ __("Validate") }}
                              </loading-button>
                            </div>
                            <p
                              v-if="validator_rating_value"
                              class="p-2 mt-1 text-center bg-green-500 font-semibold rounded text-white"
                            >
                              {{ __("Success! Rating for this Player will be:") }} <span class="font-bold">{{ validator_rating_value }}</span>
                            </p>
                            <p
                              v-if="validator_rating_exception"
                              class="p-2 mt-1 text-center bg-red-500 font-semibold rounded text-white"
                            >
                              {{ __("Oops!") }}&nbsp;{{ validator_rating_exception }}
                            </p>
                          </div>

                          <div class="col-span-6 sm:col-span-6 border-t border-gray-300 dark:border-gray-700 pt-4">
                            <x-checkbox
                              id="is_custom_score_enabled"
                              v-model="form.is_custom_score_enabled"
                              :label="__('Enable Custom Player Score Algorithm')"
                              :help="__('Use your own algorithm for player score. Enable this only after there is at-least one player in the database.')"
                              name="is_custom_score_enabled"
                              :error="form.errors.is_custom_score_enabled"
                            />
                          </div>

                          <div
                            v-show="form.is_custom_score_enabled"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-textarea
                              id="custom_score_expression"
                              v-model="form.custom_score_expression"
                              :label="__('Score Algorithm')"
                              :help="__('Eg: ( $total_player_kills - $total_deaths ) / 2 .')"
                              :error="form.errors.custom_score_expression || score_expression_validation_form.errors.get('custom_score_expression')"
                              name="custom_score_expression"
                            />
                          </div>

                          <!-- Toggle Show Available Variables -->
                          <div
                            v-if="form.is_custom_score_enabled"
                            class="col-span-6 text-gray-800"
                          >
                            <button
                              type="button"
                              class="-mt-7 text-xs float-right text-light-blue-500 focus:outline-none underline hover:text-light-blue-400"
                              @click="showHelpForScore = !showHelpForScore"
                            >
                              {{ !showHelpForScore ? 'Need help with this?' : '-hide help' }}
                            </button>
                            <div
                              v-if="showHelpForScore"
                              class="flex flex-col dark:text-gray-400"
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
                              class="flex flex-col mt-2 dark:text-gray-400"
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
                              class="flex flex-col mt-2 dark:text-gray-400"
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

                          <!-- Show option to verify/test expression against a player -->
                          <div
                            v-if="form.is_custom_score_enabled"
                            class="col-span-6 sm:col-span-6 flex flex-col text-sm w-3/5 p-4 border-2 border-gray-200 dark:border-gray-700 rounded"
                          >
                            <span
                              class="text-gray-500 dark:text-gray-400 text-center text-sm font-semibold mb-4 -mt-7 bg-white dark:bg-cool-gray-800 w-28"
                            >{{ __("Test Algorithm") }}</span>
                            <div class="flex">
                              <x-input
                                id="player_username"
                                v-model="score_expression_validation_form.player_username"
                                :label="__('Player Username')"
                                :help="__('Username of an existing player to get against')"
                                type="text"
                                name="player_username"
                                :error="score_expression_validation_form.errors.get('player_username')"
                                help-error-flex="flex-col"
                              />
                              <loading-button
                                :loading="score_expression_validation_form.busy"
                                class="ml-2 h-14 inline-flex justify-center py-2 px-4 border border-2 border-light-blue-500 shadow-sm text-sm font-bold rounded-md text-light-blue-500 hover:bg-light-blue-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                                type="button"
                                @click="validateScoreExpression"
                              >
                                {{ __("Validate") }}
                              </loading-button>
                            </div>
                            <p
                              v-if="validator_score_value"
                              class="p-2 mt-1 text-center bg-green-500 font-semibold rounded text-white"
                            >
                              {{ __("Success! Score for this Player will be:") }} <span class="font-bold">{{ validator_score_value }}</span>
                            </p>
                            <p
                              v-if="validator_score_exception"
                              class="p-2 mt-1 text-center bg-red-500 font-semibold rounded text-white"
                            >
                              {{ __("Oops!") }}&nbsp;{{ validator_score_exception }}
                            </p>
                          </div>

                          <div class="col-span-6 sm:col-span-6 border-t border-gray-300 dark:border-gray-700 pt-4 space-y-3">
                            <p class="text-sm text-gray-700 font-bold dark:text-gray-300">
                              {{ __("Show Player Intel To") }}
                            </p>

                            <div class="grid md:grid-cols-2 gap-3">
                              <div class="flex">
                                <div class="flex items-center h-5">
                                  <input
                                    id="show_player_intel_to_none"
                                    v-model="form.show_player_intel_to"
                                    type="radio"
                                    value="none"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    name="show_player_intel_to"
                                  >
                                </div>
                                <div class="ml-2 text-sm">
                                  <label
                                    for="show_player_intel_to_none"
                                    class="font-medium text-gray-900 dark:text-gray-300"
                                  >{{ __("Only Superadmin") }}</label>
                                  <p
                                    class="text-xs font-normal text-gray-500 dark:text-gray-300"
                                  >
                                    {{ __("Only superadmin role will be able to view player intel data for anyone.") }}
                                  </p>
                                </div>
                              </div>
                              <div class="flex">
                                <div class="flex items-center h-5">
                                  <input
                                    id="show_player_intel_to_staff"
                                    v-model="form.show_player_intel_to"
                                    type="radio"
                                    value="staff"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    name="show_player_intel_to"
                                  >
                                </div>
                                <div class="ml-2 text-sm">
                                  <label
                                    for="show_player_intel_to_staff"
                                    class="font-medium text-gray-900 dark:text-gray-300"
                                  >{{ __("Staff Role and above") }}</label>
                                  <p
                                    class="text-xs font-normal text-gray-500 dark:text-gray-300"
                                  >
                                    {{ __("Any staff role & superadmin will be able to view player intel for any player.") }}
                                  </p>
                                </div>
                              </div>
                              <div class="flex">
                                <div class="flex items-center h-5">
                                  <input
                                    id="show_player_intel_to_self"
                                    v-model="form.show_player_intel_to"
                                    type="radio"
                                    value="self"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    name="show_player_intel_to"
                                  >
                                </div>
                                <div class="ml-2 text-sm">
                                  <label
                                    for="show_player_intel_to_self"
                                    class="font-medium text-gray-900 dark:text-gray-300"
                                  >{{ __("Linked Account and above") }}</label>
                                  <p
                                    class="text-xs font-normal text-gray-500 dark:text-gray-300"
                                  >
                                    {{ __("User who linked player can view player intel for the linked players. Superadmin & Staff role can view all players.") }}
                                  </p>
                                </div>
                              </div>

                              <div class="flex">
                                <div class="flex items-center h-5">
                                  <input
                                    id="show_player_intel_to_login"
                                    v-model="form.show_player_intel_to"
                                    type="radio"
                                    value="login"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    name="show_player_intel_to"
                                  >
                                </div>
                                <div class="ml-2 text-sm">
                                  <label
                                    for="show_player_intel_to_login"
                                    class="font-medium text-gray-900 dark:text-gray-300"
                                  >{{ __("Any Authenticated User") }}</label>
                                  <p
                                    class="text-xs font-normal text-gray-500 dark:text-gray-300"
                                  >
                                    {{ __("Any authenticated user can view player intel data for any player.") }}
                                  </p>
                                </div>
                              </div>

                              <div class="flex">
                                <div class="flex items-center h-5">
                                  <input
                                    id="show_player_intel_to_all"
                                    v-model="form.show_player_intel_to"
                                    type="radio"
                                    value="all"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    name="show_player_intel_to"
                                  >
                                </div>
                                <div class="ml-2 text-sm">
                                  <label
                                    for="show_player_intel_to_all"
                                    class="font-medium text-gray-900 dark:text-gray-300"
                                  >{{ __("Public") }}</label>
                                  <p
                                    class="text-xs font-normal text-gray-500 dark:text-gray-300"
                                  >
                                    {{ __("Any user or guest visiting the website can view player intel for any player.") }}
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                        <loading-button
                          :loading="form.processing"
                          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          {{ __("Save Player Settings") }}
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

<script>
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import Form from 'vform';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XTextarea,
        XCheckbox,
        LoadingButton,
        XInput,
    },
    props: {
        settings: Object,
        variables_for_rating_static: Object,
        variables_for_score_static: Object,
        variables_for_score_dynamic: Object,
        variables_for_rating_dynamic: Object,
        math_functions_for_rating: Object,
    },
    data() {
        return {
            showHelpForRating: false,
            showHelpForScore: false,
            form: useForm({
                is_custom_rating_enabled: this.settings.is_custom_rating_enabled,
                custom_rating_expression: this.settings.custom_rating_expression,
                last_seen_day_for_active: this.settings.last_seen_day_for_active,
                is_custom_score_enabled: this.settings.is_custom_score_enabled,
                custom_score_expression: this.settings.custom_score_expression,
                show_player_intel_to: this.settings.show_player_intel_to,
            }),

            rating_expression_validation_form: new Form({
                player_username: null,
                custom_rating_expression: null,
            }),
            validator_rating_value: null,
            validator_rating_exception: null,

            score_expression_validation_form: new Form({
                player_username: null,
                custom_score_expression: null,
            }),
            validator_score_value: null,
            validator_score_exception: null,
        };
    },
    methods: {
        savePlayerSetting() {
            this.form.post(route('admin.setting.player.update'), {
                preserveScroll: true,
                onFinish: () => {
                    this.validator_rating_value = null;
                    this.validator_rating_exception = null;
                    this.validator_score_value = null;
                    this.validator_score_exception = null;
                    this.rating_expression_validation_form.errors.clear();
                    this.score_expression_validation_form.errors.clear();
                },
                onSuccess: () => {
                    this.form.clearErrors();
                    this.rating_expression_validation_form.errors.clear();
                    this.score_expression_validation_form.errors.clear();
                }
            });
        },

        validateRatingExpression() {
            this.rating_expression_validation_form.custom_rating_expression = this.form.custom_rating_expression;
            this.rating_expression_validation_form.post(route('admin.setting.player.validate-rating-expression'))
                .then(data => {
                    this.validator_rating_value = data.data.rating;
                    this.validator_rating_exception = null;
                })
                .catch(err => {
                    if (err.response.status !== 422) {
                        this.validator_rating_value = null;
                        this.validator_rating_exception = err.response.data.message;
                    } else {
                        this.validator_rating_value = null;
                        this.validator_rating_exception = null;
                    }
                })
                .finally(() => {
                    this.form.clearErrors();
                });
        },

        validateScoreExpression() {
            this.score_expression_validation_form.custom_score_expression = this.form.custom_score_expression;
            this.score_expression_validation_form.post(route('admin.setting.player.validate-score-expression'))
                .then(data => {
                    this.validator_score_value = data.data.score;
                    this.validator_score_exception = null;
                })
                .catch(err => {
                    if (err.response.status !== 422) {
                        this.validator_score_value = null;
                        this.validator_score_exception = err.response.data.message;
                    } else {
                        this.validator_score_value = null;
                        this.validator_score_exception = null;
                    }
                })
                .finally(() => {
                    this.form.clearErrors();
                });
        }
    }
};
</script>
