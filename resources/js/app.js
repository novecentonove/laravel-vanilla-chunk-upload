import './bootstrap';
import { createApp } from 'vue/dist/vue.esm-bundler';

import Upload from './components/Upload.vue'

const app = createApp()

app.component('Upload', Upload)


//Mount the app
if (document.querySelector('#app')) {
    app.mount('#app')
}
