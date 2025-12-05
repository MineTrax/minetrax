<template>
  <div :class="cn('space-y-2', divClass)">
    <!-- Label -->
    <Label
      v-if="label"
      :for="id"
      :class="cn('text-sm font-medium', error ? 'text-destructive' : 'text-foreground')"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ml-1">*</span>
    </Label>

    <!-- Select -->
    <Select
      v-model="internalValue"
      :name="name"
      :disabled="disabled"
      :required="required"
      :id="id"
      :autofocus="autofocus"
    >
      <SelectTrigger :class="triggerClasses" ref="triggerRef">
        <SelectValue :placeholder="placeholder" />
      </SelectTrigger>

      <SelectContent>
        <!-- Null / Clear option -->
        <template v-if="showNullItem">
          <SelectItem :value="null">
            {{ nullItemLabel }}
          </SelectItem>
        </template>
        <SelectItem
          v-for="(lbl, value) in computedList"
          :key="value"
          :value="value"
        >
          {{ lbl }}
        </SelectItem>
      </SelectContent>
    </Select>

    <!-- Help and Error Messages -->
    <div
      v-if="help || error"
      class="flex gap-1"
      :class="cn(help && error ? 'justify-between' : error ? 'justify-end' : 'justify-start', helpErrorFlex)"
    >
      <p v-if="help" class="text-xs text-muted-foreground">{{ help }}</p>
      <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick } from 'vue'
import { useVModel } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { Label } from '@/Components/ui/label'
import {
  Select,
  SelectTrigger,
  SelectContent,
  SelectItem,
  SelectValue,
} from '@/Components/ui/select'

// Props definition
const props = defineProps({
  selectList: [Object, Array],
  modelValue: [Number, String, Array, Object, Boolean, Date],
  name: String,
  placeholder: String,
  help: String,
  label: String,
  id: String,
  error: String,
  autofocus: {
    type: [String, Boolean],
    default: false,
  },
  required: {
    type: [String, Boolean],
    default: false,
  },
  disabled: {
    type: [String, Boolean],
    default: false,
  },
  helpErrorFlex: {
    type: String,
    default: 'flex-col',
  },
  disableNull: {
    type: Boolean,
    default: false,
  },
  selectClass: {
    type: String,
    default: '',
  },
  divClass: {
    type: String,
    default: '',
  },
})

const emits = defineEmits(['update:modelValue'])

// Two-way binding for modelValue
const internalValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.modelValue,
})

// Ref for focusing
const triggerRef = ref(null)

// Compute list for Select items
const computedList = computed(() => {
  if (!Array.isArray(props.selectList)) {
    return props.selectList
  }
  return props.selectList.reduce((acc, value) => {
    return { [value]: value, ...acc }
  }, {})
})

// Whether to include a null/clear item
const showNullItem = computed(() => {
  // Do not show if explicitly disabled or field is required
  if (props.disableNull || props.required) return false
  return true
})

// Label for null item – placeholder if provided else "—"
const nullItemLabel = computed(() => {
  return props.placeholder ?? '—'
})

// Classes for SelectTrigger
const triggerClasses = computed(() => {
  return cn(
    // Allow consumer to override/extend styles
    props.selectClass,
    // Error state styling
    props.error && 'border-destructive focus-visible:ring-destructive'
  )
})

// Handle autofocus once mounted
if (props.autofocus) {
  nextTick(() => {
    if (triggerRef.value?.$el) {
      triggerRef.value.$el.focus()
    }
  })
}

// Expose focus method similar to previous API
const focus = () => {
  if (triggerRef.value?.$el) {
    triggerRef.value.$el.focus()
  }
}

defineExpose({ focus })
</script>
