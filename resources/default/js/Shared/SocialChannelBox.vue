<template>
  <div
    v-if="enabled && show"
    class="p-3 bg-white dark:bg-cool-gray-800 rounded shadow space-y-2"
  >
    <h3
      v-if="showTitle"
      class="text-gray-800 dark:text-gray-200 font-extrabold"
    >
      {{ __("Socials") }}
    </h3>

    <div class="flex items-center justify-center space-x-6 flex-wrap">
      <a
        v-if="youtube"
        v-tippy
        :title="__('Youtube')"
        :href="youtube"
        target="_blank"
        class="inline-block p-1 hover:bg-red-600 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <svg
          class="fill-current h-8 w-8 p-1"
          height="512"
          viewBox="0 0 24 24"
          width="512"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M9.939 7.856a.75.75 0 00-1.134.644v7c0 .585.638.939 1.134.645l5.869-3.495a.751.751 0 00.001-1.289zm.366 6.325v-4.36l3.655 2.183z"
          />
          <path
            d="M19.904 3.271c-4.653-.691-11.153-.691-15.808 0C2.234 3.547.767 5.009.447 6.907c-.596 3.523-.596 6.664 0 10.186.32 1.899 1.787 3.36 3.649 3.636 2.332.346 5.124.519 7.915.519 2.786 0 5.571-.172 7.894-.518 1.86-.276 3.326-1.737 3.648-3.636.596-3.523.596-6.665 0-10.188-.32-1.897-1.787-3.359-3.649-3.635zm2.17 13.573c-.213 1.256-1.173 2.222-2.39 2.402-4.518.671-10.838.671-15.368-.001-1.218-.181-2.179-1.146-2.391-2.402-.574-3.394-.574-6.291 0-9.687.213-1.256 1.173-2.22 2.392-2.402 2.262-.335 4.973-.503 7.682-.503 2.711 0 5.422.168 7.684.503 1.218.181 2.179 1.146 2.391 2.402.574 3.396.574 6.293 0 9.688z"
          />
        </svg>
      </a>

      <a
        v-if="twitter"
        v-tippy
        :title="__('Twitter')"
        :href="twitter"
        target="_blank"
        class="inline-block p-1 hover:bg-light-blue-600 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <icon
          name="twitter"
          class="h-8 w-8 p-1 fill-current"
        />
      </a>

      <a
        v-if="twitch"
        v-tippy
        :title="__('Twitch')"
        :href="twitch"
        target="_blank"
        class="inline-block p-1 hover:bg-purple-600 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <icon
          name="twitch"
          class="h-8 w-8 p-1 fill-current"
        />
      </a>

      <a
        v-if="facebook"
        v-tippy
        :title="__('Facebook')"
        :href="facebook"
        target="_blank"
        class="inline-block p-1 hover:bg-blue-700 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <svg
          class="w-8 h-8 p-1 fill-current"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 155.139 155.139"
        >
          <path
            d="M89.584 155.139V84.378h23.742l3.562-27.585H89.584V39.184c0-7.984 2.208-13.425 13.67-13.425l14.595-.006V1.08C115.325.752 106.661 0 96.577 0 75.52 0 61.104 12.853 61.104 36.452v20.341H37.29v27.585h23.814v70.761h28.48z"
          />
        </svg>
      </a>

      <a
        v-if="linkedin"
        v-tippy
        :title="__('LinkedIn')"
        :href="linkedin"
        target="_blank"
        class="inline-block p-1 hover:bg-[#0a66c2] text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <icon
          name="linkedin"
          class="h-8 w-8 p-1 fill-current"
        />
      </a>

      <a
        v-if="tiktok"
        v-tippy
        :title="__('TikTok')"
        :href="tiktok"
        target="_blank"
        class="inline-block p-1 hover:bg-black text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <icon
          name="tiktok"
          class="h-8 w-8 p-1 fill-current"
        />
      </a>

      <a
        v-if="steam"
        v-tippy
        :title="__('Steam')"
        :href="steam"
        target="_blank"
        class="inline-block p-1 hover:bg-gray-800 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <svg
          class="h-8 w-8 p-1 fill-current"
          height="512"
          viewBox="0 0 24 24"
          width="512"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M17.903 1.75c-3.349 0-6.081 2.696-6.107 6.02l-3.803 5.427c-1.061-.096-1.976.123-2.785.614L0 11.714v5.727l3.08 1.233a4.567 4.567 0 004.447 3.576c2.358 0 4.312-1.803 4.527-4.099l5.855-4.259c3.359 0 6.091-2.711 6.091-6.073l-.001-.001c0-3.346-2.732-6.068-6.096-6.068zM4.569 19.271c.546.208 1.098.437 1.645.651a2.652 2.652 0 003.466-1.468 2.638 2.638 0 00-1.483-3.442l-1.393-.56c2.074-.46 4.071 1.094 4.072 3.255-.001 1.852-4.678 4.608-6.307 1.564zm13.34-7.385c-2.261 0-4.098-1.815-4.098-4.067 0-2.242 1.832-4.067 4.098-4.067 2.245 0 4.082 1.825 4.082 4.067a4.07 4.07 0 01-4.082 4.067z"
          />
          <path
            d="M17.92 4.556c-1.805 0-3.267 1.457-3.267 3.255s1.457 3.255 3.267 3.255c1.794 0 3.267-1.452 3.267-3.255s-1.473-3.255-3.267-3.255z"
          />
        </svg>
      </a>

      <a
        v-if="website"
        v-tippy
        :title="__('Website')"
        :href="website"
        target="_blank"
        class="inline-block p-1 hover:bg-green-600 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >
        <svg
          class="h-8 w-8 p-1 fill-current"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 26 26"
        >
          <g>
            <path
              d="M8.083 11.222L6.419 15a.276.276 0 01-.257.154H5.31c-.113 0-.216-.061-.256-.154l-.79-1.803a15.454 15.454 0 01-.213-.517c-.073.186-.148.359-.223.525l-.833 1.8a.277.277 0 01-.255.149h-.852a.276.276 0 01-.259-.158L.072 11.217c-.03-.074-.018-.156.034-.221s.135-.103.225-.103h.959c.121 0 .227.069.263.169l.684 1.92c.057.162.11.315.159.461.06-.146.125-.3.194-.463l.842-1.932a.277.277 0 01.256-.155h.792c.115 0 .217.063.258.157l.798 1.89c.072.17.136.329.195.479.049-.142.106-.296.171-.465l.737-1.898a.275.275 0 01.26-.164h.926a.29.29 0 01.227.104.22.22 0 01.031.226zm8.922 0L15.341 15a.276.276 0 01-.256.154h-.854a.276.276 0 01-.256-.154l-.789-1.803a13.647 13.647 0 01-.214-.517c-.073.186-.148.359-.223.525l-.833 1.8a.276.276 0 01-.255.149h-.853a.277.277 0 01-.259-.158l-1.557-3.779a.218.218 0 01.034-.221.291.291 0 01.225-.103h.959c.121 0 .227.069.263.169l.683 1.92.161.461c.059-.146.123-.3.192-.463l.843-1.932a.275.275 0 01.256-.155h.792c.115 0 .218.063.258.157l.799 1.89c.071.17.135.329.193.479.051-.142.106-.296.172-.465l.737-1.898c.038-.098.144-.164.261-.164h.926c.092 0 .177.039.227.104a.218.218 0 01.032.226zm8.921 0L24.263 15a.277.277 0 01-.257.154h-.853c-.113 0-.216-.061-.256-.154l-.789-1.803a13.647 13.647 0 01-.214-.517c-.073.186-.149.359-.224.525l-.833 1.8a.277.277 0 01-.255.149h-.852a.277.277 0 01-.26-.158l-1.557-3.779c-.029-.074-.019-.156.033-.221s.136-.103.226-.103h.96c.121 0 .227.069.262.169l.684 1.92.16.461c.059-.146.123-.3.192-.463l.843-1.932a.278.278 0 01.257-.155h.791c.115 0 .218.063.258.157l.798 1.89c.072.17.137.329.196.479.049-.142.106-.296.171-.465l.738-1.898c.037-.098.142-.164.26-.164h.926c.092 0 .176.039.227.104a.224.224 0 01.031.226zM2.71 9c.283-.718.637-1.401 1.057-2.037.062.012.12.037.185.037h1.88a18.675 18.675 0 00-.49 2h2.055c.155-.699.335-1.376.562-2h9.986c.227.624.407 1.301.562 2h2.055a18.675 18.675 0 00-.49-2h1.88c.065 0 .123-.025.186-.037.418.636.773 1.319 1.056 2.037h2.121c-1.691-5.216-6.591-9-12.363-9S2.28 3.784.588 9H2.71zm17.768-4H19.29a12.54 12.54 0 00-.851-1.519c.74.428 1.422.938 2.039 1.519zm-7.526-3c1.551 0 2.983 1.154 4.062 3H8.89c1.079-1.846 2.511-3 4.062-3zM7.463 3.481c-.308.469-.592.976-.85 1.519H5.426a11.074 11.074 0 012.037-1.519zM23.194 17c-.283.719-.638 1.4-1.057 2.037-.062-.012-.12-.037-.185-.037h-1.881c.199-.634.355-1.309.49-2h-2.055a15.848 15.848 0 01-.562 2H7.959a16.018 16.018 0 01-.562-2H5.343c.135.691.291 1.366.49 2H3.952c-.065 0-.123.025-.185.037A11.107 11.107 0 012.71 17H.588c1.692 5.216 6.592 9 12.364 9s10.672-3.784 12.363-9h-2.121zM5.426 21h1.188c.258.543.542 1.051.85 1.519A11.048 11.048 0 015.426 21zm7.526 3c-1.551 0-2.983-1.154-4.062-3h8.123c-1.078 1.846-2.51 3-4.061 3zm5.488-1.481c.309-.468.593-.976.851-1.519h1.188c-.618.581-1.3 1.091-2.039 1.519z"
            />
          </g>
        </svg>
      </a>

      <a
        v-if="discord"
        v-tippy
        :title="__('Discord')"
        :href="discord"
        target="_blank"
        class="inline-block p-1 hover:bg-indigo-600 text-gray-700 rounded hover:text-white transition duration-100 ease-in dark:text-gray-300 dark:hover:text-white"
      >

        <icon
          name="discord"
          class="h-8 w-8 p-1 fill-current"
        />
      </a>
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';

export default {
    components: {Icon},
    props: {
        enabled: Boolean,
        showTitle: Boolean,
        youtube: [String, null],
        facebook: [String, null],
        twitter: [String, null],
        twitch: [String, null],
        website: [String, null],
        steam: [String, null],
        discord: [String, null],
        tiktok: [String, null],
        linkedin: [String, null],
    },

    computed: {
        show() {
            return !!this.youtube || !!this.facebook || !!this.twitter || !!this.twitter || !!this.website || !!this.steam || !!this.discord || !!this.tiktok || !!this.linkedin;
        }
    }
};
</script>
