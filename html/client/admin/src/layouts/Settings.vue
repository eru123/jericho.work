<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const drawer = ref(null)

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

</script>
<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" :border="0">
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
        </v-app-bar>

        <v-main>
            <router-view />
        </v-main>
    </v-app>
</template>
  