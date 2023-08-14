<script setup>
import PublicFooter from "@/components/PublicFooter.vue";
import PublicHeader from "../components/PublicHeader.vue";

const props = defineProps(["md"]);
</script>
<template>
    <PublicHeader />
    <div class="md-doc-container">
        <component :is="md" />
    </div>
    <PublicFooter />
</template>
<style lang="scss">
.md-doc-container {
    @apply container mx-auto w-full max-w-screen-lg px-4 py-8;

    .markdown-body {
        @apply text-primary-900;

        h1 {
            @apply text-3xl font-bold mb-4;
        }

        h2 {
            @apply text-2xl font-bold mb-4;
        }

        h3 {
            @apply text-xl font-bold mb-4;
        }

        h4 {
            @apply text-lg font-bold mb-4;
        }

        h5 {
            @apply text-base font-bold mb-4;
        }

        h6 {
            @apply text-sm font-bold mb-4;
        }

        ul {
            @apply list-disc;
        }

        ol {
            @apply list-decimal;
        }

        ul,
        ol {
            @apply my-4 pl-8;
            li {
                @apply mb-2;
            }
        }

        & > p {
            @apply mb-4;
        }

        a {
            @apply text-primary-500 no-underline hover:underline;
        }
    }
}
</style>
