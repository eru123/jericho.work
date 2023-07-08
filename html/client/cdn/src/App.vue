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
      createModal('Success!', e?.message || 'File uploaded successfully.')
    })
    .catch((e) => {
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

const copy = (text) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      createModal('Copied!', 'The URL has been copied to your clipboard.')
    })
    .catch(() => {
      createModal('Error', 'Something went wrong! Please try again later.')
    })
}

const hsize = (bytes) => {
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${['B', 'KB', 'MB', 'GB', 'TB'][i]}`
}

</script>

<template>
  <div>
    <h1 class="brand">Open<span class="cdn">CDN</span></h1>
    <input type="file" ref="fileInput" style="display: none" @change="onFileChange" />
    <button @click="upload" :disabled="uploading">Upload a file</button>

    <div v-if="files.length" class="files">
      <h2>Uploaded files</h2>
      <div class="file" v-for="f in files" :key="f.id">
        <div v-if="f.uploaded">
          <h3 class="file-header">
            <div class="file-name">{{ f.file.name }}<br /><small>{{ hsize(f.file.size) }}</small></div>
            <div class="file-actions">
              <a :href="f.stream" target="_blank">Stream</a>
              <a :href="f.download" target="_blank">Download</a>
            </div>
          </h3>
          <div class="file-copies">

            <div class="file-copy">
              <span class="file-copy-label">
                Stream URL
              </span>
              <div class="file-copy-data">
                <input type="text" :value="f.stream" readonly />
                <button @click="copy(f.stream)">Copy</button>
              </div>
            </div>

            <div class="file-copy">
              <span class="file-copy-label">
                Download URL
              </span>
              <div class="file-copy-data">
                <input type="text" :value="f.download" readonly />
                <button @click="copy(f.download)">Copy</button>
              </div>
            </div>

            <div class="file-copy" v-if="f?.html?.tag">
              <span class="file-copy-label">
                HTML Tag
              </span>
              <div class="file-copy-data">
                <input type="text" :value="f.html.tag" readonly />
                <button @click="copy(f.html.tag)">Copy</button>
              </div>
            </div>

          </div>
        </div>
      </div>
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
    <div v-show="uploading" class="uploading-dialog">
      <h2>Uploading...</h2>
      <progress></progress>
    </div>
  </Teleport>
  <Teleport to="body">
    
  </Teleport>
</template>

<style scoped>
.files {
  max-width: 800px;
  width: 100%;
  margin: 2rem auto 0 auto;
  padding: 0 1rem 2rem 1rem;
}

.files>h2 {
  margin: 0;
  font-size: .9rem;
  color: #cfcfcf;
  text-align: left;
  text-transform: uppercase;
}

.file {
  margin-top: 1rem;
  border-radius: 5px;
  border: 0;
  padding: 1rem;
  background-color: #121216;
}

.file-header {
  display: flex;
  flex-direction: column;
}

.file-name {
  font-size: 1.25rem;
  color: #fafafa;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  margin-right: 1rem;
}

.file-name small {
  font-size: .75rem;
  color: #cfcfcf;
}

.file-actions {
  width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: center;
  margin-top: 1rem;
  gap: .5rem;
}

.file-actions a {
  padding: .25rem 1rem;
  font-size: 1rem;
  border-radius: .25rem;
  background: #006650;
  color: #fff;
}

.file-actions a:hover {
  background: #00503f;
}

.file-copies {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
}

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

.brand {
  font-size: 3rem;
  font-weight: 700;
  margin: 2rem 2rem 2rem 1rem;
  color: #fff;
  user-select: none;
}

.brand .cdn {
  color: #006650;
}

.uploading-dialog {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
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
  right: 0;
  bottom: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  z-index: 100;
  background: rgba(0, 0, 0, 0.6);
  padding: 0;
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
  max-width: 300px;
  width: 100%;
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
