<script setup>
import { ref, watchEffect } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const sidebarOpen = ref(false)
const sidebar = [
    {
        type: 'link',
        label: 'Overview',
        icon: 'home',
        to: { name: 'Overview' },
        active: router.currentRoute.value.name === 'Overview'
    },
    {
        type: 'group',
        label: 'Environment',
        icon: 'cog',
        children: [
            {
                type: 'link',
                label: 'System',
                icon: 'cog',
                to: { name: 'SystemEnvironment' },
                active: router.currentRoute.value.name === 'SystemEnvironment'
            },
            {
                type: 'link',
                label: 'Application',
                icon: 'cog',
                to: { name: 'ApplicationEnvironment' },
            }
        ]
    },
];

watchEffect(() => {
    console.log(router)
    console.log(sidebar)
})

</script>
<template>
    <div class="h-screen overflow-hidden">
        <div class="min-h-full w-[250px] bg-blue-50">
            <div v-for="(s, i) in sidebar" :key="i">
                <router-link v-if="s.type == 'link'" :to="s.to">
                    <i :class="`fas fa-${s.icon} mr-2`"></i>
                    <span>{{ s.label }}</span>
                </router-link>
                <div v-else-if="s.type == 'group'">
                    <button>
                        <i :class="`fas fa-${s.icon} mr-2`"></i>
                        <span>{{ s.label }}</span>
                    </button>
                    <div v-for="(c, ii) in s.children" v-if="c?.type == 'link'" :key="`${i}-${ii}`" :to="c.to">
                        {{ c }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>