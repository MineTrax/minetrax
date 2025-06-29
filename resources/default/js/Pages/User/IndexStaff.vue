<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import { Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/Components/ui/card';
import UserDisplayname from '@/Components/UserDisplayname.vue';

defineProps({
    staffs: {
        type: Array,
        required: true,
    },
});

const getContrastingTextColor = (hexcolor) => {
    if (!hexcolor) return '#FFFFFF';
    hexcolor = hexcolor.replace('#', '');
    if (hexcolor.length === 3) {
        hexcolor = hexcolor.split('').map(function (hex) {
            return hex + hex;
        }).join('');
    }
    const r = parseInt(hexcolor.substr(0, 2), 16);
    const g = parseInt(hexcolor.substr(2, 2), 16);
    const b = parseInt(hexcolor.substr(4, 2), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? '#000000' : '#FFFFFF';
};
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Our Staff Team')" />

    <div class="py-12">
      <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="text-center">
          <h2 class="text-3xl font-extrabold tracking-tight sm:text-5xl">
            {{ __('Our Staff Team') }}
          </h2>
          <p class="mt-3 max-w-2xl mx-auto sm:text-lg sm:mt-4 text-muted-foreground">
            {{ __('Meet the dedicated team members who keep the server running smoothly and create amazing experiences for our community.') }}
          </p>
        </div>

        <div
          v-if="staffs.length > 0"
          class="grid gap-8 mt-12 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3"
        >
          <Card
            v-for="staff in staffs"
            :key="staff.id"
            class="overflow-hidden transition-all transform hover:scale-105"
          >
            <Link
              :href="route('user.public.get', staff.username)"
              class="block"
            >
              <div
                class="h-32 bg-center bg-no-repeat bg-cover"
                :style="staff.cover_image_url ? `background-image: url('${staff.cover_image_url}')` : ''"
              />
              <CardContent class="relative px-6 pb-6 -mt-16">
                <div class="flex justify-center">
                  <img
                    class="w-32 h-32 border-4 border-white rounded-full"
                    :src="staff.profile_photo_url"
                    :alt="staff.name"
                  >
                </div>
                <div class="mt-4 text-center">
                  <h3 class="flex items-center justify-center">
                    <UserDisplayname
                      :user="staff"
                      text-class="text-xl font-semibold"
                      icon-class="w-5 h-5 ml-2"
                    />
                  </h3>
                  <div class="mt-1">
                    <span
                      v-if="staff.roles.length > 0"
                      class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium"
                      :class="!staff.roles[0].color ? 'bg-background text-card-foreground' : ''"
                      :style="staff.roles[0].color ? {
                        'background-color': staff.roles[0].color,
                        'color': getContrastingTextColor(staff.roles[0].color)
                      } : {}"
                    >
                      {{ staff.roles[0].display_name }}
                    </span>
                  </div>
                </div>
              </CardContent>
            </Link>
          </Card>
        </div>

        <div
          v-else
          class="flex justify-center mt-12 italic text-gray-500 dark:text-gray-400"
        >
          {{ __('No Staff Yet!') }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>
