<template>
    <v-public-header />
    <div class="landing-1">
        <h3>Your dream and solution<span>let's make it!</span></h3>
        <p>
            We work closely with you to understand your unique needs and develop customized solutions that turn your
            dreams into reality. Our commitment to collaboration and partnership
            ensures that we work together with you every step of the way to make
            your vision a reality.
        </p>

        <a href="#info">Learn More</a>
    </div>
    <v-public-footer />
</template>
<style scoped lang="scss">
.landing-1 {
    @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

    h3 {
        @apply text-2xl font-semibold text-primary-900 mb-6;

        span {
            @apply mt-2 block uppercase text-primary-800 text-4xl font-bold;
        }
    }

    p {
        @apply text-lg text-primary-900 mx-auto max-w-prose mb-4;
    }

    a {
        @apply px-4 py-2 rounded-md text-sm font-normal text-primary-50 bg-primary-900 hover:bg-primary-800;
    }
}

.landing-2 {
    @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

    &:target {
        @apply mt-[-3.5rem];
    }
}
</style>
