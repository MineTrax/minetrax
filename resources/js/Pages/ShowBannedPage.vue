<template>
  <app-layout>
    <app-head title="Oops! You are Banned" />

    <div class="py-12 px-10 max-w-4xl mx-auto">
      <div class="flex space-x-4">
        <div class="flex-grow">
          <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8 text-center">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
              <div class="shadow space-y-10 bg-white overflow-hidden border-b border-gray-200 rounded p-4">
                <icon
                  name="ban"
                  class="h-40 w-40 text-red-600 inline-flex"
                />
                <h3 class="text-3xl font-extrabold text-red-900">
                  Oops! You are Banned.
                </h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import {formatDistanceToNowStrict} from 'date-fns';
import Icon from '@/Components/Icon';
import ShoutBox from '@/Shared/ShoutBox';
import NewsBox from '@/Shared/NewsBox';
import InfiniteScroll from '@/Components/InfiniteScroll';
import ServerStatusBox from '@/Shared/ServerStatusBox';

export default {

    components: {
        Icon,
        AppLayout,
    },
    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict
        };
    },
};
</script>
