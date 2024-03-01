<script setup>
import { ref, watchEffect, computed } from "vue";
import usePersistentData from "@/composables/usePersistentData";
import PublicHeader from "@/components/PublicHeader.vue";

const props = defineProps(["sidebar", "pageName"]);

const sidebarOpen = usePersistentData("sidebarOpen", null);
const sidebarFirstLoad = ref(true);

const toggleSidebar = () => {
    sidebarFirstLoad.value = false;
    sidebarOpen.value = !sidebarOpen.value;
};

const showSidebar = () =>
    props?.sidebar &&
    !Array.isArray(props.sidebar) &&
    typeof props.sidebar === "object" &&
    props.sidebar !== null;

const sidebarClass = computed(() => {
    const classes = ["side-navigation"];

    if (
        !sidebarOpen.value &&
        sidebarOpen.value === null &&
        sidebarFirstLoad.value
    ) {
        classes.push("hide");
    }

    if (
        sidebarOpen.value !== null &&
        !sidebarOpen.value &&
        !sidebarFirstLoad.value
    ) {
        classes.push("hide");
        classes.push("slide-in-left");
    }

    if (sidebarOpen.value && !sidebarFirstLoad.value) {
        classes.push("slide-in-right");
    }

    return classes.join(" ");
});

watchEffect(() => {
    if (sidebarOpen.value === null) {
        sidebarOpen.value = window.innerWidth >= 768;
        sidebarFirstLoad.value = true;
    } else {
        sidebarFirstLoad.value = false;
    }
});

window?.addEventListener("resize", () => {
    sidebarFirstLoad.value = false;
    sidebarOpen.value = window.innerWidth >= 768;
});
</script>
<template>
    <PublicHeader />
    <div class="fixed-layout-container">
        <div :class="sidebarClass" v-if="showSidebar">
            <v-link v-for="item in props.sidebar" :key="item.name" :to="item.to ? item.to : ''"
                :class="item.heading ? 'parent-heading' : 'parent-item'">
                {{ item.name }}
            </v-link>
        </div>
        <div class="content">
            <div class="content-header">
                <button v-if="showSidebar" @click="toggleSidebar" class="sidebar-toggle">
                    <v-icon name="hi-solid-menu-alt-2"></v-icon>
                </button>
                <span v-if="props?.pageName" class="content-title">{{
                    props?.pageName
                }}</span>
            </div>
            <slot></slot>
        </div>
    </div>
</template>
<style scoped lang="scss">
.fixed-layout-container {
    @apply w-screen min-h-[calc(100vh-3.5rem)] flex flex-row;

    .side-navigation {
        @apply flex h-[calc(100vh-3.5rem)] bg-primary-900 p-2 min-w-[220px] text-primary-50 flex-col overflow-y-auto transition-all duration-200 translate-x-0;

        &.hide {
            @apply -translate-x-full w-0 min-w-0 p-0;
        }

        .parent-item {
            @apply py-2 md:py-1 px-4 rounded-md hover:bg-primary-800;
        }

        .parent-heading {
            @apply py-1 pl-2 pr-6 -mx-2 my-2 rounded-none text-sm bg-primary-800 text-primary-50 cursor-default;
        }
    }

    .content {
        @apply block w-full h-[calc(100vh-3.5rem)] overflow-y-auto;

        .content-header {
            @apply sticky top-0 flex bg-white shadow-md;

            .content-title {
                @apply text-sm font-semibold px-4 py-2;
            }

            .sidebar-toggle {
                @apply py-2 px-4 bg-primary-900 text-primary-50 hover:bg-primary-800 transition-all duration-200 translate-x-0;

                .icon {
                    @apply text-sm;
                }
            }
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
