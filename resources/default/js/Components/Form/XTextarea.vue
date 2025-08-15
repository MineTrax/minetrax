<template>
    <div :class="cn('space-y-2', divClass)">
        <!-- Label -->
        <Label v-if="label" :for="id" :class="cn('text-sm font-medium', error ? 'text-destructive' : 'text-foreground')">
            {{ label }}
            <span v-if="required" class="text-destructive ml-1">*</span>
        </Label>

        <!-- Textarea -->
        <Textarea
            :id="id"
            ref="textareaRef"
            v-model="modelValue"
            :name="name"
            :class="textareaClasses"
            :autocomplete="autocomplete"
            :autofocus="autofocus"
            :required="required"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="handleTextareaInput"
        />

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
import { computed, ref, nextTick, watch, onMounted } from "vue";
import { useVModel } from "@vueuse/core";
import { Textarea } from "@/Components/ui/textarea";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";

const props = defineProps({
    modelValue: {
        type: [Number, String, Array, Object, Boolean, Date],
        default: "",
    },
    name: String,
    help: String,
    label: String,
    id: String,
    error: String,
    autocomplete: {
        type: String,
        default: "off",
    },
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
        default: "flex-col",
    },
    autoResize: {
        type: Boolean,
        default: true,
    },
    textareaClass: {
        type: String,
        default: "",
    },
    divClass: {
        type: String,
        default: "",
    },
    placeholder: String,
    rows: {
        type: Number,
        default: 3,
    },
});

const emits = defineEmits(["update:modelValue"]);

const textareaRef = ref(null);

const modelValue = useVModel(props, "modelValue", emits, {
    passive: true,
    defaultValue: props.modelValue,
});

const textareaClasses = computed(() => {
    return cn(
        // Base textarea styles
        "transition-colors resize-none",
        `min-h-[${props.rows * 1.5 + 2}rem]`,
        // Hide scrollbar when autoresize is enabled
        props.autoResize && "hide-scrollbar",
        // Error state styling
        props.error && "border-destructive focus-visible:ring-destructive",
        // Custom classes
        props.textareaClass
    );
});

const resizeTextarea = async () => {
    if (props.autoResize && textareaRef.value) {
        await nextTick();
        const textarea = textareaRef.value.$el || textareaRef.value;
        if (textarea) {
            textarea.style.height = "auto";
            textarea.style.height = `${textarea.scrollHeight}px`;
        }
    }
};

const handleTextareaInput = async (event) => {
    await resizeTextarea();
};

// Watch for changes in modelValue to trigger resize
watch(modelValue, async () => {
    await resizeTextarea();
});

// Resize on mount if there's initial content
onMounted(async () => {
    await resizeTextarea();
});

const focus = () => {
    if (textareaRef.value) {
        const textarea = textareaRef.value.$el || textareaRef.value;
        textarea.focus();
    }
};

defineExpose({
    focus,
    resizeTextarea,
});
</script>
