import { createRouter, createWebHistory, } from 'vue-router';

import { useAuthStore } from '../store/modules/auth'
import routes from './routes';


const router = createRouter({
    history: createWebHistory(),
    routes,
});


router.beforeEach(loadLayoutMiddleware)

async function loadLayoutMiddleware(route, from) {
    let layout = route.meta.layout;
    let layoutComponent = await import(`../layout/${layout}.vue`)
    route.meta.layoutComponent = layoutComponent.default
}


// router.afterEach(afterEach)

export default router;
