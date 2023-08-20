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
                    <div class="title">
                        <v-icon class="icon info" name="bi-info-circle" />{{
                            dialog.title
                        }}
                    </div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button
                            class="primary"
                            @click="
                                typeof dialog?.onOk === 'function'
                                    ? dialog.onOk(dialog.close)
                                    : dialog.close()
                            "
                        >
                            OK
                        </button>
                    </div>
                </div>
                <div
                    v-else-if="dialog.type === 'warning'"
                    class="dialog warning"
                >
                    <div class="title">
                        <v-icon class="icon warning" name="co-warning" />{{
                            dialog.title
                        }}
                    </div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button
                            class="primary"
                            @click="
                                typeof dialog?.onOk === 'function'
                                    ? dialog.onOk(dialog.close)
                                    : dialog.close()
                            "
                        >
                            OK
                        </button>
                    </div>
                </div>
                <div v-else-if="dialog.type === 'error'" class="dialog danger">
                    <div class="title">
                        <v-icon
                            class="icon danger"
                            name="md-dangerous-outlined"
                        />{{ dialog.title }}
                    </div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button
                            class="primary"
                            @click="
                                typeof dialog?.onOk === 'function'
                                    ? dialog.onOk(dialog.close)
                                    : dialog.close()
                            "
                        >
                            OK
                        </button>
                    </div>
                </div>
                <div
                    v-else-if="dialog.type === 'confirm'"
                    class="dialog confirm"
                >
                    <div class="title">
                        <v-icon
                            class="icon confirm"
                            name="bi-question-circle"
                        />{{ dialog.title }}
                    </div>
                    <div class="message">{{ dialog.message }}</div>
                    <div class="actions right">
                        <button
                            class="secondary"
                            @click="
                                typeof dialog?.onCancel === 'function'
                                    ? dialog.onCancel(dialog.close)
                                    : dialog.close()
                            "
                        >
                            Cancel
                        </button>
                        <button
                            class="primary"
                            @click="
                                typeof dialog?.onOk === 'function'
                                    ? dialog.onOk(dialog.close)
                                    : dialog.close()
                            "
                        >
                            OK
                        </button>
                    </div>
                </div>
                <div
                    v-else-if="dialog.type === 'loading' && !dialog?.message"
                    class="dialog loading no-message"
                >
                    <v-icon
                        class="icon loading loading-1"
                        name="bi-hourglass-split"
                    />
                    <v-icon
                        class="icon loading loading-2"
                        name="bi-hourglass-bottom"
                    />
                </div>
                <div
                    v-else-if="dialog.type === 'loading' && dialog?.message"
                    class="dialog loading with-message"
                >
                    <div class="icon-container">
                        <v-icon
                            class="icon loading loading-1"
                            name="bi-hourglass-split"
                        />
                        <v-icon
                            class="icon loading loading-2"
                            name="bi-hourglass-bottom"
                        />
                    </div>
                    <div class="message">{{ dialog.message }}</div>
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

            &.loading {
                @apply w-32 h-32;

                .icon.loading {
                    @apply text-primary-700 w-[3rem] h-[3rem] min-w-[3rem] min-h-[3rem];
                    position: absolute;
                    opacity: 0;

                    &.loading-1 {
                        animation: loading-1 1s infinite ease-in-out;
                    }

                    &.loading-2 {
                        animation: loading-2 1s infinite ease-in-out;
                    }
                }

                &.no-message {
                    @apply rounded-full w-[4rem] h-[4rem] min-w-[4rem] min-h-[4rem] p-2;
                }

                &.with-message {
                    @apply flex flex-col items-center justify-center min-w-[200px] px-2 py-4 h-fit;

                    .icon-container {
                        @apply flex flex-col items-center justify-center;
                        @apply w-full h-[3rem];
                    }

                    .message {
                        @apply w-full block text-center text-base text-gray-700;
                    }
                }

                @keyframes loading-1 {
                    0% {
                        opacity: 1;
                    }
                    50% {
                        opacity: 1;
                    }
                    51% {
                        opacity: 0;
                    }
                    100% {
                        opacity: 0;
                    }
                }

                @keyframes loading-2 {
                    0% {
                        opacity: 0;
                    }
                    50% {
                        opacity: 0;
                    }
                    51% {
                        opacity: 1;
                    }
                    70% {
                        transform: rotate(0deg);
                    }
                    80% {
                        transform: rotate(180deg);
                    }
                    100% {
                        transform: rotate(180deg);
                        opacity: 1;
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
