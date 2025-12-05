<template>
  <div
    :class="cn('space-y-2', divClass)"
  >
    <!-- Label -->
    <Label
      v-if="label"
      :for="id"
      :class="cn(
        'text-sm font-medium',
        error ? 'text-destructive' : 'text-foreground'
      )"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ml-1">*</span>
    </Label>

    <!-- Input -->
    <Input
      :id="id"
      ref="inputRef"
      v-model="modelValue"
      :type="type"
      :name="name"
      :class="inputClasses"
      :autocomplete="autocomplete"
      :autofocus="autofocus"
      :required="required"
      :disabled="disabled"
      :placeholder="placeholder"
    />

    <!-- Help and Error Messages -->
    <div
      v-if="help || error"
      class="flex gap-1"
      :class="cn(
        help && error ? 'justify-between' : error ? 'justify-end' : 'justify-start',
        helpErrorFlex
      )"
    >
      <p
        v-if="help"
        class="text-xs text-muted-foreground"
      >
        {{ help }}
      </p>
      <p
        v-if="error"
        class="text-xs text-destructive"
      >
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useVModel } from '@vueuse/core'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: {
    type: [Number, String, Array, Object, Boolean, Date],
    default: ''
  },
  name: String,
  help: String,
  label: String,
  type: {
    type: String,
    default: 'text'
  },
  id: String,
  error: String,
  autocomplete: {
    type: String,
    default: 'off'
  },
  autofocus: {
    type: [String, Boolean],
    default: false
  },
  required: {
    type: [String, Boolean],
    default: false
  },
  disabled: {
    type: [String, Boolean],
    default: false
  },
  helpErrorFlex: {
    type: String,
    default: 'flex-col'
  },
  inputClass: {
    type: String,
    default: ''
  },
  divClass: {
    type: String,
    default: ''
  },
  placeholder: String,
})

const emits = defineEmits(['update:modelValue'])

const inputRef = ref(null)

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.modelValue,
})

const inputClasses = computed(() => {
  return cn(
    // Base input styles with proper height
    'transition-colors',
    // Error state styling
    props.error && 'border-destructive focus-visible:ring-destructive',
    // Custom classes
    props.inputClass
  )
})

const focus = () => {
  if (inputRef.value) {
    inputRef.value.focus()
  }
}

defineExpose({
  focus
})
</script>
