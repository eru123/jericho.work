<script setup>
import PublicHeader from "@/components/PublicHeader.vue";
</script>
<template>
    <PublicHeader />
    <div class="public-page-container">
        <div class="container" v-once>
            <div class="side-navigation">
                <v-link to="/profile" class="parent-item">Link</v-link>
                <v-link to="/" v-for="i in 100" class="parent-item">Link {{ i }}</v-link>
            </div>
        </div>
    </div>
</template>
<style scoped lang="scss">
.public-page-container {
    @apply w-full min-h-[calc(100vh-3.5rem)] max-w-full;
}

.container {
    @apply w-full max-w-screen-md min-h-[calc(100vh-3.5rem)] flex flex-row;

    .side-navigation {
        @apply hidden md:flex min-h-full bg-primary-900 p-2 min-w-[200px] text-primary-50 flex-col;

        .parent-item {
            @apply py-2 px-4 rounded-md hover:bg-primary-800;
        }
    }

    .icon {
        @apply mb-6 h-auto w-[8rem];
    }
}

.bouncing-rotating {
    animation: bounce-rotate 3s infinite ease-in-out;
}
</style>
