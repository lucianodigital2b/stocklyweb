<template>
  <div class="login-container min-h-screen flex">
    <div class="left-section flex-1 flex flex-column align-items-center justify-content-center p-4">
      <div class="illustration-container mb-4">
        <svg width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="50" y="40" width="200" height="120" rx="8" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2"/>
          <circle cx="150" cy="80" r="20" fill="#3b82f6"/>
          <rect x="120" y="110" width="60" height="8" rx="4" fill="#3b82f6"/>
          <rect x="130" y="125" width="40" height="6" rx="3" fill="#94a3b8"/>
          <circle cx="80" cy="60" r="3" fill="#10b981"/>
          <circle cx="90" cy="60" r="3" fill="#f59e0b"/>
          <circle cx="100" cy="60" r="3" fill="#ef4444"/>
        </svg>
      </div>
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Rápido, Eficiente e Produtivo</h2>
        <p class="text-gray-600 max-w-md">
          O Stockly é a solução completa para gestão de estoque que sua empresa precisa. 
          Controle inventário, monitore movimentações e otimize seus processos com nossa plataforma SaaS intuitiva e poderosa.
        </p>
      </div>
    </div>
    
    <div class="right-section flex-1 flex align-items-center justify-content-center">
      <div class="login-form-container w-full max-w-md">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800 mb-2">Entrar</h1>
          <p class="text-gray-600">Acesse sua conta Stockly</p>
        </div>
        
        <form @submit.prevent="login" class="space-y-4">
          <div class="field">
            <InputText 
              v-model="form.email" 
              type="email" 
              placeholder="seu@email.com" 
              class="w-full"
              :class="{ 'p-invalid': form.errors.has('email') }"
              required
            />
            <has-error :form="form" field="email" />
          </div>
          
          <div class="field">
            <Password 
              v-model="form.password" 
              placeholder="Senha" 
              :feedback="false"
              toggleMask
              class="w-full"
              :class="{ 'p-invalid': form.errors.has('password') }"
              required
            />
            <has-error :form="form" field="password" />
          </div>
          
          <div class="flex justify-content-end mb-4">
            <router-link to="/password/reset" class="text-sm text-blue-600 hover:text-blue-800">
              Esqueceu a senha?
            </router-link>
          </div>

          
          <div v-if="form.errors.any() || errorMessage" class="error-container mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <div class=" text-sm">
              <!-- Single error message -->
              <div v-if="errorMessage" class="mb-1 text-error">
                {{ errorMessage }}
              </div>
              
            </div>
          </div>

          <Button 
            type="submit" 
            label="Entrar" 
            class="w-full p-button-primary"
            :loading="form.busy"
          />
          
          <div class="text-center mt-4">
            <span class="text-sm text-gray-600">Ainda não tem conta? </span>
            <router-link to="/register" class="text-sm text-blue-600 hover:text-blue-800">
              Cadastre-se
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>


<style scoped>
.login-container {
  font-family: 'Inter', sans-serif;
}

.left-section {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.illustration-container svg {
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
}

.right-section {
  background: #ffffff;
}

.login-form-container {
  padding: 2rem;
}

.field {
  margin-bottom: 1.5rem;
}

.field :deep(.p-inputtext) {
  padding: 0.875rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  font-size: 0.875rem;
}

.field :deep(.p-inputtext:focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.field :deep(.p-password) {
  width: 100%;
}

.field :deep(.p-password .p-inputtext) {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  font-size: 0.875rem;
}

:deep(.p-button) {
  padding: 0.875rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.error-container {
  background-color: #fef2f2;
  border-color: #fecaca;
}

.error-container .text-error {
  color: #dc2626;
}

@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
  }
  
  .left-section {
    min-height: 40vh;
  }
  
  .right-section {
    min-height: 60vh;
  }
}
</style>



<script setup>
import { reactive, ref } from 'vue'
import Form from 'vform'
import Cookies from 'js-cookie'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/modules/auth'
import axios from '../../plugins/axios'
import { AlertError, HasError } from 'vform/components/bootstrap5'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'

const router = useRouter()
const store = useAuthStore()

// Error message for single error responses
const errorMessage = ref('')

// Form
const form = reactive(new Form({
  email: '',
  password: '',
  remember: false
}))

// Login
async function login () {
  try {
    // Clear previous error message
    errorMessage.value = ''
    
    // Submit the form.
    const { data } = await form.post('/api/login')

    // Save the token and user.
    store.login({ user: data.user, token: data.token })

    // Redirect home.
    router.push({ name: 'dashboard' })
  } catch (error) {
    // Handle single error object responses
    console.log('chegou', error)

    if (error.response && error.response.data && error.response.data.error) {
      errorMessage.value = error.response.data.error
    }
  }
}
</script>
