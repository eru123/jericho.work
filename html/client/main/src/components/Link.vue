<script setup>
import { computed, useSlots, useAttrs } from "vue";
import { RouterLink } from "vue-router";
import { paths as routerPaths } from "@/router";

const props = defineProps({
    ...RouterLink.props,
    inactiveClass: String,
});

const isExternalLink = computed(() => {
    return typeof props.to === "string" && props.to.startsWith("http");
});

const attrs = useAttrs();
</script>
<template>
    <a v-if="isExternalLink" v-bind="attrs" :href="to" target="_blank">
        <slot />
    </a>
    <router-link
        v-else
        v-bind="props"
        custom
        v-slot="{ isActive, href, navigate }"
    >
        <a
            v-bind="attrs"
            :href="href"
            @click="navigate"
            :class="isActive ? 'active' : ''"
            :target="
                typeof props.to === 'string' && routerPaths.includes(props.to)
                    ? '_self'
                    : '_blank'
            "
        >
            <slot />
        </a>
    </router-link>
</template>
