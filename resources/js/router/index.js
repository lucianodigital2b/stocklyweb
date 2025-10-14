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
    const publicRoutes = ['login', 'register', 'password.request', 'password.reset', 'verification.verify', 'verification.resend'];
    
    // Define routes that authenticated users should be redirected away from
    const authOnlyRoutes = ['login', 'register'];
    
    // Check if the route requires authentication
    const requiresAuth = !publicRoutes.includes(to.name) && to.name !== 'onboarding';
    
    if (requiresAuth && !isAuthenticated) {
        // Redirect to login if not authenticated
        next({ name: 'login' });
    } else if (isAuthenticated && authOnlyRoutes.includes(to.name)) {
        // Redirect authenticated users away from login/register pages only
        next({ name: 'dashboard' });
    } else if (isAuthenticated && authStore.user && authStore.user.company_id === null && to.name !== 'onboarding') {
        // Redirect authenticated users with null company_id to onboarding
        console.log('User has null company_id, redirecting to onboarding from router guard...');
        next({ name: 'onboarding' });
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
