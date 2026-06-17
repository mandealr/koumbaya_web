import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import securityService from './services/security';

const app = createApp(App);
const pinia = createPinia();

// Exposer le router globalement pour l'intercepteur API
window.router = router;

// Directive globale pour rendre du HTML SANITISÉ (anti-XSS).
// À utiliser à la place de v-html sur tout contenu provenant de l'API.
const setSafeHtml = (el, binding) => {
    el.innerHTML = securityService.sanitizeHtml(binding.value ?? '');
};
app.directive('safe-html', {
    mounted: setSafeHtml,
    updated: setSafeHtml,
});

app.use(pinia);
app.use(router);

app.mount('#app');
