<script setup>
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'
import usePersistentData from '@/composables/usePersistentData'

const router = useRouter()
const theme = useTheme()
const drawer = usePersistentData('settings-drawer', null)

const links = computed(() => {
    return [
        {
            name: 'General',
            icon: 'mdi-cog',
            to: '/general',
            active: router.currentRoute.value.path === '/general'
        },
        {
            name: 'CDN',
            icon: 'mdi-cloud',
            to: '/cdn',
            active: router.currentRoute.value.path === '/cdn'
        }
    ]
})

const navTitle = computed(() => links.value.find(link => link.active)?.name ?? 'General')
const darkMode = usePersistentData('dark-mode', false)
watch(darkMode, () => theme.global.name.value = darkMode.value ? 'dark' : 'light', { immediate: true })

</script>
<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" :border="darkMode ? 0 : 1">
            <v-toolbar>
                <v-toolbar-title>Settings</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click="drawer = !drawer">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>
            <v-list :lines="false" ref="sidebarlist">
                <v-list-subheader>MENU</v-list-subheader>
                <v-list-item v-for="item in links" :key="item.name" :to="item.to" :active="item.active" color="primary">
                    <template v-slot:prepend>
                        <v-icon>{{ item.icon }}</v-icon>
                    </template>
                    <v-list-item-title>{{ item.name }}</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar>
            <v-app-bar-nav-icon @click="drawer = !drawer" v-if="!drawer"></v-app-bar-nav-icon>
            <v-toolbar-title>{{ navTitle }}</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon @click="darkMode = !darkMode">
                <v-icon>{{ darkMode ? 'mdi-weather-night' : 'mdi-weather-sunny' }}</v-icon>
            </v-btn>
        </v-app-bar>

        <v-main>
            <router-view />
        </v-main>
    </v-app>
</template>
  