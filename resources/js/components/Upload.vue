<template>
  <div>
    <form>
      <input type="file" ref="fileInput" @change="upload" accept="image/png, image/jpeg">
    </form>
    <p>{{ status }}</p>
  </div>
</template>

<script setup>
import { ref } from "@vue/reactivity";

const status = ref('Upload non iniziato')
const fileInput =  ref(null)

const upload = async () => {
    const file = fileInput.value.files[0]
    const chunkSize = 1.5 * 1024 * 1024 // 1MB
    const totalChunks = Math.ceil(file.size / chunkSize)
    const uuid = uuidv4()

    // frontend validate
    const extension_accepted = ['png', 'jpeg'];
    const extension = file.name.split('.').pop();
    
    if(!extension_accepted.includes(extension)){
        status.value = 'File not valid'
        return;
    }

    const size = (Math.round(+file.size/1024)/1000).toFixed(2)
    if(size> 30){
        status.value = 'File too big'
        return;
    }

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

</style>