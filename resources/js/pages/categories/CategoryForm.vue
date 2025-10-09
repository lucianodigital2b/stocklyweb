<template>
  <div class="category-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Form Content -->
      <div class="grid">
        <!-- Left Side - Form Fields -->
        <div class="col-12 md:col-8">
          <Card class="form-card">
            <template #content>
              <h3 class="mb-4">{{ pageTitle }}</h3>
              
              <!-- Category Name -->
              <div class="field mb-4">
                <Label for="name" :required="true">
                  Nome
                </Label>
                <InputText 
                  id="name" 
                  v-model="formData.name" 
                  placeholder="Digite o nome da categoria"
                  class="w-full"
                  :class="{ 'p-invalid': errors.name }"
                />
                <small v-if="errors.name" class="p-error text-red-600">{{ errors.name }}</small>
              </div>


            </template>
          </Card>
        </div>

        <!-- Right Side - Category Status -->
        <div class="col-12 md:col-4">
          <!-- Category Status Card -->
          <ToggleCard 
            title="Publicar"
            subtitle="Escolha quando esta categoria deve ir ao ar"
            :toggleable="true"
            class="mb-4"
          >
            <div class="category-status-fields">
              <!-- Estado -->
              <div class="status-field mb-3">
                <div class="status-row">
                  <span class="status-label">Estado:</span>
                  <SelectButton 
                    v-model="formData.status" 
                    :options="statusOptions" 
                    optionLabel="label" 
                    optionValue="value"
                    class="status-select-button"
                  />
                </div>
              </div>

              <!-- Criado em -->
              <div class="status-field mb-3" v-if="formData.created_at">
                <div class="status-row">
                  <span class="status-label">Criado em:</span>
                  <span class="status-value">{{ formattedCreatedDate }}</span>
                </div>
              </div>

              <!-- Atualizado em -->
              <div class="status-field" v-if="formData.updated_at">
                <div class="status-row">
                  <span class="status-label">Atualizado em:</span>
                  <span class="status-value">{{ formattedUpdatedDate }}</span>
                </div>
              </div>

              <!-- Submit Button -->
              <Button 
                type="submit" 
                :label="submitButtonLabel" 
                class="mt-5 w-full" 
                icon="pi pi-check"
                :disabled="isSubmitting"
                :loading="isSubmitting"
              />
            </div>
          </ToggleCard>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';

import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';
import Label from '../../components/Label.vue';
import ToggleCard from '../../components/InfoCard.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

// Form state
const formData = ref({
  name: '',
  status: 'active',
  created_at: null,
  updated_at: null
});

const errors = ref({});
const isSubmitting = ref(false);
const isLoadingCategory = ref(false);

// Status options
const statusOptions = ref([
  { label: 'Ativo', value: 'active' },
  { label: 'Inativo', value: 'inactive' }
]);

// Computed properties
const isEditMode = computed(() => !!route.params.id);
const pageTitle = computed(() => isEditMode.value ? 'Editar Categoria' : 'Nova Categoria');
const submitButtonLabel = computed(() => isEditMode.value ? 'Atualizar Categoria' : 'Criar Categoria');

const formattedCreatedDate = computed(() => {
  if (!formData.value.created_at) return '-';
  return new Date(formData.value.created_at).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const formattedUpdatedDate = computed(() => {
  if (!formData.value.updated_at) return '-';
  return new Date(formData.value.updated_at).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

// Methods
const loadCategory = async () => {
  if (!isEditMode.value) return;
  
  isLoadingCategory.value = true;
  try {
    const response = await axios.get(`/api/categories/${route.params.id}`);
    formData.value = { ...formData.value, ...response.data };
  } catch (error) {
    console.error('Error loading category:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao carregar categoria. Tente novamente.',
      life: 5000
    });
    router.push({ name: 'categories.index' });
  } finally {
    isLoadingCategory.value = false;
  }
};

const validateForm = () => {
  errors.value = {};
  
  if (!formData.value.name?.trim()) {
    errors.value.name = 'Nome da categoria é obrigatório';
  }
  
  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) return;
  
  isSubmitting.value = true;
  
  try {
    const payload = {
      name: formData.value.name.trim(),
      status: formData.value.status
    };
    
    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/categories/${route.params.id}`, payload);
    } else {
      response = await axios.post('/api/categories', payload);
    }
    
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!',
      life: 3000
    });
    
    router.push({ name: 'categories.index' });
  } catch (error) {
    console.error('Error saving category:', error);
    
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Falha ao salvar categoria. Tente novamente.',
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Lifecycle
onMounted(() => {
  loadCategory();
});
</script>

<style scoped>
.category-form-container {
  padding: 1rem;
}

.form-card {
  height: fit-content;
}

.status-field {
  padding: 0.5rem 0;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.status-label {
  font-weight: 500;
  color: var(--text-color-secondary);
  min-width: 100px;
}

.status-value {
  color: var(--text-color);
  font-size: 0.875rem;
}

.status-select-button {
  flex: 1;
  min-width: 120px;
}

@media (max-width: 768px) {
  .status-row {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .status-select-button {
    width: 100%;
  }
}
</style>