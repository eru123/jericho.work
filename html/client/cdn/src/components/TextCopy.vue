<script setup>
import { inject } from 'vue'

const createModal = inject('createModal')

const props = defineProps({
    label: String,
    text: String
})

const copy = (text) => {
    navigator.clipboard.writeText(text)
    .then(() => {
      createModal('Copied!', 'The URL has been copied to your clipboard.')
    })
    .catch(() => {
      createModal('Error', 'Failed to copy the text to your clipboard. Please try on a browser that supports this feature or copy the text manually.')
    })
}
</script>

<template>
    <div class="file-copy">
        <span class="file-copy-label">
            {{ props.label }}
        </span>
        <div class="file-copy-data">
            <input type="text" :value="props.text" readonly />
            <button @click="copy(props.text)">Copy</button>
        </div>
    </div>
</template>

<style scoped>
.file-copy {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

.file-copy-label {
    font-size: 1rem;
    text-align: left;
    color: #cfcfcf;
}

.file-copy-data {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
}

.file-copy-data input[readonly] {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: .25rem .5rem;
    font-size: 1rem;
    width: 100%;
    background: #fafafa;
    color: #333;
}

.file-copy-data button {
    padding: .25rem .5rem;
    border-radius: .25rem;
    transition: all .2s ease;
    border: none;
    background: #006650;
    color: #fff;
}

.file-copy-data button:hover {
    background: #00503f;
}
</style>