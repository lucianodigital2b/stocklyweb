
function page (path) {
  return async () => await import(`../pages/${path}.vue`).then(m => m.default || m)
}

export default [
  {
    path: '/categories',
    name: 'categories.index',
    component: () => import('../pages/categories/CategoryList.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/categories/create',
    name: 'categories.create',
    component: () => import('../pages/categories/CategoryForm.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/categories/:id/edit',
    name: 'categories.edit',
    component: () => import('../pages/categories/CategoryForm.vue'),
    meta: { layout: 'AppLayout' }
  },

  { path: '/', component: page('dashboard'), name: '/', meta: { layout: 'AppLayout' } },  
  { path: '/dashboard', component: page('dashboard'), name: 'dashboard', meta: { layout: 'AppLayout' } },  
  { path: '/login', component: () => import('../pages/auth/Login.vue'), name: 'login', meta: { layout: 'Basic' } },  
  { path: '/register', component: () => import('../pages/auth/Register.vue'), name: 'register', meta: { layout: 'Basic' } },
  { path: '/onboarding', component: () => import('../pages/auth/Onboarding.vue'), name: 'onboarding', meta: { layout: 'Basic' } },

  { path: '/password/reset', name: 'password.request', component: () => import('../pages/auth/password/email.vue'), meta: { layout: 'Basic' } },
  { path: '/password/reset/:token', name: 'password.reset', component: () => import('../pages/auth/password/reset.vue'), meta: { layout: 'Basic' } },
  { path: '/email/verify/:id', name: 'verification.verify', component: () => import('../pages/auth/verification/verify.vue'), meta: { layout: 'Basic' } },
  { path: '/email/resend', name: 'verification.resend', component: () => import('../pages/auth/verification/resend.vue'), meta: { layout: 'Basic' } },

  {
    path: '/products',
    name: 'products.index',
    component: () => import('../pages/products/ProductList.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/products/create',
    name: 'products.create',
    component: () => import('../pages/products/ProductForm.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/products/:id/edit',
    name: 'products.edit',
    component: () => import('../pages/products/ProductForm.vue'),
    meta: { layout: 'AppLayout' }
  },

  {
    path: '/customers',
    name: 'customers.index',
    component: () => import('../pages/customers/CustomerList.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/customers/create',
    name: 'customers.create',
    component: () => import('../pages/customers/CustomerForm.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/customers/:id/edit',
    name: 'customers.edit',
    component: () => import('../pages/customers/CustomerForm.vue'),
    meta: { layout: 'AppLayout' }
  },

  {
    path: '/suppliers',
    name: 'suppliers.index',
    component: () => import('../pages/suppliers/SupplierList.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/suppliers/create',
    name: 'suppliers.create',
    component: () => import('../pages/suppliers/SupplierForm.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/suppliers/:id/edit',
    name: 'suppliers.edit',
    component: () => import('../pages/suppliers/SupplierForm.vue'),
    meta: { layout: 'AppLayout' }
  },

  {
    path: '/cost-centers',
    name: 'cost-centers.index',
    component: () => import('../pages/cost-centers/CostCenterList.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/cost-centers/create',
    name: 'cost-centers.create',
    component: () => import('../pages/cost-centers/CostCenterForm.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/cost-centers/:id/edit',
    name: 'cost-centers.edit',
    component: () => import('../pages/cost-centers/CostCenterForm.vue'),
    meta: { layout: 'AppLayout' }
  },

  {
    path: '/entries',
    name: 'entries.index',
    component: () => import('../pages/entries/EntryList.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/entries/create',
    name: 'entries.create',
    component: () => import('../pages/entries/EntryForm.vue'),
    meta: { layout: 'AppLayout' }
  },
  {
    path: '/entries/:id/edit',
    name: 'entries.edit',
    component: () => import('../pages/entries/EntryForm.vue'),
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

  // Profile Settings Route
  {
    path: '/profile',
    name: 'profile.settings',
    component: () => import('../pages/profile/ProfileSettings.vue'),
    meta: { layout: 'AppLayout' }
  },

  { path: '/:pathMatch(.*)*', component: () => import('../pages/errors/404.vue'), meta: { layout: 'AppLayout' } }
]
