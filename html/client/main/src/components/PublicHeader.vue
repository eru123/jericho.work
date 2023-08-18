<script setup>
import LogoWD from "@/assets/logo-w-dark.svg";
import LogoD from "@/assets/logo-dark.svg";
</script>
<template>
    <header class="">
        <nav class="">
            <router-link to="/" class="brand">
                <img :src="LogoWD" alt="Logo" />
                <img :src="LogoD" alt="Logo" />
            </router-link>
            <div class="actions">
                <router-link to="/login">Login</router-link>
                <router-link to="/register">Register</router-link>
            </div>
        </nav>
    </header>
</template>
<style scoped lang="scss">
header {
    @apply bg-primary-900 text-primary-50 flex justify-center sticky top-0 z-50;

    nav {
        @apply container mx-auto flex justify-between items-center max-w-screen-lg px-4 py-2;

        .brand {
            @apply flex items-center;

            img:first-child {
                @apply w-auto h-[2.3rem] mr-2 my-[.1rem] hidden lg:block;
            }

            img:last-child {
                @apply w-auto h-[2.5rem] mr-2 block lg:hidden;
            }
        }

        .actions {
            @apply flex items-center;

            a {
                @apply px-4 py-2 rounded-md text-sm font-normal text-primary-50 hover:bg-primary-800;
            }
        }
    }
}
</style>
