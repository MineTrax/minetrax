<script setup lang="ts">
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

const props = defineProps<{
    htmlData: string | null
}>()
</script>

<template>
    <Card v-if="htmlData">
        <CardContent class="px-4 py-3">
            <div class="prose dark:prose-invert max-w-none text-center" v-html="htmlData" />
        </CardContent>
    </Card>
</template>
