<template>
  <app-layout>
    <app-head
      :title="__('Posts')"
    />

    <div class="px-2 py-4 md:py-12 md:px-8 max-w-7xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
        <div class="flex-grow">
          <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block overflow-hidden min-w-full md:px-6 lg:px-8">
              <div class="">
                <PostListBox />
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col space-y-4 flex-none md:w-1/4 h-screen md:sticky top-5">
          <ServerStatusBox />
          <ShoutBox />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import PostListBox from '@/Shared/PostListBox.vue';
</script>
