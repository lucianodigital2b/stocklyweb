import { createApp } from 'vue';
// import App from './components/App.vue';
import App from './App.vue';
import router from './router';
import store from './store';
import nativeComponents from './components'
import "bootstrap/dist/js/bootstrap.js";
// import './echo',
import Aura from '@primevue/themes/aura';
import PrimeVue from 'primevue/config';


import '../assets/tailwind.css';
import '../assets/styles.scss';
import 'primeflex/primeflex.css';
import Skeleton from 'primevue/skeleton';
import ToastService from 'primevue/toastservice';

const app = createApp(App)
.use(router)
.use(store)
.use(Skeleton)
.use(ToastService)
.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.app-dark'
        }
    }
})

// app.config.globalProperties.$echo = Echo;

nativeComponents.forEach(component => {
    app.component(component.name, component);
});

app.mount('#app');