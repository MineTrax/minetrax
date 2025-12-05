<template>
    <DropdownMenu>
        <DropdownMenuTrigger>
            <button v-tippy :title="__('Change Language')"
                class="flex items-center text-sm font-medium text-foreground hover:text-foreground">
                <img :src="`/images/flags/flags-iso/shiny/48/${$page.props.localeIsoCode.toUpperCase()}.png`"
                    class="w-6 mr-2">
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="min-w-48">
            <DropdownMenuItem v-for="(locale) in availableLocales" :key="locale.code" @click="setLocale(locale.code)"
                class="cursor-pointer">
                <img :src="`/images/flags/flags-iso/shiny/48/${locale.iso_code.toUpperCase()}.png`" class="w-8 mr-2">
                <span>{{ locale.display }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const form = useForm({
    locale: usePage().props.locale,
});
let availableLocales = {};

const getAvailableLocales = () => {
    axios.get(route('locale.list')).then(response => {
        availableLocales = response.data;
    }).catch(error => {
        console.log(error);
    });
};

onMounted(() => {
    getAvailableLocales();
});

const setLocale = (locale) => {
    form.locale = locale;
    form.post(route('locale.set'), {
        preserveScroll: true,
        onSuccess: () => {
            location.reload();
        }
    });
};
</script>
