<script setup>
import { ref, watch, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { debounce } from "lodash";
import { SearchIcon, XIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const props = defineProps({
    // The term the server actually filtered on, so a back-navigation refills the box.
    modelValue: {
        type: [String, null],
        default: null,
    },
    // Where a search lands. A category page searches within itself rather than escaping to the
    // whole catalogue, which is what a shopper who clicked into a category expects.
    routeName: {
        type: String,
        required: true,
    },
    routeParams: {
        type: [String, Object, null],
        default: null,
    },
});

const term = ref(props.modelValue ?? "");

watch(() => props.modelValue, (value) => {
    term.value = value ?? "";
});

const visit = (value) => {
    // The key is omitted rather than set to undefined: Ziggy still emits a bare `?q=` for a
    // present-but-empty value, which reads as an active search the page then has to undo.
    const query = value ? { q: value } : {};
    const params = props.routeParams ? [props.routeParams, query] : [query];

    router.get(route(props.routeName, ...params), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        // The sidebar counts and the community boxes do not change with the term, so only the
        // grid and the echoed term are refetched.
        only: ["packages", "search"],
    });
};

// Typed searches are debounced so a five letter word is one request rather than five; Enter and
// the clear button go immediately, because both are a finished decision.
const debouncedVisit = debounce(visit, 350);

const onInput = () => debouncedVisit(term.value.trim());

const submit = () => {
    debouncedVisit.cancel();
    visit(term.value.trim());
};

const clear = () => {
    debouncedVisit.cancel();
    term.value = "";
    visit("");
};

onUnmounted(() => debouncedVisit.cancel());
</script>

<template>
  <div class="relative">
    <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />

    <input
      v-model="term"
      type="search"
      :placeholder="__('Search packages…')"
      :aria-label="__('Search packages')"
      class="w-full pl-9 pr-9 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50"
      @input="onInput"
      @keyup.enter="submit"
    >

    <button
      v-if="term"
      type="button"
      class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
      :aria-label="__('Clear search')"
      @click="clear"
    >
      <XIcon class="w-4 h-4" />
    </button>
  </div>
</template>
