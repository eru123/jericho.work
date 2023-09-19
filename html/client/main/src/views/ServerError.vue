<script setup>
import useServerData from "@/composables/useServerData";
const $server = useServerData();

</script>
<template>
    <v-public-page>
        <div class="container">
            <v-icon class="icon" name="io-logo-apple-ar" />
            <h1>{{ $server?.error?.title || 'SERVER ERROR'}}</h1>
            <p>{{ $server?.error?.message }}</p>
            <button @click="delete $server.error">OK</button>
        </div>
    </v-public-page>
</template>
<style scoped lang="scss">
.container {
    @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

    .icon {
        @apply mb-6 h-[4rem] w-[4rem] text-red-500;
    }

    h1 {
        @apply text-2xl font-semibold text-primary-900 mb-2;

        span {
            @apply mt-2 block uppercase text-primary-800 text-4xl font-bold;
        }
    }

    p {
        @apply text-lg text-primary-900 mx-auto max-w-prose mb-4;
    }

    a {
        @apply px-4 py-2 rounded-md text-sm font-normal text-primary-50 bg-primary-900 hover:bg-primary-800;
    }

    button {
        @apply px-4 py-2 rounded-md text-sm font-normal text-primary-50 bg-primary-900 hover:bg-primary-800 min-w-[5rem];
    }
}
</style>
