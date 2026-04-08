<template>
  <div :class="cn('space-y-2', divClass)">
    <!-- Label -->
    <Label
      v-if="label"
      :for="id"
      :class="cn('text-sm font-medium', error ? 'text-destructive' : 'text-foreground')"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-destructive ml-1"
      >*</span>
    </Label>

    <!-- Date Picker -->
    <div class="relative">
      <DatePicker
        :id="id"
        ref="datePickerRef"
        v-model:value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :name="name"
        :type="type"
        :format="format"
        :value-type="valueType"
        :append-to-body="appendToBody"
        class="w-full"
        :input-class="inputClasses"
      />
    </div>

    <!-- Help and Error Messages -->
    <div
      v-if="help || error"
      class="flex gap-1"
      :class="cn(help && error ? 'justify-between' : error ? 'justify-end' : 'justify-start', helpErrorFlex)"
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
import { computed, ref } from "vue";
import { useVModel } from "@vueuse/core";
import DatePicker from "vue-datepicker-next";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";

const props = defineProps({
    modelValue: {
        type: [String, Date],
        default: null,
    },
    name: String,
    help: String,
    label: String,
    id: String,
    error: String,
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
        default: "flex-col",
    },
    datePickerClass: {
        type: String,
        default: "",
    },
    divClass: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Select date...",
    },
    type: {
        type: String,
        default: "date",
    },
    format: {
        type: String,
        default: "YYYY-MM-DD",
    },
    valueType: {
        type: String,
        default: "format",
    },
    appendToBody: {
        type: Boolean,
        default: true,
    },
});

const emits = defineEmits(["update:modelValue"]);

const datePickerRef = ref(null);

const modelValue = useVModel(props, "modelValue", emits, {
    passive: true,
    defaultValue: props.modelValue,
});

const inputClasses = computed(() => {
    return cn(
        // Base input styles matching shadcn/ui Input component
        "flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50",
        // Error state styling
        props.error && "border-destructive focus-visible:ring-destructive",
        // Custom classes
        props.datePickerClass
    );
});

const focus = () => {
    if (datePickerRef.value) {
        const input = datePickerRef.value.$el?.querySelector("input");
        if (input) {
            input.focus();
        }
    }
};

defineExpose({
    focus,
});
</script>

<style scoped>
/* Ensure the date picker container takes full width */
:deep(.mx-datepicker) {
    width: 100% !important;
}

/* Ensure the popup is above dialog overlays when rendered inline */
:deep(.mx-datepicker-popup) {
    z-index: 100 !important;
}
</style>
