<template>
  <div class="entry-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoadingData">
        <FormSkeleton />
      </template>

      <!-- Form Content -->
      <template v-else>
        <div class="grid">
          <!-- Left Side - Form Fields -->
          <div class="col-12 md:col-8">
            <Card class="form-card">
              <template #content>
                <h3 class="mb-4">{{ pageTitle }}</h3>
                
                <!-- Value -->
                <div class="field mb-4">
                  <Label for="value" :required="true">
                    Valor
                  </Label>
                  <InputNumber 
                    id="value" 
                    v-model="formData.value" 
                    mode="currency" 
                    currency="BRL" 
                    locale="pt-BR"
                    placeholder="R$0,00"
                    class="w-full"
                    :class="{ 'p-invalid': errors.value }"
                  />
                  <small v-if="errors.value" class="p-error text-red-600">{{ errors.value }}</small>
                </div>

                <!-- Operation Type -->
                <div class="field mb-4">
                  <Label for="operation" :required="true">
                    Tipo de Operação
                  </Label>
                  <SelectButton 
                    v-model="formData.operation" 
                    :options="operationOptions" 
                    optionLabel="label" 
                    optionValue="value"
                    class="w-full"
                    :class="{ 'p-invalid': errors.operation }"
                  />
                  <small v-if="errors.operation" class="p-error text-red-600">{{ errors.operation }}</small>
                </div>

                <!-- Supplier -->
                <div class="field mb-4">
                  <Label for="supplier_id">
                    Fornecedor
                  </Label>
                  <Dropdown 
                    id="supplier_id" 
                    v-model="formData.supplier_id" 
                    :options="suppliers" 
                    optionLabel="name" 
                    optionValue="id"
                    placeholder="Selecione um fornecedor"
                    class="w-full"
                    filter
                    showClear
                  />
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
                    placeholder="Selecione um centro de custo"
                    class="w-full"
                    filter
                    showClear
                  />
                </div>

                <!-- Payment Method -->
                <div class="field mb-4">
                  <Label for="payment_method">
                    Método de Pagamento
                  </Label>
                  <Dropdown 
                    id="payment_method" 
                    v-model="formData.payment_method" 
                    :options="paymentMethodOptions" 
                    optionLabel="label" 
                    optionValue="value"
                    placeholder="Selecione o método de pagamento"
                    class="w-full"
                  />
                </div>

                <!-- Due Date -->
                <div class="field mb-4">
                  <Label for="due_at">
                    Data de Vencimento
                  </Label>
                  <Calendar 
                    id="due_at" 
                    v-model="formData.due_at" 
                    dateFormat="dd/mm/yy"
                    placeholder="Selecione a data de vencimento"
                    class="w-full"
                    showIcon
                  />
                </div>

                <!-- Paid Date -->
                <div class="field mb-4">
                  <Label for="paid_at">
                    Data de Pagamento
                  </Label>
                  <Calendar 
                    id="paid_at" 
                    v-model="formData.paid_at" 
                    dateFormat="dd/mm/yy"
                    placeholder="Selecione a data de pagamento"
                    class="w-full"
                    showIcon
                  />
                </div>

                <!-- External Code -->
                <div class="field mb-4">
                  <Label for="external_code">
                    Código Externo
                  </Label>
                  <InputText 
                    id="external_code" 
                    v-model="formData.external_code" 
                    placeholder="Digite o código externo"
                    class="w-full"
                  />
                  <small class="text-gray-500">Código de referência externa (opcional).</small>
                </div>

                <!-- Account -->
                <div class="field mb-4">
                  <Label for="account">
                    Conta
                  </Label>
                  <InputText 
                    id="account" 
                    v-model="formData.account" 
                    placeholder="Digite a conta"
                    class="w-full"
                  />
                </div>

                <!-- Barcode -->
                <div class="field mb-4">
                  <Label for="barcode">
                    Código de Barras
                  </Label>
                  <InputText 
                    id="barcode" 
                    v-model="formData.barcode" 
                    placeholder="Digite o código de barras"
                    class="w-full"
                  />
                </div>

                <!-- Observations -->
                <div class="field mb-4">
                  <Label for="observations">
                    Observações
                  </Label>
                  <Textarea 
                    id="observations" 
                    v-model="formData.observations" 
                    rows="4"
                    class="w-full"
                    placeholder="Observações sobre o lançamento..."
                    :maxlength="1000"
                  />
                  <small class="text-color-secondary">Caracteres: {{ (formData.observations || '').length }}/1000</small>
                </div>

                <!-- Payment Info -->
                <div class="field mb-4">
                  <Label for="payment_info">
                    Informações de Pagamento
                  </Label>
                  <Textarea 
                    id="payment_info" 
                    v-model="formData.payment_info" 
                    rows="3"
                    class="w-full"
                    placeholder="Informações adicionais sobre o pagamento..."
                    :maxlength="500"
                  />
                  <small class="text-color-secondary">Caracteres: {{ (formData.payment_info || '').length }}/500</small>
                </div>

              </template>
            </Card>
          </div>

          <!-- Right Side - Summary and Actions -->
          <div class="col-12 md:col-4">
            <!-- Status Card -->
            <Card class="mb-4">
              <template #content>
                <h4 class="mb-3">Status do Lançamento</h4>
                
                <div class="mb-3">
                  <strong>Status:</strong>
                  <Tag :value="entryStatus" :severity="entryStatusSeverity" class="ml-2" />
                </div>
                
                <div class="mb-3">
                  <strong>Tipo:</strong>
                  <Tag :value="operationLabel" :severity="operationSeverity" class="ml-2" />
                </div>
                
                <div v-if="formData.value" class="mb-3">
                  <strong>Valor:</strong>
                  <div class="text-lg font-semibold mt-1" :class="operationClass">
                    {{ formatCurrency(formData.value) }}
                  </div>
                </div>
              </template>
            </Card>

            <!-- Actions Card -->
            <Card>
              <template #content>
                <h4 class="mb-3">Ações</h4>
                
                <div class="flex flex-column gap-2">
                  <Button 
                    type="submit" 
                    :label="submitButtonLabel" 
                    :loading="isSubmitting"
                    :disabled="isSubmitting"
                    class="w-full"
                  />
                  
                  <Button 
                    label="Cancelar" 
                    severity="secondary" 
                    outlined
                    @click="$router.push({ name: 'entries.index' })"
                    class="w-full"
                  />
                </div>
              </template>
            </Card>
          </div>
        </div>
      </template>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useRoute, useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import SelectButton from 'primevue/selectbutton';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';
import Label from '../../components/Label.vue';
import axios from '../../plugins/axios';

const toast = useToast();
const route = useRoute();
const router = useRouter();

// Check if we're in edit mode
const isEditMode = computed(() => !!route.params.id);
const entryId = computed(() => route.params.id);

const formData = reactive({
  value: null,
  operation: 1, // Default to revenue
  supplier_id: null,
  cost_center_id: null,
  payment_method: null,
  due_at: null,
  paid_at: null,
  external_code: '',
  account: '',
  barcode: '',
  observations: '',
  payment_info: ''
});

const errors = reactive({
  value: '',
  operation: ''
});

// Loading states
const isLoadingData = ref(false);
const isSubmitting = ref(false);

// Options data
const suppliers = ref([]);
const costCenters = ref([]);

// Operation options
const operationOptions = ref([
  { label: 'Entrada', value: 1 },
  { label: 'Saída', value: 2 }
]);

// Payment method options
const paymentMethodOptions = ref([
  { label: 'PIX', value: 'pix' },
  { label: 'Boleto', value: 'bank_slip' },
  { label: 'Dinheiro', value: 'money' },
  { label: 'Crédito', value: 'credit' },
  { label: 'Débito', value: 'debit' }
]);

// Load suppliers and cost centers
const loadFormData = async () => {
  isLoadingData.value = true;
  try {
    const [suppliersResponse, costCentersResponse] = await Promise.all([
      axios.get('/api/suppliers'),
      axios.get('/api/cost-centers')
    ]);
    
    suppliers.value = suppliersResponse.data.data || suppliersResponse.data;
    costCenters.value = costCentersResponse.data.data || costCentersResponse.data;
  } catch (error) {
    console.error('Error loading form data:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro ao carregar dados do formulário',
      life: 3000
    });
  } finally {
    isLoadingData.value = false;
  }
};

// Load entry data if in edit mode
const loadEntryData = async () => {
  if (isEditMode.value) {
    try {
      const response = await axios.get(`/api/entries/${entryId.value}`);
      
      if (response.data) {
        const entry = response.data;
        
        // Map entry data to form fields
        Object.keys(formData).forEach(key => {
          if (entry.hasOwnProperty(key)) {
            if (key === 'due_at' || key === 'paid_at') {
              formData[key] = entry[key] ? new Date(entry[key]) : null;
            } else {
              formData[key] = entry[key];
            }
          }
        });
      }
    } catch (error) {
      console.error('Error loading entry:', error);
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Erro ao carregar dados do lançamento',
        life: 3000
      });
    }
  }
};

// Form validation
const validateForm = () => {
  let valid = true;
  
  // Clear previous errors
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  
  // Validate required fields
  if (!formData.value || formData.value <= 0) {
    errors.value = 'Valor é obrigatório e deve ser maior que zero';
    valid = false;
  }

  if (!formData.operation || (formData.operation !== 1 && formData.operation !== 2)) {
    errors.operation = 'Tipo de operação é obrigatório';
    valid = false;
  }

  return valid;
};

// Form submission
const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;

  try {
    const submitData = { ...formData };
    
    // Format dates for backend
    if (submitData.due_at) {
      submitData.due_at = submitData.due_at.toISOString().split('T')[0];
    }
    if (submitData.paid_at) {
      submitData.paid_at = submitData.paid_at.toISOString().split('T')[0];
    }

    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/entries/${entryId.value}`, submitData);
    } else {
      response = await axios.post('/api/entries', submitData);
    }

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Lançamento atualizado com sucesso!' : 'Lançamento cadastrado com sucesso!',
      life: 3000
    });

    // Redirect to edit page if creating new entry
    if (!isEditMode.value && response.data?.id) {
      router.push({ name: 'entries.edit', params: { id: response.data.id } });
    }

  } catch (error) {
    console.error('Submit error:', error);
    
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
      detail: error.response?.data?.message || `Falha ao ${isEditMode.value ? 'atualizar' : 'cadastrar'} lançamento`,
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Computed properties
const pageTitle = computed(() => isEditMode.value ? 'Editar Lançamento' : 'Cadastrar Lançamento');

const submitButtonLabel = computed(() => {
  if (isSubmitting.value) {
    return isEditMode.value ? 'Atualizando...' : 'Cadastrando...';
  }
  return isEditMode.value ? 'Atualizar Lançamento' : 'Cadastrar Lançamento';
});

const operationLabel = computed(() => {
  const option = operationOptions.value.find(op => op.value === formData.operation);
  return option ? option.label : 'N/A';
});

const operationSeverity = computed(() => {
  return formData.operation === 1 ? 'success' : 'danger';
});

const operationClass = computed(() => {
  return formData.operation === 1 ? 'text-green-600' : 'text-red-600';
});

const entryStatus = computed(() => {
  if (formData.paid_at) {
    return 'Pago';
  }
  
  if (formData.due_at && formData.due_at < new Date()) {
    return 'Em atraso';
  }
  
  return 'Pendente';
});

const entryStatusSeverity = computed(() => {
  if (formData.paid_at) {
    return 'success';
  }
  
  if (formData.due_at && formData.due_at < new Date()) {
    return 'danger';
  }
  
  return 'info';
});

// Helper functions
const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

// Load data on component mount
onMounted(async () => {
  await loadFormData();
  await loadEntryData();
});
</script>

<style scoped>
.entry-form-container {
  padding: 1rem;
}

.form-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.field {
  margin-bottom: 1rem;
}

.p-invalid {
  border-color: #e24c4c;
}

.p-error {
  color: #e24c4c;
  font-size: 0.875rem;
}
</style>