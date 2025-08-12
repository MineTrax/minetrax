<template>
    <div :class="cn('space-y-2', divClass)">
        <!-- Label -->
        <Label v-if="label" :for="id" :class="cn('text-sm font-medium', errorToShow ? 'text-destructive' : 'text-foreground')">
            {{ label }}
            <span v-if="required" class="text-destructive ml-1">*</span>
        </Label>

        <!-- Preview / Dropzone -->
        <div class="group relative" :class="cn('w-full', previewWrapperClass)">
            <button
                type="button"
                class="block w-full h-full focus:outline-none"
                @click="openFileDialog"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="onDrop"
                :disabled="disabled"
            >
                <!-- With image -->
                <div v-if="displayUrl" :class="containerClasses">
                    <img :src="displayUrl" alt="preview" :class="imageClasses" />

                    <!-- Overlay controls (mobile: always visible) -->
                    <div class="absolute inset-0 flex md:hidden items-center justify-center gap-2 bg-black/40 text-white" :class="shapeClass">
                        <Button size="sm" variant="secondary" type="button" :disabled="disabled" @click.stop.prevent="openFileDialog">{{ changeLabel }}</Button>
                    </div>

                    <!-- Overlay controls (desktop: on hover) -->
                    <div
                        class="absolute inset-0 hidden md:flex items-center justify-center gap-2 bg-black/40 text-white rounded-md md:opacity-0 md:pointer-events-none md:group-hover:opacity-100 md:group-hover:pointer-events-auto transition-opacity"
                        :class="shapeClass"
                    >
                        <Button size="sm" variant="secondary" type="button" :disabled="disabled" @click.stop.prevent="openFileDialog">{{ changeLabel }}</Button>
                    </div>
                </div>

                <!-- Without image -->
                <div v-else :class="emptyClasses">
                    <div class="text-center space-y-1">
                        <div class="text-sm font-medium">{{ uploadLabel }}</div>
                        <div class="text-xs text-muted-foreground">{{ hintText }}</div>
                    </div>
                </div>
            </button>

            <!-- All controls are inside the preview overlay for better UX -->

            <!-- Remove/Clear icon placed as sibling to avoid clipping by rounded overflow -->
            <button
                v-if="showRemoveIcon && (hasSelection || (removable && currentUrl))"
                type="button"
                :disabled="disabled"
                class="absolute z-20 inline-flex items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white w-6 h-6 p-1"
                :class="iconPositionClass"
                :aria-label="hasSelection ? clearLabel : removeLabel"
                @click.stop.prevent="hasSelection ? clearSelection() : $emit('remove')"
            >
                <Icon :name="hasSelection ? 'close' : 'trash'" />
            </button>

            <!-- Hidden input -->
            <input ref="fileInputRef" type="file" class="hidden" :id="id" :name="name" :accept="accept" :disabled="disabled" @change="onFileChange" />
        </div>

        <!-- Help and Error -->
        <div v-if="help || errorToShow" class="flex gap-1" :class="cn(help && errorToShow ? 'justify-between' : errorToShow ? 'justify-end' : 'justify-start', helpErrorFlex)">
            <p v-if="help" class="text-xs text-muted-foreground">{{ help }}</p>
            <p v-if="errorToShow" class="text-xs text-destructive">{{ errorToShow }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from "vue";
import { useVModel } from "@vueuse/core";
import { cn } from "@/lib/utils";
import { Label } from "@/Components/ui/label";
import Button from "@/Components/ui/button/Button.vue";
import Icon from "@/Components/Icon.vue";

const props = defineProps({
    modelValue: { type: [File, Object, String, null], default: null },
    currentUrl: { type: String, default: "" },
    label: String,
    name: String,
    id: String,
    error: String,
    help: String,
    hint: { type: String, default: "PNG, JPG, GIF up to 10MB" },
    required: { type: [Boolean, String], default: false },
    disabled: { type: [Boolean, String], default: false },
    accept: { type: String, default: "image/*" },
    maxSizeMb: { type: Number, default: 100 },
    shape: { type: String, default: "rect" }, // 'rect' | 'circle'
    previewClass: { type: String, default: "" }, // consumer can set sizes e.g. 'h-28 w-28' or 'h-28 w-full'
    divClass: { type: String, default: "" },
    helpErrorFlex: { type: String, default: "flex-col" },
    removable: { type: Boolean, default: true },
    uploadLabel: { type: String, default: "Upload" },
    changeLabel: { type: String, default: "Change" },
    clearLabel: { type: String, default: "Clear" },
    removeLabel: { type: String, default: "Remove" },
    showRemoveIcon: { type: Boolean, default: true },
});

const emits = defineEmits(["update:modelValue", "remove"]);

const fileInputRef = ref(null);
const isDragging = ref(false);
const localError = ref("");
const objectUrl = ref("");

const internalValue = useVModel(props, "modelValue", emits, { passive: true });

const hasSelection = computed(() => internalValue.value instanceof File);
const errorToShow = computed(() => localError.value || props.error);

const shapeClass = computed(() => (props.shape === "circle" ? "rounded-full" : "rounded-md"));

const previewWrapperClass = computed(() => props.previewClass || (props.shape === "circle" ? "h-28 w-28" : "h-28 w-full"));

const containerClasses = computed(() => {
    return cn("relative overflow-hidden", previewWrapperClass.value, shapeClass.value);
});

const imageClasses = computed(() => cn("absolute inset-0 h-full w-full object-cover", shapeClass.value));

const emptyClasses = computed(() => {
    return cn(
        "relative flex items-center justify-center border-2 border-dashed text-muted-foreground/80 bg-muted/30 hover:bg-muted/50 transition-colors",
        isDragging.value ? "border-primary/60" : "border-border",
        previewWrapperClass.value,
        shapeClass.value
    );
});

const hintText = computed(() => props.hint);

const displayUrl = computed(() => {
    if (objectUrl.value) return objectUrl.value;
    return props.currentUrl || "";
});

// Delete/cross icon offset: allow flowing outside the rounded container when circular
const iconPositionClass = computed(() => (props.shape === "circle" ? "-top-2 -right-2" : "top-2 right-2"));

watch(internalValue, (val, oldVal) => {
    localError.value = "";
    // revoke old object URL
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = "";
    }

    if (val instanceof File) {
        // client-side validation
        if (!val.type.startsWith("image/")) {
            localError.value = "Invalid file type. Please select an image.";
            internalValue.value = null;
            return;
        }
        const sizeMb = val.size / (1024 * 1024);
        if (sizeMb > props.maxSizeMb) {
            localError.value = `File too large (>${props.maxSizeMb}MB).`;
            internalValue.value = null;
            return;
        }
        objectUrl.value = URL.createObjectURL(val);
    }
});

onBeforeUnmount(() => {
    if (objectUrl.value) URL.revokeObjectURL(objectUrl.value);
});

const openFileDialog = () => {
    if (props.disabled) return;
    fileInputRef.value?.click();
};

const onFileChange = (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    internalValue.value = file;
    // reset value to allow re-selecting same file
    e.target.value = "";
};

const onDrop = (e) => {
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (!files || files.length === 0) return;
    internalValue.value = files[0];
};

const clearSelection = () => {
    internalValue.value = null;
};
</script>

<style scoped></style>
