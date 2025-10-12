import { createRouter, createWebHistory, } from 'vue-router';

import { useAuthStore } from '../store/modules/auth'
import routes from './routes';


const router = createRouter({
    history: createWebHistory(),
    routes,
});


router.beforeEach(async (to, from, next) => {
    // Load layout middleware
    await loadLayoutMiddleware(to, from);
    
    // Authentication guard
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;
    
    // Define routes that don't require authentication
    const publicRoutes = ['login', 'register', 'onboarding', 'password.request', 'password.reset', 'verification.verify', 'verification.resend'];
    
    // Check if the route requires authentication
    const requiresAuth = !publicRoutes.includes(to.name);
    
    if (requiresAuth && !isAuthenticated) {
        // Redirect to login if not authenticated
        next({ name: 'login' });
    } else if (isAuthenticated && publicRoutes.includes(to.name)) {
        // Redirect authenticated users away from auth pages
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

async function loadLayoutMiddleware(route, from) {
    let layout = route.meta.layout;
    let layoutComponent = await import(`../layout/${layout}.vue`)
    route.meta.layoutComponent = layoutComponent.default
}


// router.afterEach(afterEach)

export default router;
