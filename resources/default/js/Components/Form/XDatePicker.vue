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
:deep(.mx-datepicker) {
    width: 100% !important;
}

/* Higher z-index for inline popups (inside dialogs) */
:deep(.mx-datepicker-popup) {
    z-index: 100 !important;
}
</style>

<!-- Global (unscoped) styles so they apply to body-appended popups too -->
<style>
.mx-datepicker-main {
    color: var(--popover-foreground);
    background-color: var(--popover);
    border-color: var(--border);
}

/* Header nav buttons */
.mx-btn {
    color: var(--muted-foreground);
    border-color: var(--border);
}

.mx-btn:hover {
    color: var(--primary);
    border-color: var(--primary);
}

.mx-btn-text {
    color: var(--popover-foreground);
}

.mx-btn-text:hover {
    color: var(--primary);
}

/* Calendar header label (month/year) */
.mx-calendar-header-label {
    color: var(--popover-foreground);
}

/* Table header (day names) */
.mx-table th {
    color: var(--muted-foreground);
}

/* Calendar cells */
.mx-calendar-content .cell {
    color: var(--popover-foreground);
    border-radius: 4px;
}

.mx-calendar-content .cell:hover {
    color: var(--accent-foreground);
    background-color: var(--accent);
}

.mx-calendar-content .cell.active {
    color: var(--primary-foreground);
    background-color: var(--primary);
}

.mx-calendar-content .cell.in-range,
.mx-calendar-content .cell.hover-in-range {
    color: var(--popover-foreground);
    background-color: var(--muted);
}

.mx-calendar-content .cell.disabled {
    color: var(--muted-foreground);
    background-color: var(--muted);
    opacity: 0.5;
}

.mx-table-date .today {
    color: var(--primary);
}

.mx-table-date .cell.not-current-month {
    color: var(--muted-foreground);
}

/* Borders between panels / sections */
.mx-calendar + .mx-calendar,
.mx-datepicker-header,
.mx-datepicker-footer,
.mx-time + .mx-time,
.mx-time-header {
    border-color: var(--border);
}

.mx-datepicker-sidebar + .mx-datepicker-content {
    border-color: var(--border);
}

/* Time picker */
.mx-time {
    background-color: var(--popover);
}

.mx-time-column {
    border-color: var(--border);
}

.mx-time-column .mx-time-item:hover,
.mx-time-option:hover {
    color: var(--accent-foreground);
    background-color: var(--accent);
}

.mx-time-column .mx-time-item.active,
.mx-time-option.active {
    color: var(--primary);
}

/* Icon colors */
.mx-icon-calendar,
.mx-icon-clear {
    color: var(--muted-foreground);
}

.mx-icon-clear:hover {
    color: var(--popover-foreground);
}
</style>
