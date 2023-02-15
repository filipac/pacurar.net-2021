import {createApp} from "vue";
import App from "./App.vue";
import {createPinia} from "pinia";
let allDivs = document.querySelectorAll('[data-replace-vue-app]');

    const pinia = createPinia()
allDivs.forEach((div) => {
    const app = createApp(App)
    app.use(pinia)
    app.mount(div);
})
