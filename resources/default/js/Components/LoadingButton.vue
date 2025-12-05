<template>
  <Button
    :disabled="loading"
    :variant="variant"
    :size="size"
  >
  <Loader2Icon v-if="loading" class="w-4 h-4 animate-spin" />
    <slot />
  </Button>
</template>

<script>
import { Button } from '@/Components/ui/button';
import { Loader2Icon } from 'lucide-vue-next';

export default {
    components: { Button, Loader2Icon },
    props: {
        loading: Boolean,
        variant: {
            type: String,
            default: 'default',
        },
        size: {
            type: String,
            default: 'default',
        },
    },
};
</script>
