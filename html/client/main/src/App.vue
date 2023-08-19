<script setup>
import { watchEffect } from "vue";
import useServerData from "@/composables/useServerData";
import ServerError from "@/views/ServerError.vue";

const $server = useServerData();

watchEffect(() => {
    if ($server?.debug) {
        console.log("Server Data", $server);
        if ($server?.error) {
            console.log("Server error: ", $server?.error);
        }
    }
});
</script>
<template>
    <ServerError v-if="$server?.error" />
    <router-view v-else />
</template>
