<template>
  <div class="main">
    <div
      class="dropzone-container"
      @dragover="dragover"
      @dragleave="dragleave"
      @drop="drop"
    >
      <input
        type="file"
        name="file"
        id="fileInput"
        class="hidden-input"
        ref="fileInput"
        @change="onChange" 
        accept=".pdf,.jpg,.jpeg,.png"
      />

      <label for="fileInput" class="file-label">
        <div v-if="isDragging">Release to drop files here.</div>
        <div v-else>Drop files here or <u>click here</u> to upload.</div>
      </label>

      <div class="preview-container mt-4" v-if="files.length">
        <div v-for="file in files" :key="file.name" class="preview-card">
          <div>
            <img class="preview-img" :src="generateThumbnail(file)" />
            <p :title="file.name">
              {{ makeName(file.name) }}
            </p>
          </div>
          <div>
            <button
              class="ml-2"
              type="button"
              @click="remove(files.indexOf(file))"
              title="Remove file"
            >
              <b>&times;</b>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref } from "@vue/reactivity";

const status     = ref('Ready')
const fileInput  = ref(null)
const isDragging = ref(false)
const files      = ref([])

const onChange = () => {
    files.value = [fileInput.value.files[0]];
}

const generateThumbnail = (file) =>{

    let fileSrc = URL.createObjectURL(file);

    setTimeout(() => {
        URL.revokeObjectURL(fileSrc);
    }, 1000);

    return fileSrc;
}

const makeName = (name) => {
    return (
    name.split(".")[0].substring(0, 3) +
    "..." +
    name.split(".")[name.split(".").length - 1]
    );
}

const remove = (i) => {
    files.value.splice(i, 1);
}

const dragover = (e) => {
    e.preventDefault();
   isDragging.value = true;
}

const dragleave = () => {
    isDragging.value = false;
}

const drop = (e) => {
    e.preventDefault();
    fileInput.value.files = e.dataTransfer.files;
    onChange();
    isDragging.value = false;
}





const upload = async () => {
      const file = fileInput.value.files[0]
      const chunkSize = 1.5 * 1024 * 1024 // 1MB
      const totalChunks = Math.ceil(file.size / chunkSize)
      const uuid = uuidv4()

      for (let i = 0; i < totalChunks; i++) {
        const start = i * chunkSize;
        const end = start + chunkSize;
        const chunk = file.slice(start, end);
        const percent = (i + 1) / totalChunks * 100;

        const formData = new FormData();
        formData.append('uuid', uuid);
        formData.append('file', chunk);
        formData.append('file_name', file.name);
        formData.append('chunk_index', i);
        formData.append('chunks_total', totalChunks);
        formData.append('percent', percent);
        
        try {
          await axios.post('/upload', formData);
          status.value = `Uploading chunk ${i + 1} of ${totalChunks}`;
          
        } catch (error) {
          status.value = `Upload failed: ${error.message}`;
        }
      }
      status.value = 'Upload completed';
    }
    
const uuidv4 = () => {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  );
}

</script>

<style>
.main {
  display: flex;
  flex-grow: 1;
  align-items: center;
  height: 100vh;
  justify-content: center;
  text-align: center;
}
.dropzone-container {
  padding: 4rem;
  background: #f7fafc;
  border: 1px solid #e2e8f0;
}
.hidden-input {
  opacity: 0;
  overflow: hidden;
  position: absolute;
  width: 1px;
  height: 1px;
}
.file-label {
  font-size: 20px;
  display: block;
  cursor: pointer;
}
.preview-container {
  display: flex;
  margin-top: 2rem;
}
.preview-card {
  display: flex;
  border: 1px solid #a2a2a2;
  padding: 5px;
  margin-left: 5px;
}
.preview-img {
  width: 50px;
  height: 50px;
  border-radius: 5px;
  border: 1px solid #a2a2a2;
  background-color: #a2a2a2;
}
</style>