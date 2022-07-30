<template>
  <div class="min-h-screen flex flex-col md:justify-center items-center pt-7 md:pt-0">
    <div>
      <slot name="logo" />
    </div>

    <div class="m-3 md:m-0 md:mt-6 md:w-full md:max-w-md mt-6 px-6 py-4 bg-white dark:bg-cool-gray-800 shadow-md overflow-hidden rounded md:rounded-lg">
      <slot />
    </div>
  </div>
</template>
