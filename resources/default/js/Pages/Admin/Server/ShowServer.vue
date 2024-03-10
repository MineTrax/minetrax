<template>
  <AdminLayout>
    <app-head :title="__('Server #:id', {id: server.id})" />

    <div class="max-w-6xl px-10 py-12 mx-auto space-y-4">
      <h2 class="text-2xl text-gray-600 dark:text-gray-200">
        <span class="font-bold">#{{ server.id }}</span>
        -
        <span class="font-bold">{{ server.name }}</span>
      </h2>

      <server-sub-menu :id="server.id" />

      <div class="flex justify-between space-x-2">
        <overview-card
          :title="__('Online Players')"
          :value="onlinePlayers"
          :desc="__('Maximum limit: :max', {max: maxPlayersAllowed})"
          color="text-green-300"
          icon="users"
          class="flex-1"
        />
        <overview-card
          :title="__('Total PvP Kills')"
          :value="millify(serverAggrData.pvp_kills, {precision: 2})"
          :desc="__('Mob Kills: :mob_kills', {mob_kills: millify(serverAggrData.mob_kills, {precision: 2}) })"
          color="text-yellow-300"
          icon="swords-cross"
          class="flex-1"
        />
        <overview-card
          :title="__('Total Deaths')"
          :value="millify(serverAggrData.player_deaths, {precision: 2})"
          :desc="__('Total Damage: :player_damage_taken', { player_damage_taken: millify(serverAggrData.player_damage_taken, {precision: 2}) })"
          color="text-red-500"
          icon="skull-bones-outline"
          class="flex-1"
        />
        <overview-card
          :title="__('Total Players')"
          :value="serverAggrData.total_players"
          :desc="__(':active_players active players', {active_players: serverAggrData.active_players})"
          color="text-light-blue-300"
          icon="users"
          class="flex-1"
        />
      </div>

      <div
        id="server-general-data"
        class="w-full p-2 space-y-4 text-sm text-gray-700 bg-white rounded shadow dark:text-gray-300 dark:bg-cool-gray-800 md:p-5"
      >
        <div class="flex justify-between pb-4 space-x-8 border-b border-gray-200 dark:border-gray-700">
          <div class="flex-1 space-y-4">
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-light-blue-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M7 0a1.5 1.5 0 0 0-1.5 1.5v11a1.5 1.5 0 0 0 1.404 1.497c-.35.305-.872.678-1.628 1.056A.5.5 0 0 0 5.5 16h5a.5.5 0 0 0 .224-.947c-.756-.378-1.278-.75-1.628-1.056A1.5 1.5 0 0 0 10.5 12.5v-11A1.5 1.5 0 0 0 9 0H7Zm1 3a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Zm0 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Zm.5 1.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0ZM8 9a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Hostname") }}
                </p>
              </div>
              <p>
                <span>{{ server.hostname }}</span>
              </p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-green-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M5.525 3.025a3.5 3.5 0 0 1 4.95 0 .5.5 0 1 0 .707-.707 4.5 4.5 0 0 0-6.364 0 .5.5 0 0 0 .707.707Z"
                  />
                  <path
                    d="M6.94 4.44a1.5 1.5 0 0 1 2.12 0 .5.5 0 0 0 .708-.708 2.5 2.5 0 0 0-3.536 0 .5.5 0 0 0 .707.707Z"
                  />
                  <path
                    d="M2.974 2.342a.5.5 0 1 0-.948.316L3.806 8H1.5A1.5 1.5 0 0 0 0 9.5v2A1.5 1.5 0 0 0 1.5 13H2a.5.5 0 0 0 .5.5h2A.5.5 0 0 0 5 13h6a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5h.5a1.5 1.5 0 0 0 1.5-1.5v-2A1.5 1.5 0 0 0 14.5 8h-2.306l1.78-5.342a.5.5 0 1 0-.948-.316L11.14 8H4.86L2.974 2.342ZM2.5 11a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Zm4.5-.5a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0Zm2.5.5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Zm1.5-.5a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0Zm2 0a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0Z"
                  />
                  <path d="M8.5 5.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0Z" />
                </svg>
                <p class="ml-1">
                  {{ "IP Address" }}
                </p>
              </div>
              <p>{{ server.ip_address }}</p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-orange-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.444 12.444 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.518.518 0 0 0 .668.05A11.448 11.448 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049z"
                  />
                  <path
                    d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.455 9.455 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065zm-2.183 2.183c.226-.226.185-.605-.1-.75A6.473 6.473 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.478 5.478 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091l.016-.015zM9.06 12.44c.196-.196.198-.52-.04-.66A1.99 1.99 0 0 0 8 11.5a1.99 1.99 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Join / Query Port") }}
                </p>
              </div>
              <p>
                {{ server.join_port }} / {{ server.query_port }}
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
              <p>{{ server.country.name }}</p>
            </div>
          </div>
          <div class="flex-1 space-y-4">
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-orange-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M15.698 7.287 8.712.302a1.03 1.03 0 0 0-1.457 0l-1.45 1.45 1.84 1.84a1.223 1.223 0 0 1 1.55 1.56l1.773 1.774a1.224 1.224 0 0 1 1.267 2.025 1.226 1.226 0 0 1-2.002-1.334L8.58 5.963v4.353a1.226 1.226 0 1 1-1.008-.036V5.887a1.226 1.226 0 0 1-.666-1.608L5.093 2.465l-4.79 4.79a1.03 1.03 0 0 0 0 1.457l6.986 6.986a1.03 1.03 0 0 0 1.457 0l6.953-6.953a1.031 1.031 0 0 0 0-1.457"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Minecraft Version") }}
                </p>
              </div>
              <p>
                <span>{{ server.minecraft_version }}</span>
              </p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-light-blue-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M.036 3.314a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0L.314 3.964a.5.5 0 0 1-.278-.65zm0 3a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0L.314 6.964a.5.5 0 0 1-.278-.65zm0 3a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0L.314 9.964a.5.5 0 0 1-.278-.65zm0 3a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.757-.703a.5.5 0 0 1-.278-.65z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Server Type") }}
                </p>
              </div>
              <p>{{ server.type.key }}</p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-green-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.444 12.444 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.518.518 0 0 0 .668.05A11.448 11.448 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049z"
                  />
                  <path
                    d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.455 9.455 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065zm-2.183 2.183c.226-.226.185-.605-.1-.75A6.473 6.473 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.478 5.478 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091l.016-.015zM9.06 12.44c.196-.196.198-.52-.04-.66A1.99 1.99 0 0 0 8 11.5a1.99 1.99 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("WebQuery Port") }}
                </p>
              </div>
              <p
                class="focus:outline-none"
              >
                {{ server.webquery_port }}
              </p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-indigo-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path d="M9 7a1 1 0 0 1 1-1h5v2h-5a1 1 0 0 1-1-1zM1 9h4a1 1 0 0 1 0 2H1V9z" />
                  <path
                    d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Added At") }}
                </p>
              </div>
              <p>
                {{
                  formatToDayDateString(server.created_at)
                }}
              </p>
            </div>
          </div>
        </div>

        <div class="flex justify-between space-x-8 dark:border-gray-700">
          <div class="flex-1 space-y-4">
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-light-blue-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Ingame-Chat Enabled") }}
                </p>
              </div>
              <p>
                <span>{{ server.is_ingame_chat_enabled ? __('Yes') : __('No') }}</span>
              </p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-green-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M5.933.87a2.89 2.89 0 0 1 4.134 0l.622.638.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636zM7.002 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0zm1.602-2.027c.04-.534.198-.815.846-1.26.674-.475 1.05-1.09 1.05-1.986 0-1.325-.92-2.227-2.262-2.227-1.02 0-1.792.492-2.1 1.29A1.71 1.71 0 0 0 6 5.48c0 .393.203.64.545.64.272 0 .455-.147.564-.51.158-.592.525-.915 1.074-.915.61 0 1.03.446 1.03 1.084 0 .563-.208.885-.822 1.325-.619.433-.926.914-.926 1.64v.111c0 .428.208.745.585.745.336 0 .504-.24.554-.627z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Server Intel Enabled") }}
                </p>
              </div>
              <p>{{ server.is_server_intel_enabled ? __('Yes') : __('No') }}</p>
            </div>
          </div>
          <div class="flex-1 space-y-4">
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-orange-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"
                  />
                  <path
                    d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Is Player Intel Enabled") }}
                </p>
              </div>
              <p>
                <span>{{ server.is_player_intel_enabled ? __('Yes') : __('No') }}</span>
              </p>
            </div>
            <div class="flex justify-between">
              <div class="flex">
                <svg
                  class="w-5 h-5 text-light-blue-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 16 16"
                >
                  <path d="M9 7a1 1 0 0 1 1-1h5v2h-5a1 1 0 0 1-1-1zM1 9h4a1 1 0 0 1 0 2H1V9z" />
                  <path
                    d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"
                  />
                </svg>
                <p class="ml-1">
                  {{ __("Updated At") }}
                </p>
              </div>
              <p
                v-tippy
                :title="formatTimeAgoToNow(server.updated_at)"
              >
                {{ formatToDayDateString(server.updated_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>


      <div
        id="terminal-container"
        class="p-3 bg-white rounded shadow dark:bg-cool-gray-800"
      >
        <div class="flex justify-between">
          <h3 class="mb-1 font-extrabold text-gray-800 dark:text-gray-200">
            {{ __("Server Logs") }}
          </h3>
        </div>
        <div
          id="terminal"
          class="break-normal"
        />
        <form @submit.prevent="sendCommandToServer">
          <input
            ref="inputbox"
            v-model="commandText"
            :disabled="sendingCommand"
            aria-label="Commander"
            class="block w-full mt-1 bg-gray-100 border-none rounded focus:ring-gray-300 dark:focus:ring-gray-700 sm:text-sm disabled:opacity-50 dark:bg-cool-gray-900 dark:text-gray-200 focus:bg-white"
            type="text"
            :placeholder="__('Type a command and press Enter to run...')"
          >
          <span
            v-if="commandError"
            class="text-xs text-red-400"
          >{{ commandError }}</span>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import {Terminal} from 'xterm';
import {FitAddon} from 'xterm-addon-fit';
import {WebLinksAddon} from 'xterm-addon-web-links';
import OverviewCard from '@/Components/Dashboard/OverviewCard.vue';
import ServerSubMenu from '@/Pages/Admin/Server/ServerSubMenu.vue';
import {debounce} from 'lodash';
import millify from 'millify';
import {USE_WEBSOCKETS} from '@/constants';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    components: {
        AdminLayout,
        ServerSubMenu,
        OverviewCard,
    },
    props: {
        server: Object,
        serverAggrData: Object,
    },
    setup() {
        const {can} = useAuthorizable();
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {can, formatTimeAgoToNow, formatToDayDateString};
    },

    data() {
        return {
            // consolelogs: this.serverConsoleLogs.slice().reverse(),
            millify: millify,
            refitTerminal: null,
            onlinePlayers: this.__('Loading') + '...',
            maxPlayersAllowed: 0,
            serverPingInterval: null,
            sendingCommand: false,
            commandText: '',
            commandError: null,
            termx: null,
            loadingConsoleLogs: true,
            consoleLogsQueryInterval: null,
            consolelogs: [],
        };
    },

    mounted() {
        const theme = {
            background: '#111827',
            cursor: 'transparent',
            black: 'rgb(19, 26, 32)',
            red: '#E54B4B',
            green: '#9ECE58',
            yellow: '#ddcf31',
            blue: '#396FE2',
            magenta: '#BB80B3',
            cyan: '#2DDAFD',
            white: '#d0d0d0',
            brightBlack: 'rgba(255, 255, 255, 0.2)',
            brightRed: '#FF5370',
            brightGreen: '#31ab14',
            brightYellow: '#FFCB6B',
            brightBlue: '#82AAFF',
            brightMagenta: '#C792EA',
            brightCyan: '#89DDFF',
            brightWhite: '#ffffff',
            selection: '#FAF089',
        };

        const terminalProps = {
            disableStdin: true,
            cursorStyle: 'underline',
            allowTransparency: true,
            fontSize: 12,
            fontFamily: 'Menlo, Monaco, Consolas, monospace',
            rows: 30,
            theme: theme
        };

        const term = new Terminal(terminalProps);
        const fitAddon = new FitAddon();
        term.loadAddon(fitAddon);
        term.loadAddon(new WebLinksAddon());

        term.open(document.getElementById('terminal'));

        fitAddon.fit();
        this.refitTerminal = debounce(() => {
            fitAddon.fit();
        }, 100);
        addEventListener('resize', this.refitTerminal);


        // Fetch consolelogs
        axios.get(route('admin.server.consolelogs.index', this.server.id)).then(response => {
            this.consolelogs = response.data?.reverse();
            for (let line of this.consolelogs) {
                term.writeln(line.data);
            }
        }).finally(() => {
            this.loadingConsoleLogs = false;
        });

        if (USE_WEBSOCKETS) {
            Echo.private('consolelogs.' + this.server.id).listen('ServerConsolelogCreated', data => {
                this.consolelogs.unshift(data.data);
                term.writeln(data.data.data);
            });
        } else {
            this.consoleLogsQueryInterval = setInterval(() => this.pollServerForConsoleLogs(term), 5000);
        }

        // Load Server Online Status
        this.pingServer();
        this.serverPingInterval = setInterval(() => this.pingServer(), 10000);

        // @WorkAround for 'Could not dispose an addon that has not been loaded'
        // Not able to assign termx directly above because of Proxy, it give above error
        this.termx = term;
    },

    unmounted() {
        this.termx.dispose();
        if (USE_WEBSOCKETS) {
            Echo.leave(`consolelogs.${this.server.id}`);
        } else {
            clearInterval(this.consoleLogsQueryInterval);
        }
        removeEventListener('resize', this.refitTerminal);
        clearInterval(this.serverPingInterval);
    },

    methods: {
        pingServer() {
            axios.get(route('server.ping.get', this.server.id))
                .then(data => {
                    this.$nextTick(() => {
                        if (data.data.players) {
                            this.onlinePlayers = data.data.players.online.toString();
                            this.maxPlayersAllowed = data.data.players.max.toString();
                        }
                    });
                })
                .catch(() => {
                    this.$nextTick(() => {
                        this.onlinePlayers = 'offline';
                    });
                });
        },

        sendCommandToServer() {
            this.sendingCommand = true;

            let formJson = {
                type: 'custom',
                params: this.commandText,
                context: 'server'
            };

            axios.post(route('admin.server.command', this.server.id), formJson).then(() => {
                this.commandText = '';
            }).catch(e => {
                this.commandError = e.message;
            }).finally(() => {
                this.sendingCommand = false;
            });
        },

        pollServerForConsoleLogs(term) {
            if (USE_WEBSOCKETS) return;

            const afterId = this.consolelogs.length ? this.consolelogs[this.consolelogs.length - 1]?.id : 0;
            axios.get(route('admin.server.consolelogs.index', {
                server: this.server.id,
                after: afterId
            })).then(response => {
                if (response.data && response.data.length) {
                    const dataArr = response.data.reverse();
                    for (let line of dataArr) {
                        term.writeln(line.data);
                    }
                    this.consolelogs = this.consolelogs.concat(dataArr);
                }
            });
        }
    },
};
</script>

<style src="xterm/css/xterm.css"></style>
