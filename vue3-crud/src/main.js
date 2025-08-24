//import createApp from Vue
import { createApp } from 'vue';

//import component App
import App from './App.vue';

//import config router
import router from './router';

import '@fortawesome/fontawesome-free/css/all.css';

import { createPinia } from 'pinia';
import { useUserStore } from "./stores/user";

//create App Vue
const app = createApp(App);

app.use(createPinia());

const userStore = useUserStore();
userStore.loadFromLocalStorage(); 

//gunakan "router" di Vue dengan plugin "use"
app.use(router);

app.mount('#app');