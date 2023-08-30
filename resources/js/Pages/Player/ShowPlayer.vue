<template>
  <app-layout>
    <app-head :title="__(':username - Player Details', {username: player.username})" />

    <div class="px-2 py-4 md:py-12 md:px-10 max-w-7xl mx-auto space-y-4">
      <PlayerSubMenu
        :player="player"
        :can-show-player-intel="canShowPlayerIntel"
      />

      <div
        class="flex justify-between items-center shadow bg-white dark:bg-cool-gray-800 rounded p-3"
      >
        <div
          id="position"
          class="flex flex-col items-center justify-center"
        >
          <div class="flex items-center text-light-blue-400 font-extrabold">
            <span
              v-if="player.position"
              v-tippy
              :title="__('Position: :position', {position: player.position})"
              class="
                border-2
                rounded
                text-2xl
                px-2
                py-1
                border-light-blue-300
                bg-light-blue-50
                dark:bg-cool-gray-800
              "
            >
              #{{ player.position }}
            </span>
            <span
              v-else
              v-tippy
              class="italic text-sm text-gray-500 dark:text-gray-400"
              :title="__('Position: None')"
            >{{ __("None") }}</span>
          </div>

          <span class="text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"> {{ __("Position") }} </span>
        </div>

        <div
          id="rating"
          class="flex flex-col items-center justify-center"
        >
          <div class="flex">
            <icon
              v-if="player.rating != null"
              v-tippy
              :name="`rating-${player.rating}`"
              :title="__('Rating: :rating',{rating: player.rating})"
              class="w-12 h-12 focus:outline-none"
            />
            <p
              v-else
              v-tippy
              class="italic text-sm text-gray-500 dark:text-gray-400"
              :title="__('Rating: None')"
            >
              {{ __("None") }}
            </p>
          </div>

          <span class="text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"> {{ __("Rating") }} </span>
        </div>

        <div
          id="rank"
          class="flex flex-col items-center justify-center"
        >
          <div class="flex items-center justify-center space-x-2">
            <img
              v-if="player.rank && player.rank.photo_url"
              v-tippy
              :alt="player.rank.name"
              :src="player.rank.photo_url"
              :title="__('Rank: :rank', {rank: player.rank.name})"
              class="h-12 w-12 focus:outline-none"
            >
            <p
              v-else
              v-tippy
              class="italic text-sm text-gray-500 dark:text-gray-400"
              :title="__('Rank: None')"
            >
              {{ __("None") }}
            </p>
          </div>

          <span class="text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"> {{ __("Rank") }} </span>
        </div>

        <div
          id="country"
          class="flex flex-col items-center justify-center"
        >
          <img
            v-if="player.country && player.country.name"
            v-tippy
            :alt="player.country.name"
            :src="player.country.photo_path"
            :title="__('Country: :country', {country: player.country.name})"
            class="h-12 w-12 -mt-0.5 focus:outline-none"
          >
          <p
            v-else
            v-tippy
            class="italic text-sm text-gray-500 dark:text-gray-400"
            :title="__('Country: None')"
          >
            {{ __("Unknown") }}
          </p>

          <span class="text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"> {{ __("Country") }} </span>
        </div>
      </div>

      <div class="flex flex-col-reverse md:flex-row md:justify-between md:space-x-4">
        <div
          class="shadow mt-4 md:mt-0 bg-white dark:bg-cool-gray-800 rounded relative flex items-center justify-center"
        >
          <button
            class="focus:outline-none absolute top-2 left-2"
            @click="toggle3dPlayerAnimation"
          >
            <icon
              v-if="playerAnimationEnabled"
              class="w-6 h-6 text-red-300 dark:text-red-500"
              name="pause"
            />
            <icon
              v-else
              class="w-6 h-6 text-green-300 dark:text-green-500"
              name="play"
            />
          </button>
          <canvas id="skin_container" />
        </div>

        <div class="flex flex-col w-full space-y-4">
          <div
            v-if="!player.is_active"
            class="shadow bg-white dark:bg-cool-gray-800 rounded w-full p-2 md:p-5 text-red-400 dark:text-red-500 italic text-center"
          >
            {{ __("Player is inactive and will not be considered for rating.") }}
          </div>
          <div class="shadow bg-white dark:bg-cool-gray-800 rounded w-full p-2 md:p-5">
            <div class="flex justify-between space-x-2 items-center mb-2">
              <h1 class="text-2xl text-gray-800 dark:text-gray-200 font-extrabold">
                {{ player.username }}
              </h1>
              <h2 class="text-gray-400 font-semibold text-xs md:text-sm">
                {{ player.uuid }}
              </h2>
            </div>

            <div
              id="stats"
              class="text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-400 space-y-4"
            >
              <div
                id="first"
                class="flex justify-between pb-4 border-b border-gray-200 dark:border-gray-700"
              >
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="h-5 w-5 text-green-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Position") }}
                      </p>
                    </div>
                    <p class="font-bold">
                      {{ player.position }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Score") }}
                      </p>
                    </div>
                    <p class="font-bold">
                      {{ player.total_score }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-orange-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                        <path
                          d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Rating") }}
                      </p>
                    </div>
                    <p>
                      <span
                        v-if="player.rating !== null"
                        class="font-bold"
                      >{{ player.rating }}</span>
                      <span
                        v-else
                        class="italic text-gray-500"
                      >{{ __("None") }}</span>
                    </p>
                  </div>
                </div>

                <div class="flex flex-1 justify-center items-center">
                  <img
                    :src="player.avatar_url"
                    alt="Avatar"
                    class="h-30 w-30 rounded"
                  >
                </div>

                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-green-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Mob Kills") }}
                      </p>
                    </div>
                    <p class="font-bold">
                      {{ player.total_mob_kills }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-pink-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Player Kills") }}
                      </p>
                    </div>
                    <p class="font-bold">
                      {{ player.total_player_kills }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-red-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Deaths") }}
                      </p>
                    </div>
                    <p class="font-bold">
                      {{ player.total_deaths }}
                    </p>
                  </div>
                </div>
              </div>

              <div
                id="second"
                class="
                flex
                justify-between
                pb-4
                border-b border-gray-200 dark:border-gray-700
                space-x-8
              "
              >
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Rank") }}
                      </p>
                    </div>
                    <p>
                      <span v-if="player.rank">{{ player.rank.name }}</span>
                      <span
                        v-else
                        class="italic text-gray-500"
                      >{{ __("None") }}</span>
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-green-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Country") }}
                      </p>
                    </div>
                    <p>{{ player.country.name }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Total Playtime") }}
                      </p>
                    </div>
                    <p>
                      {{ secondsToHMS(player.play_time, true) }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-gray-700 dark:text-gray-200"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Times Slept") }}
                      </p>
                    </div>
                    <p>{{ player.total_sleep_in_bed }}</p>
                  </div>
                </div>
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-orange-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Next Rank") }}
                      </p>
                    </div>
                    <p>
                      <span v-if="player.next_rank">{{ player.next_rank }}</span>
                      <span
                        v-else
                        class="italic text-gray-500"
                      >{{ __("None") }}</span>
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Sessions") }}
                      </p>
                    </div>
                    <p>{{ player.total_leave_game }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-yellow-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Total Afktime") }}
                      </p>
                    </div>
                    <p>
                      {{ secondsToHMS(player.afk_time, true) }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-green-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Servers Played") }}
                      </p>
                    </div>
                    <p>{{ player.servers_count }}</p>
                  </div>
                </div>
              </div>

              <div
                id="third"
                class="flex justify-between pb-4 border-b border-gray-200 dark:border-gray-700 space-x-8"
              >
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Items Mined") }}
                      </p>
                    </div>
                    <p>{{ player.total_mined }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-green-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M4.293 15.707a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 0zm0-6a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 5.414 5.707 9.707a1 1 0 01-1.414 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Items Crafted") }}
                      </p>
                    </div>
                    <p>{{ player.total_crafted }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-purple-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M4.293 15.707a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 0zm0-6a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 5.414 5.707 9.707a1 1 0 01-1.414 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Items Picked Up") }}
                      </p>
                    </div>
                    <p>{{ player.total_picked_up }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-red-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M15.707 4.293a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 011.414-1.414L10 8.586l4.293-4.293a1 1 0 011.414 0zm0 6a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 111.414-1.414L10 14.586l4.293-4.293a1 1 0 011.414 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Items Broken") }}
                      </p>
                    </div>
                    <p>{{ player.total_broken }}</p>
                  </div>
                </div>
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-purple-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Items Used") }}
                      </p>
                    </div>
                    <p>{{ player.total_used }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-yellow-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Distance Walked") }}
                      </p>
                    </div>
                    <p>{{ millify(player.distance_traveled) }} {{ __("meters") }}</p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Claimed By") }}
                      </p>
                    </div>
                    <p>
                      <inertia-link
                        v-if="player.owner"
                        class="font-bold text-light-blue-400 hover:text-light-blue-600"
                        as="a"
                        :href="route('user.public.get', player.owner.username)"
                      >
                        @{{ player.owner.username }}
                      </inertia-link>
                      <span
                        v-else
                        class="italic text-gray-500"
                      >{{ __("None") }}</span>
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-red-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Favorite Server") }}
                      </p>
                    </div>
                    <p
                      v-if="!player.favorite_server"
                      class="text-gray-400 italic"
                    >
                      {{ __("None") }}
                    </p>
                    <p
                      v-else
                      v-tippy
                      class="focus:outline-none"
                      :title="player.favorite_server.hostname"
                    >
                      {{ player.favorite_server.name }}
                    </p>
                  </div>
                </div>
              </div>

              <div
                id="fourth"
                class="flex justify-between pb-4 space-x-8"
              >
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-light-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Joined") }}
                      </p>
                    </div>
                    <p
                      v-tippy
                      class="ml-1 focus:outline-none"
                      :content="formatToDayDateString(player.first_seen_at)"
                    >
                      {{
                        player.first_seen_at ? formatTimeAgoToNow(player.first_seen_at) : "unknown"
                      }}
                    </p>
                  </div>
                </div>
                <div class="flex-1 space-y-4">
                  <div class="flex justify-between">
                    <div class="flex">
                      <svg
                        class="w-5 h-5 text-green-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path
                          fill-rule="evenodd"
                          d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <p class="ml-1">
                        {{ __("Last Seen") }}
                      </p>
                    </div>
                    <p
                      v-tippy
                      class="ml-1 focus:outline-none"
                      :content="formatToDayDateString(player.last_seen_at)"
                    >
                      {{
                        player.last_seen_at ? formatTimeAgoToNow(player.last_seen_at) : "unknown"
                      }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import * as skinview3d from 'skinview3d';
import {useHelpers} from '@/Composables/useHelpers';
import millify from 'millify';
import PlayerSubMenu from '@/Shared/PlayerSubMenu.vue';

export default {

    components: {
        PlayerSubMenu,
        Icon,
        AppLayout,
    },
    props: {
        player: Object,
        canShowPlayerIntel: Boolean,
    },
    setup() {
        const {secondsToHMS, formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {secondsToHMS, formatTimeAgoToNow, formatToDayDateString, millify};
    },
    data() {
        return {
            playerAnimationEnabled: true,
            skinViewer: null,
        };
    },
    mounted() {
        this.skinViewer = new skinview3d.SkinViewer({
            canvas: document.getElementById('skin_container'),
            width: 300,
            height: 500,
            skin: route('player.skin.get', {uuid: this.player.uuid, username: this.player.username}),
        });
        let control = skinview3d.createOrbitControls(this.skinViewer);
        control.enableRotate = true;
        control.enableZoom = false;
        control.enablePan = false;
        let walk = this.skinViewer.animations.add(skinview3d.WalkingAnimation);
        walk.speed = 0.1;
        let rotate = this.skinViewer.animations.add(skinview3d.RotatingAnimation);
        rotate.speed = 0.5;
    },

    methods: {
        toggle3dPlayerAnimation: function () {
            if (this.playerAnimationEnabled) {
                // Disable Animation
                this.skinViewer.animations.paused = true;
                this.playerAnimationEnabled = false;
            } else {
                // Enable Animation
                this.skinViewer.animations.paused = false;
                this.playerAnimationEnabled = true;
            }
        },
    },
};
</script>
