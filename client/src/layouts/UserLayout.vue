<script setup>

import { computed } from 'vue'
import { aclToArray } from '@/lib/app'
import usePersistentData from '@/composables/usePersistentData'

const user = usePersistentData('user', null)

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

</script>
<template>
    <v-fixed-layout :sidebar="sidebarItems">
        <router-view />
    </v-fixed-layout>
</template>
<style scoped lang="scss"></style>