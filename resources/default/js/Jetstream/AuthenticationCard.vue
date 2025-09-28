<template>
    <div class="md:mt-24 mb-10 md:mb-40 flex flex-col md:justify-center items-center pt-7 md:pt-0">
        <div>
            <slot name="logo" />
        </div>

        <Card class="m-3 md:m-0 md:mt-6 md:w-full md:max-w-md mt-6 px-6 py-4 overflow-hidden">
            <slot />
        </Card>
    </div>
</template>

<script setup>
import { Card } from "@/Components/ui/card";
</script>
