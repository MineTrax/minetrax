<template>
    <div :class="cn('space-y-2', divClass)">
        <!-- Label -->
        <Label v-if="label" :for="id" :class="cn('text-sm font-medium', error ? 'text-destructive' : 'text-foreground')">
            {{ label }}
            <span v-if="required" class="text-destructive ml-1">*</span>
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
                value-type="format"
                class="w-full"
                :input-class="inputClasses"
            />
        </div>

        <!-- Help and Error Messages -->
        <div v-if="help || error" class="flex gap-1" :class="cn(help && error ? 'justify-between' : error ? 'justify-end' : 'justify-start', helpErrorFlex)">
            <p v-if="help" class="text-xs text-muted-foreground">
                {{ help }}
            </p>
            <p v-if="error" class="text-xs text-destructive">
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
        "flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50",
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

/* Style the calendar popup to match the current theme */
:deep(.mx-datepicker-popup) {
    border: 1px solid hsl(var(--border)) !important;
    border-radius: 0.375rem !important;
    background: hsl(var(--background)) !important;
    color: hsl(var(--foreground)) !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

/* Style the calendar header */
:deep(.mx-calendar-header) {
    background: hsl(var(--muted)) !important;
    color: hsl(var(--foreground)) !important;
    border-bottom: 1px solid hsl(var(--border)) !important;
}

/* Style the calendar navigation buttons */
:deep(.mx-btn) {
    background: transparent !important;
    color: hsl(var(--foreground)) !important;
    border: 1px solid hsl(var(--border)) !important;
    border-radius: 0.375rem !important;
    transition: all 0.2s !important;
}

:deep(.mx-btn:hover) {
    background: hsl(var(--accent)) !important;
    color: hsl(var(--accent-foreground)) !important;
}

/* Style the calendar cells */
:deep(.mx-calendar-content .cell) {
    color: hsl(var(--foreground)) !important;
    border-radius: 0.375rem !important;
    transition: all 0.2s !important;
}

:deep(.mx-calendar-content .cell:hover) {
    background: hsl(var(--accent)) !important;
    color: hsl(var(--accent-foreground)) !important;
}

:deep(.mx-calendar-content .cell.active) {
    background: hsl(var(--primary)) !important;
    color: hsl(var(--primary-foreground)) !important;
}

:deep(.mx-calendar-content .cell.in-range) {
    background: hsl(var(--muted)) !important;
    color: hsl(var(--foreground)) !important;
}

:deep(.mx-calendar-content .cell.disabled) {
    color: hsl(var(--muted-foreground)) !important;
    opacity: 0.5 !important;
}

/* Style the today cell */
:deep(.mx-calendar-content .cell.today) {
    background: hsl(var(--secondary)) !important;
    color: hsl(var(--secondary-foreground)) !important;
    font-weight: 500 !important;
}

/* Style month/year selectors */
:deep(.mx-calendar-header-label) {
    color: hsl(var(--foreground)) !important;
    font-weight: 500 !important;
}

/* Ensure proper spacing in calendar grid */
:deep(.mx-table-date) {
    border-collapse: separate !important;
    border-spacing: 2px !important;
}

/* Style the time picker if needed */
:deep(.mx-time-header) {
    background: hsl(var(--muted)) !important;
    color: hsl(var(--foreground)) !important;
    border-bottom: 1px solid hsl(var(--border)) !important;
}

:deep(.mx-time-option) {
    color: hsl(var(--foreground)) !important;
}

:deep(.mx-time-option:hover) {
    background: hsl(var(--accent)) !important;
    color: hsl(var(--accent-foreground)) !important;
}

:deep(.mx-time-option.active) {
    background: hsl(var(--primary)) !important;
    color: hsl(var(--primary-foreground)) !important;
}
</style>
