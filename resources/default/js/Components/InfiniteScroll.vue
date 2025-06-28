<template>
  <slot />

  <div ref="sentinel"></div>

  <slot
    v-if="loading"
    name="loading"
  >
    <div class="p-5 text-center text-foreground dark:text-foreground text-sm">
      {{ loadingText }}
    </div>
  </slot>
</template>

<script setup>
import { ref } from 'vue'
import { useInfiniteScroll } from '@vueuse/core'

const props = defineProps({
  loadMore: {
    type: Function,
    required: true,
  },
  loadingText: {
    type: String,
    default: 'Loading more...'
  },
  threshold: {
    type: Number,
    default: 200
  }
})

const loading = ref(false)
const sentinel = ref(null)

useInfiniteScroll(
  sentinel,
  async () => {
    if (loading.value) return
    loading.value = true
    await props.loadMore()
    loading.value = false
  },
  {
    distance: props.threshold,
    debounce: 100,
  }
)
</script>
