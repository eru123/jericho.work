<script setup>

import { computed } from 'vue'
import { aclToArray } from '@/lib/app'
import usePersistentData from '@/composables/usePersistentData'
import { useRouter } from 'vue-router'

const user = usePersistentData('user', null)
const router = useRouter();
const sidebarItems = computed(() => ([
    {
        to: '/dashboard',
        name: 'Dashboard',
    },
    {
        to: '/profile',
        name: 'Profile',
        icon: 'hi-solid-user-circle'
    },
    ...aclToArray('website|admin', {
        to: '/websites',
        name: 'Websites',
    }, user.value),
    ...aclToArray('database|admin', {
        to: '/databases',
        name: 'Databases',
    }, user.value),
    ...aclToArray('sftp|admin', {
        to: '/sftp',
        name: 'SFTP',
    }, user.value),
]))

const pageName = computed(() => router?.currentRoute?.value?.name);

</script>
<template>
    <v-fixed-layout :sidebar="sidebarItems" :page-name="pageName" v-if="user?.token">
        <router-view />
    </v-fixed-layout>
    <v-public-page v-else>
        <router-view />
    </v-public-page>
</template>
<style scoped lang="scss"></style>