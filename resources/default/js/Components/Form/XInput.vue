<template>
  <div
    :class="cn('space-y-2', divClass, attrs.class)"
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
      <span
        v-if="isRequired"
        class="text-destructive ml-1"
      >*</span>
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
      :required="isRequired"
      :disabled="disabled"
      :placeholder="placeholder"
      v-bind="inputAttrs"
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
import { computed, ref, useAttrs } from "vue";
import { useVModel } from "@vueuse/core";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";

// Anything not declared as a prop belongs on the input, not on the wrapper.
//
// Vue's default fallthrough puts leftover attributes on the root element, which here is the layout
// div — so every step, min, max and maxlength written by a caller was landing on a div and doing
// nothing. A price field with step="0.01" therefore behaved as step="1" and the browser rejected
// 4.99 with "the two nearest valid values are 4 and 5".
defineOptions({ inheritAttrs: false });

const props = defineProps({
    modelValue: {
        type: [Number, String, Array, Object, Boolean, Date],
        default: ""
    },
    name: String,
    help: String,
    label: String,
    type: {
        type: String,
        default: "text"
    },
    id: String,
    error: String,
    autocomplete: {
        type: String,
        default: "off"
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
        default: "flex-col"
    },
    inputClass: {
        type: String,
        default: ""
    },
    divClass: {
        type: String,
        default: ""
    },
    placeholder: String,
});

const emits = defineEmits(["update:modelValue"]);

const attrs = useAttrs();

// class and style stay on the wrapper, which is where the twelve callers that pass them expect
// them — those position the field within a grid. Everything else is an input attribute.
const inputAttrs = computed(() => {
    return Object.fromEntries(
        Object.entries(attrs).filter(([name]) => name !== "class" && name !== "style")
    );
});

/**
 * Whether `required` was set, in either spelling.
 *
 * A bare attribute — `required` rather than `:required="true"` — arrives as the empty string, which
 * is falsy, so the asterisk was skipped on every field written that way even though the native input
 * was already enforcing it.
 */
const isRequired = computed(() => props.required === "" || props.required === true || props.required === "true");

const inputRef = ref(null);

const modelValue = useVModel(props, "modelValue", emits, {
    passive: true,
    defaultValue: props.modelValue,
});

const inputClasses = computed(() => {
    return cn(
    // Base input styles with proper height
        "transition-colors",
        // Error state styling
        props.error && "border-destructive focus-visible:ring-destructive",
        // Custom classes
        props.inputClass
    );
});

const focus = () => {
    if (inputRef.value) {
        inputRef.value.focus();
    }
};

defineExpose({
    focus
});
</script>
