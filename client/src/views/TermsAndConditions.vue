<script setup>
import Markdown from "@/components/Markdown.vue";
import TermsAndConditionsMD from "@/md/terms-and-conditions.md";
</script>
<template>
    <Markdown :md="TermsAndConditionsMD" v-once />
</template>
