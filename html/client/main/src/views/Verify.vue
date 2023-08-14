<script setup>
import useServerData from "@/composables/useServerData";
import PublicFooter from "@/components/PublicFooter.vue";
import PublicHeader from "@/components/PublicHeader.vue";

const $server = useServerData();
</script>
<template>
    <PublicHeader />
    <div class="container">
		<v-icon name="bi-x-circle" v-if="$server?.error" class="icon error"/>
		<v-icon name="bi-check-circle" v-if="!$server?.error" class="icon success"/>
        <h1>{{ $server?.title }}</h1>
        <p v-if="$server?.success || $server?.error">
            {{ $server?.success || $server?.error }}
        </p>
    </div>
    <PublicFooter />
</template>
<style scoped lang="scss">
.container {
    @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

	.icon {
		@apply mb-6 h-[4rem] w-[4rem];

		&.success {
			@apply text-green-600;
		}

		&.error {
			@apply text-red-500;
		}
	}

    h1 {
        @apply text-2xl font-semibold text-primary-900 mb-2;

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
</style>
