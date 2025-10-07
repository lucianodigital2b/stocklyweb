import { createRouter, createWebHistory, } from 'vue-router';

import { useAuthStore } from '../store/modules/auth'
import routes from './routes';


const router = createRouter({
    history: createWebHistory(),
    routes,
});


router.beforeEach(loadLayoutMiddleware)

async function loadLayoutMiddleware(route, from) {

    
    try {
        let layout = route.meta.layout;
        
        // let layoutComponent = await import(`/resources/js/layouts/${layout}.vue`)
        let layoutComponent = await import(`/resources/js/layout/AppLayout.vue`)

        route.meta.layoutComponent = layoutComponent.default
    } catch (e) {
        console.error('Error occurred in processing of layouts: ', e)
        let layout = 'basic'
        let layoutComponent = await import(`../layouts/${layout}.vue`)
        
        route.meta.layoutComponent = layoutComponent.default
    }



}


// router.afterEach(afterEach)

export default router;
