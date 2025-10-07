
function page (path) {
  return async () => await import(`../pages/${path}.vue`).then(m => m.default || m)
}

export default [
  { path: '/', component: page('dashboard'), name: '/', meta: { layout: 'AppLayout' } },  
  { path: '/dashboard', component: page('dashboard'), name: 'dashboard', meta: { layout: 'AppLayout' } },  
  { path: '/login', component: page('auth/Login'), name: 'login', meta: { layout: 'Basic' } },  
  { path: '/register', component: page('auth/Register'), name: 'register', meta: { layout: 'Basic' } },

  { path: '/password/reset', name: 'password.request', component: page('auth/password/email') },
  { path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset') },
  { path: '/email/verify/:id', name: 'verification.verify', component: page('auth/verification/verify') },
  { path: '/email/resend', name: 'verification.resend', component: page('auth/verification/resend') },

  {
    path: '/products',
    name: 'products.index',
    component: () => import('../pages/products/ProductList.vue'),
    children: [
      {
        path: '/products/create',
        name: 'products.create',
        component: () => import('../pages/products/ProductList.vue'),
        meta: { 
          showDrawer: true, 
          drawerType: 'Product', // Identifier for content
          parentRoute: '/products',      // Route to return to on close
        } 
      },
    ],
    meta: { layout: 'AppLayout' }
  },

  {
    path: '/settings',
    component: page('settings/index'),
    children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: page('settings/profile') },
      { path: 'password', name: 'settings.password', component: page('settings/password') }
    ]
  },

  { path: '/:pathMatch(.*)*', component: () => import('../pages/errors/404.vue'), meta: { layout: 'AppLayout' } }
]
