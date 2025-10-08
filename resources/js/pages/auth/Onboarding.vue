<template>
  <div class="onboarding-container min-h-screen flex">
    <div class="left-section flex-1 flex flex-column align-items-center justify-content-center p-4">
      <div class="illustration-container mb-4">
        <svg width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="50" y="40" width="200" height="120" rx="8" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2"/>
          <circle cx="100" cy="80" r="12" fill="#10b981"/>
          <circle cx="130" cy="80" r="12" fill="#3b82f6"/>
          <circle cx="160" cy="80" r="12" fill="#f59e0b"/>
          <circle cx="190" cy="80" r="12" fill="#ef4444"/>
          <rect x="80" y="110" width="140" height="8" rx="4" fill="#3b82f6"/>
          <rect x="90" y="125" width="120" height="6" rx="3" fill="#94a3b8"/>
          <rect x="100" y="140" width="100" height="6" rx="3" fill="#94a3b8"/>
        </svg>
      </div>
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Configure sua empresa</h2>
        <p class="text-gray-600 max-w-md">
          Vamos configurar as informações da sua empresa para personalizar sua experiência no Stockly.
        </p>
      </div>
      
      <!-- Progress indicator -->
      <div class="progress-container mt-8">
        <div class="flex items-center justify-center space-x-4">
          <div 
            v-for="step in totalSteps" 
            :key="step"
            class="step-indicator"
            :class="{
              'active': step === currentStep,
              'completed': step < currentStep,
              'pending': step > currentStep
            }"
          >
            {{ step }}
          </div>
        </div>
        <div class="text-center mt-2 text-sm text-gray-600">
          Passo {{ currentStep }} de {{ totalSteps }}
        </div>
      </div>
    </div>
    
    <div class="right-section flex-1 flex align-items-center justify-content-center">
      <div class="onboarding-form-container w-full max-w-md">
        <!-- Step 1: Basic Company Information -->
        <div v-if="currentStep === 1" class="step-content">
          <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Informações Básicas</h1>
            <p class="text-gray-600">Dados principais da sua empresa</p>
          </div>
          
          <form @submit.prevent="nextStep" class="space-y-4">
            <div class="field">
              <InputText 
                v-model="form.name" 
                placeholder="Nome da empresa" 
                class="w-full"
                :class="{ 'p-invalid': errors.name }"
                required
              />
              <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.document_number" 
                placeholder="CNPJ" 
                class="w-full"
                :class="{ 'p-invalid': errors.document_number }"
                required
              />
              <small v-if="errors.document_number" class="p-error">{{ errors.document_number }}</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.email" 
                type="email" 
                placeholder="Email da empresa" 
                class="w-full"
                :class="{ 'p-invalid': errors.email }"
                required
              />
              <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.phone" 
                placeholder="Telefone" 
                class="w-full"
                :class="{ 'p-invalid': errors.phone }"
              />
              <small v-if="errors.phone" class="p-error">{{ errors.phone }}</small>
            </div>

            <Button 
              type="submit" 
              label="Próximo" 
              class="w-full p-button-primary"
              :loading="loading"
            />
          </form>
        </div>

        <!-- Step 2: Address Information -->
        <div v-if="currentStep === 2" class="step-content">
          <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Endereço</h1>
            <p class="text-gray-600">Localização da sua empresa</p>
          </div>
          
          <form @submit.prevent="nextStep" class="space-y-4">
            <div class="field">
              <InputText 
                v-model="form.address" 
                placeholder="Endereço completo" 
                class="w-full"
                :class="{ 'p-invalid': errors.address }"
                required
              />
              <small v-if="errors.address" class="p-error">{{ errors.address }}</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.city" 
                placeholder="Cidade" 
                class="w-full"
                :class="{ 'p-invalid': errors.city }"
                required
              />
              <small v-if="errors.city" class="p-error">{{ errors.city }}</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.state" 
                placeholder="Estado" 
                class="w-full"
                :class="{ 'p-invalid': errors.state }"
                required
              />
              <small v-if="errors.state" class="p-error">{{ errors.state }}</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.postal_code" 
                placeholder="CEP" 
                class="w-full"
                :class="{ 'p-invalid': errors.postal_code || viaCepErrors.cep }"
                @blur="handleCepBlur"
                @input="handleCepInput"
                required
              />
              <small v-if="errors.postal_code" class="p-error">{{ errors.postal_code }}</small>
              <small v-else-if="viaCepErrors.cep" class="p-error">{{ viaCepErrors.cep }}</small>
              <small v-else class="text-color-secondary">Digite o CEP para preenchimento automático</small>
            </div>
            
            <div class="field">
              <InputText 
                v-model="form.country" 
                placeholder="País" 
                class="w-full"
                :class="{ 'p-invalid': errors.country }"
                required
              />
              <small v-if="errors.country" class="p-error">{{ errors.country }}</small>
            </div>

            <div class="flex space-x-4">
              <Button 
                type="button" 
                label="Voltar" 
                class="flex-1 p-button-secondary"
                @click="previousStep"
              />
              <Button 
                type="submit" 
                label="Próximo" 
                class="flex-1 p-button-primary"
                :loading="loading"
              />
            </div>
          </form>
        </div>

        <!-- Step 3: Final Configuration -->
        <div v-if="currentStep === 3" class="step-content">
          <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Finalizar</h1>
            <p class="text-gray-600">Confirme as informações e finalize</p>
          </div>
          
          <div class="summary-card p-4 bg-gray-50 rounded-lg mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Resumo das informações:</h3>
            <div class="space-y-2 text-sm">
              <div><strong>Empresa:</strong> {{ form.name }}</div>
              <div><strong>CNPJ:</strong> {{ form.document_number }}</div>
              <div><strong>Email:</strong> {{ form.email }}</div>
              <div v-if="form.phone"><strong>Telefone:</strong> {{ form.phone }}</div>
              <div><strong>Endereço:</strong> {{ form.address }}, {{ form.city }}, {{ form.state }}</div>
              <div><strong>CEP:</strong> {{ form.postal_code }}</div>
              <div><strong>País:</strong> {{ form.country }}</div>
            </div>
          </div>
          
          <form @submit.prevent="submitOnboarding" class="space-y-4">
            <div class="flex space-x-4">
              <Button 
                type="button" 
                label="Voltar" 
                class="flex-1 p-button-secondary"
                @click="previousStep"
              />
              <Button 
                type="submit" 
                label="Finalizar" 
                class="flex-1 p-button-primary"
                :loading="loading"
              />
            </div>
          </form>
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="error-container mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
          <div class="text-sm text-red-600">
            {{ errorMessage }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.onboarding-container {
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

.onboarding-form-container {
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

:deep(.p-button) {
  padding: 0.875rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.progress-container {
  max-width: 300px;
}

.step-indicator {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.step-indicator.pending {
  background-color: #e5e7eb;
  color: #9ca3af;
}

.step-indicator.active {
  background-color: #3b82f6;
  color: white;
}

.step-indicator.completed {
  background-color: #10b981;
  color: white;
}

.summary-card {
  border: 1px solid #e5e7eb;
}

.error-container {
  background-color: #fef2f2;
  border-color: #fecaca;
}

@media (max-width: 768px) {
  .onboarding-container {
    flex-direction: column;
  }
  
  .left-section {
    min-height: 40vh;
  }
  
  .right-section {
    min-height: 60vh;
  }
  
  .progress-container {
    margin-top: 1rem;
  }
}
</style>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/modules/auth'
import { useViaCep } from '../../composables/useViaCep'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const router = useRouter()
const store = useAuthStore()

// ViaCEP integration
const { 
  formatCep, 
  fetchAddressByCep, 
  errors: viaCepErrors, 
  isLoading: isLoadingCep 
} = useViaCep()

// State
const currentStep = ref(1)
const totalSteps = ref(3)
const loading = ref(false)
const errorMessage = ref('')
const errors = ref({})

// Form data
const form = reactive({
  name: '',
  document_number: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'Brasil',
  status: 'active'
})

// ViaCEP handlers
const handleCepInput = (event) => {
  form.postal_code = formatCep(event.target.value)
}

const handleCepBlur = async () => {
  if (form.postal_code && form.postal_code.replace(/\D/g, '').length === 8) {
    await fetchAddressByCep(form.postal_code, form)
  }
}

// Validation
const validateStep = (step) => {
  errors.value = {}
  
  if (step === 1) {
    if (!form.name) errors.value.name = 'Nome da empresa é obrigatório'
    if (!form.document_number) errors.value.document_number = 'CNPJ é obrigatório'
    if (!form.email) errors.value.email = 'Email é obrigatório'
  }
  
  if (step === 2) {
    if (!form.address) errors.value.address = 'Endereço é obrigatório'
    if (!form.city) errors.value.city = 'Cidade é obrigatória'
    if (!form.state) errors.value.state = 'Estado é obrigatório'
    if (!form.postal_code) errors.value.postal_code = 'CEP é obrigatório'
    if (!form.country) errors.value.country = 'País é obrigatório'
  }
  
  return Object.keys(errors.value).length === 0
}

// Navigation
const nextStep = () => {
  if (validateStep(currentStep.value)) {
    if (currentStep.value < totalSteps.value) {
      currentStep.value++
    }
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

// Submit onboarding
const submitOnboarding = async () => {
  if (!validateStep(2)) return // Validate all required fields
  
  loading.value = true
  errorMessage.value = ''
  
  try {
    // Submit company information
    const response = await fetch('/api/companies', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${store.token}`,
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(form)
    })

    if (response.ok) {
      const data = await response.json()
      
      // Update user's company association if needed
      // This might require updating the user model or store
      
      // Redirect to dashboard
      router.push({ name: 'dashboard' })
    } else {
      const errorData = await response.json()
      errorMessage.value = errorData.message || 'Erro ao salvar informações da empresa'
    }
  } catch (error) {
    console.error('Onboarding error:', error)
    errorMessage.value = 'Erro interno. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>