<script setup>
import useDialogs from "@/composables/useDialog";
const dialogs = useDialogs();
</script>
<template>
    <Teleport to="body">
        <div class="dialogs-container" v-if="dialogs?.length">
            <div
                v-for="dialog in dialogs"
                :key="dialog.key"
                class="dialog-container"
            >
                <div v-if="dialog.type === 'info'" class="dialog info">
                    <div class="title"><v-icon class="icon info" name="bi-info-circle"/>{{ dialog.title }}</div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button class="primary" @click="typeof dialog?.onOk === 'function' ? dialog.onOk(dialog.close) : dialog.close()">
                            OK
                        </button>
                    </div>
                </div>
                <div v-else-if="dialog.type === 'warning'" class="dialog warning">
                    <div class="title"><v-icon class="icon warning" name="co-warning"/>{{ dialog.title }}</div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button class="primary" @click="typeof dialog?.onOk === 'function' ? dialog.onOk(dialog.close) : dialog.close()">
                            OK
                        </button>
                    </div>
                </div>
                <div v-else-if="dialog.type === 'error'" class="dialog danger">
                    <div class="title"><v-icon class="icon danger" name="md-dangerous-outlined"/>{{ dialog.title }}</div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button class="primary" @click="typeof dialog?.onOk === 'function' ? dialog.onOk(dialog.close) : dialog.close()">
                            OK
                        </button>
                    </div>
                </div>
                <div
                    v-else-if="dialog.type === 'confirm'"
                    class="dialog confirm"
                >
                    <div class="title"><v-icon class="icon confirm" name="bi-question-circle"/>{{ dialog.title }}</div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button
                            class="secondary"
                            @click="typeof dialog?.onCancel === 'function' ? dialog.onCancel(dialog.close) : dialog.close()"
                        >
                            Cancel
                        </button>
                        <button class="primary" @click="typeof dialog?.onOk === 'function' ? dialog.onOk(dialog.close) : dialog.close()">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
<style scoped lang="scss">
.dialogs-container {
    .dialog-container {
        @apply fixed top-0 left-0 right-0 bottom-0 z-auto;
        @apply flex items-center justify-center;
        @apply bg-black bg-opacity-50;

        .dialog {
            @apply w-fit min-w-[320px] max-w-screen-sm mx-auto;
            @apply flex flex-col justify-center items-center text-center;
            @apply rounded-md shadow-lg bg-white shadow-black;
            @apply p-4;
            @apply relative;
            @apply max-h-[calc(100vh-3.5rem)] overflow-y-auto;
            animation: dialog-in 0.2s ease-in-out;
            .title {
                @apply w-full block text-left text-base font-bold text-gray-900;

                .icon {
                    @apply inline-block mr-2;

                    &.info {
                        @apply text-cyan-700;
                    }

                    &.confirm {
                        @apply text-primary-700;
                    }

                    &.danger {
                        @apply text-red-700;
                    }

                    &.warning {
                        @apply text-orange-700;
                    }
                }
            }

            .message {
                @apply w-full block text-left text-base text-gray-700;
            }

            .actions {
                @apply mt-4 block w-full;
                @apply flex flex-row justify-center items-center;

                &.right {
                    @apply justify-end items-center;
                }

                &.left {
                    @apply justify-start items-center;
                }

                &.center {
                    @apply justify-center items-center;
                }

                button {
                    @apply px-4 py-1;
                    @apply rounded-md;
                    @apply text-white;
                    @apply transition-all duration-200 ease-in-out;

                    &:not(:last-child) {
                        @apply mr-2;
                    }

                    &.primary {
                        @apply text-primary-50 bg-primary-800 hover:bg-primary-900;
                    }

                    &.secondary {
                        @apply bg-gray-500 hover:bg-gray-600;
                    }

                    &.danger {
                        @apply bg-red-500 hover:bg-red-600;
                    }
                }
            }
        }
    }
}

@keyframes dialog-in {
    from {
        opacity: 0;
        scale: 0;
    }
    to {
        opacity: 1;
        scale: 1;
    }
}
</style>
