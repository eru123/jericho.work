<script setup>
import LogoD from "@/assets/logo-light.svg";
</script>
<template>
    <v-public-page>
        <div class="container" v-once>
            <img :src="LogoD" alt="Logo" class="bouncing-rotating icon" />
        </div>
    </v-public-page>
</template>
<style scoped lang="scss">
.container {
    @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

    .icon {
        @apply mb-6 h-auto w-[8rem];
    }
}

.bouncing-rotating {
    animation: bounce-rotate 3s infinite ease-in-out;
}

@keyframes bounce-rotate {
    0% {
        transform: translateY(0px) rotate(0deg) scaleX(1);
    }

    25% {
        transform: translateY(-20px) rotate(5deg) scaleX(-1);
    }

    50% {
        transform: translateY(0px) rotate(-5deg) scaleX(1);
    }

    75% {
        transform: translateY(-20px) rotate(5deg) scaleX(-1);
    }

    100% {
        transform: translateY(0px) rotate(0deg) scaleX(1);
    }
}</style>
