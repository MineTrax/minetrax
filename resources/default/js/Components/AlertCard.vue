<template>
  <Card :class="cn('mb-4', cardVariants({ variant, class: props.class }))">
    <CardContent class="p-4">
      <div class="flex">
        <div class="py-1">
          <slot name="icon">
            <svg
              :class="cn('fill-current h-6 w-6 mr-4', colorVariants({ variant }))"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"
              />
            </svg>
          </slot>
        </div>
        <div :class="cn('flex-1', titleClass)">
          <div :class="cn('font-bold', colorVariants({ variant }))">
            <slot />
          </div>
          <div class="text-sm mt-1">
            <slot name="body" />
          </div>
        </div>
      </div>
      <button
        v-if="closeButton"
        class="absolute rounded-full bg-card border border-border hover:bg-accent p-1 -top-2 -right-2 shadow-sm"
        @click="$emit('close')"
      >
        <XMarkIcon class="h-4 w-4" />
      </button>
    </CardContent>
  </Card>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';
import { Card, CardContent } from '@/Components/ui/card';

const cardVariants = cva(
  'relative border-l-4 shadow-sm',
  {
    variants: {
      variant: {
        default: 'border-l-primary bg-card text-card-foreground',
        destructive: 'border-l-destructive bg-card text-card-foreground',
        warning: 'border-l-warning-500 bg-card text-card-foreground',
        success: 'border-l-success-500 bg-card text-card-foreground',
        info: 'border-l-info-500 bg-card text-card-foreground',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

const colorVariants = cva(
  '',
  {
    variants: {
      variant: {
        default: 'text-primary',
        destructive: 'text-destructive',
        warning: 'text-warning-500',
        success: 'text-success-500',
        info: 'text-info-500',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'destructive', 'warning', 'success', 'info'].includes(value)
  },
  class: {
    type: String,
    default: '',
  },
  titleClass: {
    type: String,
    default: '',
  },
  closeButton: {
    type: Boolean,
    default: false,
  },
  // Legacy props for backward compatibility
  borderColor: {
    type: String,
    default: '',
  },
  textColor: {
    type: String,
    default: '',
  },
});

defineEmits(['close']);
</script>
