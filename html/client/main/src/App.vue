<script setup>
import { watchEffect } from "vue";
import useServerData from "@/composables/useServerData";
import ServerError from "@/views/ServerError.vue";
import Dialogs from "@/components/Dialogs.vue";
import { useRouter } from "vue-router";

const $server = useServerData();
const router = useRouter();

watchEffect(() => {
    window.__skiddph__redirect = (path) => {
        window.scrollTo(0, 0);
        router.push(path);
    }

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
    <Dialogs />
</template>
