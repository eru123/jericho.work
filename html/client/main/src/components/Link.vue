<script setup>
import { computed, useAttrs } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { paths as routerPaths } from "@/router";

const router = useRouter();

const props = defineProps({
    ...RouterLink.props,
    inactiveClass: String,
});

const isExternalLink = computed(() => {
    return typeof props.to === "string" && /^(https?|mailto):/.test(props.to);
});

const attrs = useAttrs();

const handleClick = (e) => {
    if (isExternalLink.value) {
        return;
    }

    if (props.disabled) {
        e.preventDefault();
        return;
    }

    if (props.to === undefined) {
        return;
    }

    if (props.to === null) {
        return;
    }

    if (props.to === false) {
        e.preventDefault();
        return;
    }

    if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) {
        return;
    }

    if (e.defaultPrevented) {
        return;
    }

    if (e.button !== undefined && e.button !== 0) {
        return;
    }

    if (props.target && props.target !== "_self") {
        return;
    }

    e.preventDefault();
    window.scrollTo(0, 0);
    router.push(props.to);
};
</script>
<template>
    <a
        v-bind="attrs"
        :href="to"
        @click="handleClick"
        :target="
            !props?.target
                ? isExternalLink
                    ? '_blank'
                    : typeof props.to === 'string' &&
                      routerPaths.includes(props.to)
                    ? '_self'
                    : '_blank'
                : props.target
        "
    >
        <slot />
    </a>
</template>
