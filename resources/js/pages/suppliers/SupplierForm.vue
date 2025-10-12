<template>
  <div class="supplier-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoadingCostCenters">
        <FormSkeleton />
      </template>

      <!-- Form Content -->
      <template v-else>
        <div class="grid">
          <!-- Left Side - Form Fields -->
          <div class="col-12 md:col-7">
            <Card class="form-card">
              <template #content>
                <h3 class="mb-4">{{ pageTitle }}</h3>
                
                <!-- Supplier Name -->
                <div class="field mb-4">
                  <Label for="name" :required="true">
                    Nome do Fornecedor
                  </Label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    placeholder="Digite o nome do fornecedor"
                    class="w-full"
                    :class="{ 'p-invalid': errors.name }"
                  />
                  <small v-if="errors.name" class="p-error text-red-600">{{ errors.name }}</small>
                </div>

                <!-- Email -->
                <div class="field mb-4">
                  <Label for="email">
                    Email
                  </Label>
                  <InputText 
                    id="email" 
                    v-model="formData.email" 
                    type="email"
                    placeholder="Digite o email do fornecedor"
                    class="w-full"
                    :class="{ 'p-invalid': errors.email }"
                  />
                  <small v-if="errors.email" class="p-error text-red-600">{{ errors.email }}</small>
                </div>

                <!-- Phone -->
                <div class="field mb-4">
                  <Label for="phone">
                    Telefone
                  </Label>
                  <InputText 
                    id="phone" 
                    v-model="formData.phone" 
                    placeholder="Digite o telefone do fornecedor"
                    class="w-full"
                    :class="{ 'p-invalid': errors.phone }"
                  />
                  <small class="text-color-secondary">Formato: (11) 99999-9999</small>
                  <small v-if="errors.phone" class="p-error">{{ errors.phone }}</small>
                </div>

                <!-- Number -->
                <div class="field mb-4">
                  <Label for="number">
                    Número/Código
                  </Label>
                  <InputText 
                    id="number" 
                    v-model="formData.number" 
                    placeholder="Digite o número ou código do fornecedor"
                    class="w-full"
                    :class="{ 'p-invalid': errors.number }"
                  />
                  <small v-if="errors.number" class="p-error">{{ errors.number }}</small>
                </div>

                <!-- Cost Center -->
                <div class="field mb-4">
                  <Label for="cost_center_id">
                    Centro de Custo
                  </Label>
                  <Dropdown 
                    id="cost_center_id" 
                    v-model="formData.cost_center_id" 
                    :options="costCenters" 
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Selecione o centro de custo"
                    class="w-full"
                    :class="{ 'p-invalid': errors.cost_center_id }"
                  />
                  <small v-if="errors.cost_center_id" class="p-error">{{ errors.cost_center_id }}</small>
                </div>

                <!-- Address Section -->
                <div class="address-section mb-4">
                  <h4 class="mb-3">Endereço</h4>
                  
                  <!-- CEP -->
                  <div class="field mb-3">
                    <Label for="postcode">CEP</Label>
                    <InputText 
                      id="postcode"
                      v-model="formData.postcode" 
                      placeholder="00000-000"
                      class="w-full"
                      :class="{ 'p-invalid': errors.postcode || viaCepErrors.postcode }"
                      @blur="handleCepBlur"
                      @input="handleCepInput"
                      maxlength="9"
                    />
                    <small v-if="errors.postcode" class="p-error">{{ errors.postcode }}</small>
                    <small v-else-if="viaCepErrors.postcode" class="p-error">{{ viaCepErrors.postcode }}</small>
                    <small v-else class="text-color-secondary">Digite o CEP para preenchimento automático</small>
                  </div>

                  <!-- Address -->
                  <div class="field mb-3">
                    <Label for="address">Logradouro</Label>
                    <InputText 
                      id="address"
                      v-model="formData.address" 
                      placeholder="Rua, Avenida, etc."
                      class="w-full"
                      :class="{ 'p-invalid': errors.address }"
                    />
                    <small v-if="errors.address" class="p-error">{{ errors.address }}</small>
                  </div>

                  <!-- Neighborhood -->
                  <div class="field mb-3">
                    <Label for="neighborhood">Bairro</Label>
                    <InputText 
                      id="neighborhood"
                      v-model="formData.neighborhood" 
                      placeholder="Nome do bairro"
                      class="w-full"
                      :class="{ 'p-invalid': errors.neighborhood }"
                    />
                    <small v-if="errors.neighborhood" class="p-error">{{ errors.neighborhood }}</small>
                  </div>

                  <!-- City and State Row -->
                  <div class="grid">
                    <div class="col-8">
                      <div class="field mb-3">
                        <Label for="city">Cidade</Label>
                        <InputText 
                          id="city"
                          v-model="formData.city" 
                          placeholder="Nome da cidade"
                          class="w-full"
                          :class="{ 'p-invalid': errors.city }"
                        />
                        <small v-if="errors.city" class="p-error">{{ errors.city }}</small>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="field mb-3">
                        <Label for="state">Estado</Label>
                        <Dropdown 
                          id="state"
                          v-model="formData.state" 
                          :options="brazilianStates"
                          optionLabel="nome"
                          optionValue="sigla"
                          placeholder="Selecione o estado"
                          class="w-full"
                          :class="{ 'p-invalid': errors.state }"
                          filter
                          showClear
                        />
                        <small v-if="errors.state" class="p-error">{{ errors.state }}</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Observations -->
                <div class="field mb-4">
                  <Label for="observations">
                    Observações
                  </Label>
                  <Textarea 
                    id="observations" 
                    v-model="formData.observations" 
                    placeholder="Digite observações sobre o fornecedor"
                    class="w-full"
                    rows="4"
                    :class="{ 'p-invalid': errors.observations }"
                  />
                  <small v-if="errors.observations" class="p-error">{{ errors.observations }}</small>
                </div>
              </template>
            </Card>
          </div>

          <!-- Divider -->
          <div class="col-12 md:col-1 flex align-items-center justify-content-center">
            <div class="form-divider"></div>
          </div>

          <!-- Right Side - Supplier Info -->
          <div class="col-12 md:col-4">
            <!-- Supplier Status Card -->
            <ToggleCard 
              title="Status do Fornecedor"
              subtitle="Informações sobre o status do fornecedor"
              :toggleable="true"
              class="mb-4"
            >
              <div class="supplier-status-fields">
                <!-- Created At -->
                <div class="status-field mb-3">
                  <div class="status-row">
                    <span class="status-label">Cadastrado em:</span>
                    <span class="status-value">{{ formattedCreatedDate }}</span>
                  </div>
                </div>

                <!-- Updated At -->
                <div class="status-field">
                  <div class="status-row">
                    <span class="status-label">Atualizado em:</span>
                    <span class="status-value">{{ formattedUpdatedDate }}</span>
                  </div>
                </div>

                <!-- Submit Button -->
                <Button 
                  type="submit" 
                  :label="submitButtonLabel" 
                  class="mt-5" 
                  icon="pi pi-check"
                  :disabled="isSubmitting || isLoadingCostCenters || isLoadingSupplier"
                  :loading="isSubmitting"
                />
              </div>
            </ToggleCard>
          </div>
        </div>
      </template>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed, onMounted } from 'vue';
import { useAxios } from '@vueuse/integrations/useAxios';
import { useToast } from 'primevue/usetoast';
import { useRoute, useRouter } from 'vue-router';
import { useViaCep } from '../../composables/useViaCep';
import { brazilianStates } from '../../utils/brazilianStates';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';
import Label from '../../components/Label.vue';
import ToggleCard from '../../components/InfoCard.vue';
import axios from '../../plugins/axios';

const toast = useToast();
const route = useRoute();
const router = useRouter();

// ViaCEP integration
const { 
  formatCep, 
  fetchAddressByCep, 
  errors: viaCepErrors 
} = useViaCep();

// Check if we're in edit mode
const isEditMode = computed(() => !!route.params.id);
const supplierId = computed(() => route.params.id);

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  number: '',
  address: '',
  postcode: '',
  state: '',
  city: '',
  neighborhood: '',
  cost_center_id: null,
  observations: ''
});

const errors = reactive({
  name: '',
  email: '',
  phone: '',
  number: '',
  address: '',
  postcode: '',
  state: '',
  city: '',
  neighborhood: '',
  cost_center_id: '',
  observations: ''
});

// ViaCEP handlers
const handleCepInput = (event) => {
  formData.postcode = formatCep(event.target.value)
}

const handleCepBlur = async () => {
  if (formData.postcode && formData.postcode.replace(/\D/g, '').length === 8) {
    // Map postcode to cep for ViaCEP
    const mappedData = {
      cep: formData.postcode,
      address: formData.address,
      neighborhood: formData.neighborhood,
      city: formData.city,
      state: formData.state
    };
    
    await fetchAddressByCep(formData.postcode, mappedData);
    
    // Map back to formData
    formData.address = mappedData.address;
    formData.neighborhood = mappedData.neighborhood;
    formData.city = mappedData.city;
    formData.state = mappedData.state;
  }
}

// Cost Centers API
const { data: costCentersData, isLoading: isLoadingCostCenters } = useAxios(
  '/api/cost-centers',
  { method: 'GET' },
  axios
);

const costCenters = ref([]);

watch(costCentersData, (newVal) => {
  if (newVal?.data) {
    costCenters.value = newVal.data;
  }
});

// Load supplier data if in edit mode
const isLoadingSupplier = ref(false);

const loadSupplierData = async () => {
  if (isEditMode.value) {
    isLoadingSupplier.value = true;
    try {
      const response = await axios.get(`/api/suppliers/${supplierId.value}`);
      console.log('Supplier response:', response); // Debug log
      
      if (response.data) {
        const supplier = response.data;
        console.log('Supplier data:', supplier); // Debug log
        
        // Dynamically map supplier data to formData fields
        const fieldMappings = {
          name: supplier.name || '',
          email: supplier.email || '',
          phone: supplier.phone || '',
          number: supplier.number || '',
          address: supplier.address || '',
          postcode: supplier.postcode || '',
          state: supplier.state || '',
          city: supplier.city || '',
          neighborhood: supplier.neighborhood || '',
          cost_center_id: supplier.cost_center_id || null,
          observations: supplier.observations || ''
        };

        // Only assign fields that exist in formData to avoid adding unwanted properties
        Object.keys(formData).forEach(key => {
          if (fieldMappings.hasOwnProperty(key)) {
            formData[key] = fieldMappings[key];
          }
        });

        console.log(formData);
      }
    } catch (error) {
      console.error('Error loading supplier:', error);
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Falha ao carregar dados do fornecedor',
        life: 5000
      });
    } finally {
      isLoadingSupplier.value = false;
    }
  }
};

onMounted(() => {
  loadSupplierData();
});

// Form submission
const isSubmitting = ref(false);

const validateForm = () => {
  let valid = true;
  
  // Clear previous errors
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  
  // Validate required fields
  if (!formData.name.trim()) {
    errors.name = 'Nome do fornecedor é obrigatório';
    valid = false;
  }

  // Email is optional - only validate format if provided
  if (formData.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    errors.email = 'Email deve ter um formato válido';
    valid = false;
  }

  // Validate CEP
  if (formData.postcode) {
    const cepNumbers = formData.postcode.replace(/\D/g, '');
    if (cepNumbers.length !== 8) {
      errors.postcode = 'CEP deve ter 8 dígitos';
      valid = false;
    }
  }

  // Validate state (UF)
  if (formData.state && formData.state.length !== 2) {
    errors.state = 'Estado deve ter 2 caracteres (UF)';
    valid = false;
  }

  return valid;
};

const handleSubmit = async () => {
  console.log('Form submitted', formData); // Debug log
  
  if (!validateForm()) {
    console.log('Validation failed', errors); // Debug log
    return;
  }

  isSubmitting.value = true;

  try {
    const submitData = { ...formData };
    
    // Add company_id if needed (assuming it comes from auth context)
    // submitData.company_id = user.company_id;

    console.log('Submitting data:', submitData); // Debug log

    let response;
    if (isEditMode.value) {
      // For edit mode, use PUT method
      response = await axios.put(`/api/suppliers/${supplierId.value}`, submitData);
    } else {
      // For create mode, use POST method
      response = await axios.post('/api/suppliers', submitData);
    }

    console.log('Response:', response); // Debug log

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Fornecedor atualizado com sucesso!' : 'Fornecedor cadastrado com sucesso!',
      life: 3000
    });

    // Redirect to edit page if creating new supplier
    if (!isEditMode.value && response.data?.id) {
      router.push({ name: 'suppliers.edit', params: { id: response.data.id } });
    }

  } catch (error) {
    console.error('Submit error:', error); // Debug log
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = validationErrors[field][0];
        }
      });
    }
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || `Falha ao ${isEditMode.value ? 'atualizar' : 'cadastrar'} fornecedor`,
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Computed properties for dynamic content
const pageTitle = computed(() => isEditMode.value ? 'Editar Fornecedor' : 'Cadastrar Fornecedor');
const submitButtonLabel = computed(() => {
  if (isSubmitting.value) {
    return isEditMode.value ? 'Atualizando...' : 'Cadastrando...';
  }
  return isEditMode.value ? 'Atualizar Fornecedor' : 'Cadastrar Fornecedor';
});

// Supplier status computed properties
const formattedCreatedDate = computed(() => {
  // This would come from the loaded supplier data
  return isEditMode.value ? 'Carregando...' : 'Novo fornecedor';
});

const formattedUpdatedDate = computed(() => {
  // This would come from the loaded supplier data
  return isEditMode.value ? 'Carregando...' : 'Não atualizado';
});
</script>

<style scoped>
.supplier-form-container {
  padding: 1rem;
}

.form-card {
  height: fit-content;
}

.form-divider {
  width: 2px;
  height: 200px;
  background: linear-gradient(to bottom, transparent, #e5e7eb, transparent);
}

.supplier-status-fields .status-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-label {
  font-weight: 500;
  color: #6b7280;
}

.status-value {
  color: #374151;
  font-size: 0.875rem;
}

.address-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}
</style>