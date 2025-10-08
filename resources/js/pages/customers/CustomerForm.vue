<template>
  <div class="customer-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoadingStores">
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
                
                <!-- Customer Name -->
                <div class="field mb-4">
                  <Label for="name" :required="true">
                    Nome do Cliente
                  </Label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    placeholder="Digite o nome do cliente"
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
                    placeholder="Digite o email do cliente"
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
                    placeholder="Digite o telefone do cliente"
                    class="w-full"
                    :class="{ 'p-invalid': errors.phone }"
                  />
                  <small class="text-color-secondary">Formato: (11) 99999-9999</small>
                  <small v-if="errors.phone" class="p-error">{{ errors.phone }}</small>
                </div>

                <!-- Document Number (CPF/CNPJ) -->
                <div class="field mb-4">
                  <Label for="document_number">
                    CPF/CNPJ
                  </Label>
                  <InputText 
                    id="document_number" 
                    v-model="formData.document_number" 
                    placeholder="Digite o CPF ou CNPJ"
                    class="w-full"
                    :class="{ 'p-invalid': errors.document_number }"
                    maxlength="18"
                  />
                  <small class="text-color-secondary">CPF: 000.000.000-00 | CNPJ: 00.000.000/0000-00</small>
                  <small v-if="errors.document_number" class="p-error">{{ errors.document_number }}</small>
                </div>

          

                <!-- Customer Notes Section -->
                <div class="notes-section mb-4">
                  <h4 class="mb-3">Informações Adicionais</h4>
                  
                  <!-- Address Section -->
                  <div class="address-section mb-4">
                    <h5 class="mb-3">Endereço</h5>
                    
                    <!-- CEP -->
                    <div class="field mb-3">
                      <Label for="cep">CEP</Label>
                      <InputText 
                        id="cep"
                        v-model="formData.cep" 
                        placeholder="00000-000"
                        class="w-full"
                        :class="{ 'p-invalid': errors.cep || viaCepErrors.cep }"
                        @blur="handleCepBlur"
                        @input="handleCepInput"
                        maxlength="9"
                      />
                      <small v-if="errors.cep" class="p-error">{{ errors.cep }}</small>
                      <small v-else-if="viaCepErrors.cep" class="p-error">{{ viaCepErrors.cep }}</small>
                      <small v-else class="text-color-secondary">Digite o CEP para preenchimento automático</small>
                    </div>

                    <!-- Address and Number Row -->
                    <div class="grid">
                      <div class="col-8">
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
                      </div>
                      <div class="col-4">
                        <div class="field mb-3">
                          <Label for="number">Número</Label>
                          <InputText 
                            id="number"
                            v-model="formData.number" 
                            placeholder="123"
                            class="w-full"
                            :class="{ 'p-invalid': errors.number }"
                          />
                          <small v-if="errors.number" class="p-error">{{ errors.number }}</small>
                        </div>
                      </div>
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
                          <InputText 
                            id="state"
                            v-model="formData.state" 
                            placeholder="UF"
                            class="w-full"
                            :class="{ 'p-invalid': errors.state }"
                            maxlength="2"
                          />
                          <small v-if="errors.state" class="p-error">{{ errors.state }}</small>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Birth Date -->
                  <div class="field mb-4">
                    <Label for="birth_date">Data de Nascimento</Label>
                    <Calendar 
                      id="birth_date"
                      v-model="formData.birth_date" 
                      placeholder="Selecione a data de nascimento"
                      class="w-full"
                      dateFormat="dd/mm/yy"
                      :maxDate="new Date()"
                    />
                    <small class="text-gray-500">Data de nascimento do cliente.</small>
                  </div>

                  <!-- Customer Type -->
                  <div class="field mb-4">
                    <Label for="customer_type">Tipo de Cliente</Label>
                    <Dropdown 
                      id="customer_type" 
                      v-model="formData.customer_type" 
                      :options="customerTypeOptions" 
                      optionLabel="label"
                      optionValue="value"
                      placeholder="Selecione o tipo"
                      class="w-full"
                    />
                  </div>

                  <!-- Newsletter Subscription -->
                  <div class="field mb-4">
                    <div class="flex align-items-center gap-3">
                      <Checkbox 
                        id="newsletter_subscription"
                        v-model="formData.newsletter_subscription" 
                        :binary="true"
                      />
                      <div>
                        <Label for="newsletter_subscription">Receber Newsletter</Label>
                        <small class="text-gray-500">Cliente deseja receber emails promocionais.</small>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <!-- Divider -->
          <div class="col-12 md:col-1 flex align-items-center justify-content-center">
            <div class="form-divider"></div>
          </div>

          <!-- Right Side - Customer Info & Avatar -->
          <div class="col-12 md:col-4">
            <!-- Customer Status Card -->
            <ToggleCard 
              title="Status do Cliente"
              subtitle="Informações sobre o status do cliente"
              :toggleable="true"
              class="mb-4"
            >
              <div class="customer-status-fields">
                <!-- Status -->
                <div class="status-field mb-3">
                  <div class="status-row">
                    <span class="status-label">Status:</span>
                    <SelectButton 
                      v-model="formData.status" 
                      :options="statusOptions" 
                      optionLabel="label" 
                      optionValue="value"
                      class="status-select-button"
                    />
                  </div>
                </div>

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
                  :disabled="isSubmitting || isLoadingStores || isLoadingCustomer"
                  :loading="isSubmitting"
                />
              </div>
            </ToggleCard>

            <!-- Customer Avatar Section -->
            <div class="customer-avatar-section">
              <h4 class="mb-3">Avatar do Cliente</h4>
              
              <!-- Avatar Upload Area -->
              <div class="avatar-upload-container">
                <div 
                  v-if="!customerAvatar" 
                  class="upload-placeholder"
                  @click="triggerFileInput"
                  @dragover.prevent
                  @drop.prevent="handleDrop"
                >
                  <i class="pi pi-user text-4xl text-gray-400 mb-3"></i>
                  <p class="text-gray-600 mb-2">Clique para fazer upload ou arraste uma imagem</p>
                  <small class="text-gray-500">PNG, JPG até 5MB</small>
                </div>
                
                <div v-else class="avatar-preview-container">
                  <img :src="customerAvatarPreview" alt="Avatar Preview" class="customer-avatar-preview" />
                  <div class="avatar-overlay">
                    <Button 
                      icon="pi pi-pencil" 
                      class="p-button-rounded p-button-secondary p-button-sm mr-2"
                      @click="triggerFileInput"
                    />
                    <Button 
                      icon="pi pi-trash" 
                      class="p-button-rounded p-button-danger p-button-sm"
                      @click="removeCustomerAvatar"
                    />
                  </div>
                </div>
                
                <input 
                  ref="fileInput"
                  type="file" 
                  accept="image/*" 
                  @change="handleFileSelect"
                  style="display: none"
                />
              </div>
            </div>
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
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';
import SelectButton from 'primevue/selectbutton';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
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
const customerId = computed(() => route.params.id);

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  document_number: '',
  cep: '',
  address: '',
  number: '',
  neighborhood: '',
  city: '',
  state: '',
  birth_date: null,
  customer_type: 'regular',
  newsletter_subscription: false,
  status: 'active'
});

const errors = reactive({
  name: '',
  email: '',
  phone: '',
  document_number: '',
  cep: '',
  address: '',
  number: '',
  neighborhood: '',
  city: '',
  state: '',
});

// Customer Avatar Upload
const customerAvatar = ref(null);
const customerAvatarPreview = ref('');
const fileInput = ref(null);

// Status options
const statusOptions = ref([
  { label: 'Ativo', value: 'active' },
  { label: 'Inativo', value: 'inactive' },
  { label: 'Bloqueado', value: 'blocked' }
]);

// Customer type options
const customerTypeOptions = ref([
  { label: 'Regular', value: 'regular' },
  { label: 'VIP', value: 'vip' },
  { label: 'Corporativo', value: 'corporate' }
]);

// ViaCEP API integration
// ViaCEP handlers
const handleCepInput = (event) => {
  formData.cep = formatCep(event.target.value)
}

const handleCepBlur = async () => {
  if (formData.cep && formData.cep.replace(/\D/g, '').length === 8) {
    await fetchAddressByCep(formData.cep, formData)
  }
}

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    handleImageFile(file);
  }
};

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    handleImageFile(file);
  }
};

const handleImageFile = (file) => {
  if (file.size > 5 * 1024 * 1024) { // 5MB limit
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Arquivo muito grande. Máximo 5MB.',
      life: 3000
    });
    return;
  }

  customerAvatar.value = file;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    customerAvatarPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeCustomerAvatar = () => {
  customerAvatar.value = null;
  customerAvatarPreview.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Stores API
const { data: storesData, isLoading: isLoadingStores } = useAxios(
  '/api/stores',
  { method: 'GET' },
  axios
);

const stores = ref([]);

watch(storesData, (newVal) => {
  if (newVal?.data) {
    stores.value = newVal.data;
  }
});

// Load customer data if in edit mode
const isLoadingCustomer = ref(false);

const loadCustomerData = async () => {
  if (isEditMode.value) {
    isLoadingCustomer.value = true;
    try {
      const response = await axios.get(`/api/customers/${customerId.value}`);
      console.log('Customer response:', response); // Debug log
      
      if (response.data) {
        const customer = response.data;
        console.log('Customer data:', customer); // Debug log
        
        // Dynamically map customer data to formData fields
        const fieldMappings = {
          name: customer.name || '',
          email: customer.email || '',
          phone: customer.phone || '',
          document_number: customer.document_number || '',
          cep: customer.cep || '',
          address: customer.address || '',
          number: customer.number || '',
          neighborhood: customer.neighborhood || '',
          city: customer.city || '',
          state: customer.state || '',
          birth_date: customer.birth_date ? new Date(customer.birth_date) : null,
          customer_type: customer.customer_type || 'regular',
          newsletter_subscription: customer.newsletter_subscription === 1,
          status: customer.status || 'active'
        };

        // Only assign fields that exist in formData to avoid adding unwanted properties
        Object.keys(formData).forEach(key => {
          if (fieldMappings.hasOwnProperty(key)) {
            formData[key] = fieldMappings[key];
          }
        });
        
        // Set avatar preview if exists
        if (customer.avatar) {
          customerAvatarPreview.value = customer.avatar;
        }

        console.log(formData);
        
      }
    } catch (error) {
      console.error('Error loading customer:', error);
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Falha ao carregar dados do cliente',
        life: 5000
      });
    } finally {
      isLoadingCustomer.value = false;
    }
  }
};

onMounted(() => {
  loadCustomerData();
});

// Form submission
const isSubmitting = ref(false);

const validateForm = () => {
  let valid = true;
  
  // Clear previous errors
  errors.name = '';
  errors.email = '';
  errors.phone = '';
  errors.document_number = '';
  errors.cep = '';
  errors.address = '';
  errors.number = '';
  errors.neighborhood = '';
  errors.city = '';
  errors.state = '';
  
  // Validate required fields
  if (!formData.name.trim()) {
    errors.name = 'Nome do cliente é obrigatório';
    valid = false;
  }

  // Email is now optional - only validate format if provided
  if (formData.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    errors.email = 'Email deve ter um formato válido';
    valid = false;
  }

  // Validate document_number (CPF or CNPJ)
  if (formData.document_number) {
    const docNumbers = formData.document_number.replace(/\D/g, '');
    if (docNumbers.length !== 11 && docNumbers.length !== 14) {
      errors.document_number = 'CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos';
      valid = false;
    }
  }

  // Validate CEP
  if (formData.cep) {
    const cepNumbers = formData.cep.replace(/\D/g, '');
    if (cepNumbers.length !== 8) {
      errors.cep = 'CEP deve ter 8 dígitos';
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
    const submitData = new FormData();
    
    // Define fields that need special handling or should be excluded
    const excludeFields = []; 
    
    // Automatically convert formData to FormData using Object.entries
    Object.entries(formData).forEach(([key, value]) => {
      if (excludeFields.includes(key)) return;
      
      // Handle different data types
      if (value === null || value === undefined) {
        submitData.append(key, '');
      } else if (typeof value === 'boolean') {
        submitData.append(key, value ? '1' : '0');
      } else if (typeof value === 'number') {
        submitData.append(key, value.toString());
      } else if (value instanceof Date) {
        submitData.append(key, value.toISOString().split('T')[0]); // Format as YYYY-MM-DD
      } else if (Array.isArray(value)) {
        // Skip arrays here, handle them separately if needed
        return;
      } else {
        submitData.append(key, value);
      }
    });
    
    if (customerAvatar.value) {
      submitData.append('avatar', customerAvatar.value);
    }

    console.log('Submitting data:', Object.fromEntries(submitData)); // Debug log

    let response;
    if (isEditMode.value) {
      // For edit mode, use PUT method
      submitData.append('_method', 'PUT');
      response = await axios.post(`/api/customers/${customerId.value}`, submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    } else {
      // For create mode, use POST method
      response = await axios.post('/api/customers', submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    }

    console.log('Response:', response); // Debug log

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Cliente atualizado com sucesso!' : 'Cliente cadastrado com sucesso!',
      life: 3000
    });

    // Redirect to edit page if creating new customer
    if (!isEditMode.value && response.data?.id) {
      router.push({ name: 'customers.edit', params: { id: response.data.id } });
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
      detail: error.response?.data?.message || `Falha ao ${isEditMode.value ? 'atualizar' : 'cadastrar'} cliente`,
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Computed properties for dynamic content
const pageTitle = computed(() => isEditMode.value ? 'Editar Cliente' : 'Cadastrar Cliente');
const submitButtonLabel = computed(() => {
  if (isSubmitting.value) {
    return isEditMode.value ? 'Atualizando...' : 'Cadastrando...';
  }
  return isEditMode.value ? 'Atualizar Cliente' : 'Cadastrar Cliente';
});

// Customer status computed properties
const formattedCreatedDate = computed(() => {
  // This would come from the loaded customer data
  return isEditMode.value ? 'Carregando...' : 'Novo cliente';
});

const formattedUpdatedDate = computed(() => {
  // This would come from the loaded customer data
  return isEditMode.value ? 'Carregando...' : 'Não atualizado';
});
</script>

<style scoped>
.customer-form-container {
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

.customer-status-fields .status-field {
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

.status-select-button {
  font-size: 0.75rem;
}

.customer-avatar-section {
  margin-top: 1.5rem;
}

.avatar-upload-container {
  position: relative;
}

.upload-placeholder {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s;
}

.upload-placeholder:hover {
  border-color: #9ca3af;
}

.avatar-preview-container {
  position: relative;
  display: inline-block;
}

.customer-avatar-preview {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid #e5e7eb;
}

.avatar-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.avatar-preview-container:hover .avatar-overlay {
  opacity: 1;
}

.notes-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}
</style>