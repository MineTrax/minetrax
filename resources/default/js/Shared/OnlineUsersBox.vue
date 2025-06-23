<template>
  <div v-if="enabled && users">
    <div class="p-3 bg-white dark:bg-surface-800 rounded shadow space-y-2">
      <h3 class="font-extrabold text-foreground dark:text-foreground">
        {{ __("Online Users") }}
      </h3>

      <div
        v-if="users && users.length > 0"
        class="flex justify-center flex-wrap"
      >
        <inertia-link
          v-for="user in onlineMembers"
          :key="user.id"
          as="a"
          :href="route('user.public.get', user.user.username)"
          class="cursor-pointer flex mr-2 items-center text-primary hover:underline"
          :style="[user.user.roles[0].color ? {color: user.user.roles[0].color} : null]"
        >
          <img
            class="h-5 w-5 rounded-full mr-0.5"
            :src="user.user.profile_photo_url"
            :alt="user.user.username"
          >
          <span>@{{ user.user.username }}</span>
        </inertia-link>
      </div>

      <div
        v-else
        class="text-foreground dark:text-foreground italic font-light flex justify-center"
      >
        {{ __("No member online.") }}
      </div>

      <div class="flex justify-center text-xs text-foreground dark:text-foreground font-semibold">
        {{ __("Total") }}: {{ guestCount + onlineMembers.length }} ({{ __("members") }}: {{ onlineMembers.length }}, {{ __("guests") }}: {{ guestCount }})
      </div>
    </div>
  </div>
</template>

<script>

export default {
    props: {
        users: Array,
        enabled: Boolean
    },
    computed: {
        guestCount() {
            let total = 0;
            this.users.forEach(user => {
                if (!user.user) {
                    total++;
                }
            });
            return total;
        },

        onlineMembers() {
            return this.users.filter(user => user.user);
        }
    }
};
</script>
