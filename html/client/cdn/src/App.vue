<script setup>

import { ref } from 'vue'
const fileInput = ref(null)
const uploading = ref(false)

const modalRef = ref(null)
const modalTitle = ref('')
const modalMessage = ref('')
const modalCloseCallback = ref(null)
const files = ref([])

const createModal = (title, message, callback = null) => {
  modalTitle.value = title
  modalMessage.value = message
  modalRef.value.classList.add('active')

  if (callback) {
    modalCloseCallback.value = callback
  }
}

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
      if (!e?.status) {
        throw (e?.error || 'Something went wrong! Please try again later.')
      }

      files.value = [...e.files, ...files.value]
    })
    .catch((e) => {
      console.log('xxx', e)
      createModal('Error', e)
    })
    .finally(() => {
      uploading.value = false
      fileInput.value.value = ''
    })
}

const upload = () => {
  fileInput.value.click()
}

const modalClose = () => {
  modalRef.value.classList.remove('active')
  modalRef.value.classList.add('hidden')
  if (modalCloseCallback.value) {
    modalCloseCallback.value()
  }

  modalCloseCallback.value = null
}

</script>

<template>
  <div>
    <h1>OpenCDN</h1>
    <input type="file" ref="fileInput" style="display: none" @change="onFileChange" />
    <button @click="upload" :disabled="uploading">Upload a file</button>

    <div v-if="files.length" class="files">
      <h2>Uploaded files</h2>
      <div class="file" v-for="f in files" :key="f.id">
        <div>
          
        </div>
      </div>
    </div>

    <div v-if="uploading" class="uploading-dialog">
      <h2>Uploading...</h2>
      <progress></progress>
    </div>
  </div>
  <Teleport to="body">
    <div ref="modalRef" class="modal hidden">
      <div class="modal-content">
        <h2>{{ modalTitle }}</h2>
        <p>{{ modalMessage }}</p>
        <div class="actions" align="right">
          <button ref="modalClose" class="close-modal" @click="modalClose">Close</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.uploading-dialog {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  z-index: 100;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  z-index: 100;
  background: rgba(0, 0, 0, 0.6);
  padding: .5rem;
}

.modal.active {
  display: flex;
}

.hidden {
  display: none;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
  max-width: 100%;
  width: 300px;
}

.modal-content h2 {
  margin: 0;
  color: #333;
  font-size: 1.5rem;
}

.modal-content p {
  margin: 0;
  color: #444;
  font-size: 1rem;
}

.modal-close {
  padding: .25rem .5rem;
  border-radius: .25rem;
  background: #dddddd;
  color: #333;
  transition: all .2s ease;
  border: none;
}

.modal-close:hover {
  background: #bbb;
}

.actions {
  margin-top: 1rem;
}
</style>
