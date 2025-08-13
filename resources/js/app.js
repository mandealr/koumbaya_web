import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

const app = createApp(App);
const pinia = createPinia();

// Exposer le router globalement pour l'intercepteur API
window.router = router;

app.use(pinia);
app.use(router);

app.mount('#app');
