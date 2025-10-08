<template>
  <div class="register-container min-h-screen flex">
    <div class="left-section flex-1 flex flex-column align-items-center justify-content-center p-4">
      <div class="illustration-container mb-4">
        <svg width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="50" y="40" width="200" height="120" rx="8" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2"/>
          <circle cx="120" cy="70" r="15" fill="#10b981"/>
          <circle cx="180" cy="70" r="15" fill="#3b82f6"/>
          <rect x="80" y="100" width="140" height="8" rx="4" fill="#3b82f6"/>
          <rect x="100" y="115" width="100" height="6" rx="3" fill="#94a3b8"/>
          <rect x="110" y="130" width="80" height="6" rx="3" fill="#94a3b8"/>
          <circle cx="80" cy="60" r="3" fill="#10b981"/>
          <circle cx="90" cy="60" r="3" fill="#f59e0b"/>
          <circle cx="100" cy="60" r="3" fill="#ef4444"/>
        </svg>
      </div>
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Comece sua jornada</h2>
        <p class="text-gray-600 max-w-md">
          Junte-se ao Stockly e transforme a gestão do seu estoque. 
          Crie sua conta e descubra como nossa plataforma pode otimizar seus processos de negócio.
        </p>
      </div>
    </div>
    
    <div class="right-section flex-1 flex align-items-center justify-content-center">
      <div class="register-form-container w-full max-w-md">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800 mb-2">Criar Conta</h1>
          <p class="text-gray-600">Preencha os dados para começar</p>
        </div>
        
        <form @submit.prevent="register" class="space-y-4">
          <div class="field">
            <InputText 
              v-model="form.name" 
              placeholder="Nome completo" 
              class="w-full"
              :class="{ 'p-invalid': form.errors.has('name') }"
              required
            />
            <has-error :form="form" field="name" />
          </div>
          
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
              :feedback="true"
              toggleMask
              class="w-full"
              :class="{ 'p-invalid': form.errors.has('password') }"
              required
            />
            <has-error :form="form" field="password" />
          </div>
          
          <div class="field">
            <Password 
              v-model="form.password_confirmation" 
              placeholder="Confirmar senha" 
              :feedback="false"
              toggleMask
              class="w-full"
              :class="{ 'p-invalid': form.errors.has('password_confirmation') }"
              required
            />
            <has-error :form="form" field="password_confirmation" />
          </div>

          <div v-if="form.errors.any() || errorMessage" class="error-container mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <div class="text-sm">
              <!-- Single error message -->
              <div v-if="errorMessage" class="mb-1 text-error">
                {{ errorMessage }}
              </div>
              <!-- Form validation errors -->
              <AlertError :form="form" />
            </div>
          </div>

          <Button 
            type="submit" 
            label="Criar Conta" 
            class="w-full p-button-primary"
            :loading="form.busy"
          />
          
          <div class="text-center mt-4">
            <span class="text-sm text-gray-600">Já tem uma conta? </span>
            <router-link to="/login" class="text-sm text-blue-600 hover:text-blue-800">
              Entrar
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>


<style scoped>
.register-container {
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

.register-form-container {
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
  .register-container {
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
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/modules/auth'
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
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
}))

// Register
const register = async () => {
  try {
    // Clear previous error message
    errorMessage.value = ''
    
    // Submit the registration form
    const { data } = await form.post('/api/register')
    
    // Auto-login after registration
    const loginForm = new Form({
      email: form.email,
      password: form.password
    })
    
    const loginData = await loginForm.post('/api/login')

    // Save the token and user
    store.login({
      token: loginData.data.token,
      user: loginData.data.user,
    })

    // Redirect to onboarding instead of dashboard
    router.push({ name: 'onboarding' })
  } catch (error) {
    console.log('Registration error:', error)

    if (error.response && error.response.data && error.response.data.error) {
      errorMessage.value = error.response.data.error
    }
  }
}
</script>
