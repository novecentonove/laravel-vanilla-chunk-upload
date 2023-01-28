<template>
  <div>
    <form>
      <input type="file" ref="fileInput" @change="upload"/>
    </form>
    <p>{{ status }}</p>
  </div>
</template>

<script setup>
import { ref } from "@vue/reactivity";

const status = ref('Upload non iniziato')
const fileInput=  ref(null)

const upload = async () => {
      const file = fileInput.value.files[0];
      const chunkSize = 1 * 1024 * 1024; // 1MB
      const totalChunks = Math.ceil(file.size / chunkSize);
      const uuid = uuidv4()

      for (let i = 0; i < totalChunks; i++) {
        const start = i * chunkSize;
        const end = start + chunkSize;
        const chunk = file.slice(start, end);

        const formData = new FormData();
        formData.append('file', chunk);
        formData.append('chunk_index', i);
        formData.append('chunks_total', totalChunks);
        formData.append('uuid', uuid);

        try {
          await axios.post('/upload', formData);
          status.value = `Uploading chunk ${i + 1} of ${totalChunks}`;
        } catch (error) {
          status.value = `Upload failed: ${error.message}`;
        }
      }
      status.value = 'Upload completato';
    }
    
const uuidv4 = () => {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  );
}

</script>

<style>

</style>