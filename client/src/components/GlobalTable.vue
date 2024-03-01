<script setup>
import { computed } from "vue";

const props = defineProps(["columns", "data", "actions"]);

const processedData = computed(() => {
    return props.data;
});
</script>
<template>
    <div class="overflow-x-auto">
        <div class="p-1.5 w-full inline-block align-middle">
            <div class="overflow-hidden border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th v-for="(column, i) in props.columns" :key="`th-${i}`" scope="col" :class="`px-6 py-3 text-xs font-bold text-${column?.align ?? 'left'
                                } text-gray-500 uppercase`">
                                {{ column?.name }}
                            </th>
                            <th v-if="props?.actions?.length"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="(row, i) in processedData" :key="i">
                            <td v-for="(column, j) in props.columns" :key="`td-${i}-${j}`"
                                class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                {{ row?.[column?.key] }}
                            </td>
                            <td v-if="props?.actions?.length" class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                <button v-for="(act, acti) in props.actions" :key="act?.key ?? act?.name ?? acti"
                                    @click="() => act?.click(row)" :class="act?.class ?? ''">
                                    {{ act?.name ?? act?.key }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<style scoped lang="scss"></style>
