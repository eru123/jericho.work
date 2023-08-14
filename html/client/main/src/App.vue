<script setup>
import { watchEffect } from "vue";
import useServerData from "@/composables/useServerData";
const $server = useServerData();

watchEffect(() => {
    if ($server?.debug) {
        console.log($server);
    }
});

</script>
<template>
    <router-view />
</template>
