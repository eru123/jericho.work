<script setup>
import PublicFooter from "@/components/PublicFooter.vue";
import PublicHeader from "@/components/PublicHeader.vue";
</script>
<template>
    <PublicHeader />
    <div class="public-page-container">
        <slot></slot>
    </div>
    <PublicFooter />
</template>
<style scoped lang="scss">
.public-page-container {
    @apply w-full min-h-[calc(100vh-3.5rem)] max-w-full;
}
</style>
