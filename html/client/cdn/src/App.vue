<script setup>

// define a ref for the file input
import { ref } from 'vue'
const fileInput = ref(null)
const uploading = ref(false)

const modalRef = ref(null)
// function to create a referenced modal
const createModal = (title, message, callback = null) => {
  const modal = document.createElement('div')
  modal.className = 'modal'
  modal.innerHTML = `
    <div class="modal-content">
      <h1>${title}</h1>
      <p>${message}</p>
      <button class="modal-close">Close</button>
    </div>
  `
  modalRef.value = modal
  document.body.appendChild(modal)
  modal.querySelector('.modal-close').addEventListener('click', () => {
    modalRef.value = null
    document.body.removeChild(modal)
    if (callback) {
      callback()
    }
  })
}

// on file input change, upload file to server
const onFileChange = () => {
  if (!fileInput.value.files.length) {
    return
  }
  const file = fileInput.value.files[0]
  const formData = new FormData()
  formData.append('file', file)
  uploading.value = true
  fetch('/upload', {
    method: 'POST',
    body: formData
  })
    .then((e) => e.json())
    .then((e) => {
      console.log(e)
    })
    .catch((e) => {
      console.error(e)
      // create a modal to show the error
      createModal('Error', e.message)
    })
    .finally(() => {
      uploading.value = false
      fileInput.value.value = ''
    })
}

const upload = () => {
  fileInput.value.click()
}

</script>

<template>
  <div>
    <h1>OpenCDN</h1>
    <h2>Upload a file</h2>
    <input type="file" ref="fileInput" style="display: none" @change="onFileChange" />
    <button @click="upload" :disabled="uploading">Upload</button>
    <div v-if="uploading">Uploading...</div>
  </div>
</template>

<style scoped>
.logo {
  height: 6em;
  padding: 1.5em;
  will-change: filter;
  transition: filter 300ms;
}

.logo:hover {
  filter: drop-shadow(0 0 2em #646cffaa);
}

.logo.vue:hover {
  filter: drop-shadow(0 0 2em #42b883aa);
}
</style>
