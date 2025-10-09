<template>
  <div class="cost-center-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoadingCostCenter">
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
                
                <!-- Cost Center Name -->
                <div class="field mb-4">
                  <Label for="name" :required="true">
                    Nome do Centro de Custo
                  </Label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    placeholder="Digite o nome do centro de custo"
                    class="w-full"
                    :class="{ 'p-invalid': errors.name }"
                  />
                  <small v-if="errors.name" class="p-error text-red-600">{{ errors.name }}</small>
                </div>

                <!-- Description -->
                <div class="field mb-4">
                  <Label for="description">
                    Descrição
                  </Label>
                  <Textarea 
                    id="description" 
                    v-model="formData.description" 
                    placeholder="Digite a descrição do centro de custo"
                    class="w-full"
                    rows="4"
                    :class="{ 'p-invalid': errors.description }"
                  />
                  <small v-if="errors.description" class="p-error text-red-600">{{ errors.description }}</small>
                </div>

                <!-- Code -->
                <div class="field mb-4">
                  <Label for="code">
                    Código
                  </Label>
                  <InputText 
                    id="code" 
                    v-model="formData.code" 
                    placeholder="Digite o código do centro de custo"
                    class="w-full"
                    :class="{ 'p-invalid': errors.code }"
                  />
                  <small v-if="errors.code" class="p-error">{{ errors.code }}</small>
                </div>

                <!-- Status -->
                <div class="field mb-4">
                  <Label for="status">
                    Status
                  </Label>
                  <Dropdown 
                    id="status" 
                    v-model="formData.status" 
                    :options="statusOptions" 
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Selecione o status"
                    class="w-full"
                    :class="{ 'p-invalid': errors.status }"
                  />
                  <small v-if="errors.status" class="p-error">{{ errors.status }}</small>
                </div>
              </template>
            </Card>
          </div>

          <!-- Divider -->
          <div class="col-12 md:col-1 flex align-items-center justify-content-center">
            <div class="form-divider"></div>
          </div>

          <!-- Right Side - Cost Center Info -->
          <div class="col-12 md:col-4">
            <!-- Cost Center Status Card -->
            <ToggleCard 
              title="Status do Centro de Custo"
              subtitle="Informações sobre o status do centro de custo"
              :toggleable="true"
              class="mb-4"
            >
              <div class="cost-center-status-fields">
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
                  :disabled="isSubmitting || isLoadingCostCenter"
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
import { reactive, ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useRoute, useRouter } from 'vue-router';
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

// Check if we're in edit mode
const isEditMode = computed(() => !!route.params.id);
const costCenterId = computed(() => route.params.id);

const formData = reactive({
  name: '',
  description: '',
  code: '',
  status: 'active'
});

const errors = reactive({
  name: '',
  description: '',
  code: '',
  status: ''
});

// Status options
const statusOptions = ref([
  { label: 'Ativo', value: 'active' },
  { label: 'Inativo', value: 'inactive' }
]);

// Load cost center data if in edit mode
const isLoadingCostCenter = ref(false);

const loadCostCenterData = async () => {
  if (isEditMode.value) {
    isLoadingCostCenter.value = true;
    try {
      const response = await axios.get(`/api/cost-centers/${costCenterId.value}`);
      console.log('Cost Center response:', response); // Debug log
      
      if (response.data) {
        const costCenter = response.data;
        console.log('Cost Center data:', costCenter); // Debug log
        
        // Dynamically map cost center data to formData fields
        const fieldMappings = {
          name: costCenter.name || '',
          description: costCenter.description || '',
          code: costCenter.code || '',
          status: costCenter.status || 'active'
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
      console.error('Error loading cost center:', error);
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Falha ao carregar dados do centro de custo',
        life: 5000
      });
    } finally {
      isLoadingCostCenter.value = false;
    }
  }
};

onMounted(() => {
  loadCostCenterData();
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
    errors.name = 'Nome do centro de custo é obrigatório';
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

    console.log('Submitting data:', submitData); // Debug log

    let response;
    if (isEditMode.value) {
      // For edit mode, use PUT method
      response = await axios.put(`/api/cost-centers/${costCenterId.value}`, submitData);
    } else {
      // For create mode, use POST method
      response = await axios.post('/api/cost-centers', submitData);
    }

    console.log('Response:', response); // Debug log

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Centro de custo atualizado com sucesso!' : 'Centro de custo cadastrado com sucesso!',
      life: 3000
    });

    // Redirect to edit page if creating new cost center
    if (!isEditMode.value && response.data?.id) {
      router.push({ name: 'cost-centers.edit', params: { id: response.data.id } });
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
      detail: error.response?.data?.message || `Falha ao ${isEditMode.value ? 'atualizar' : 'cadastrar'} centro de custo`,
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Computed properties for dynamic content
const pageTitle = computed(() => isEditMode.value ? 'Editar Centro de Custo' : 'Cadastrar Centro de Custo');
const submitButtonLabel = computed(() => {
  if (isSubmitting.value) {
    return isEditMode.value ? 'Atualizando...' : 'Cadastrando...';
  }
  return isEditMode.value ? 'Atualizar Centro de Custo' : 'Cadastrar Centro de Custo';
});

// Cost center status computed properties
const formattedCreatedDate = computed(() => {
  // This would come from the loaded cost center data
  return isEditMode.value ? 'Carregando...' : 'Novo centro de custo';
});

const formattedUpdatedDate = computed(() => {
  // This would come from the loaded cost center data
  return isEditMode.value ? 'Carregando...' : 'Não atualizado';
});
</script>

<style scoped>
.cost-center-form-container {
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

.cost-center-status-fields .status-field {
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
</style>