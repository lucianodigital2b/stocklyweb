<template>
  <div class="inventory-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoading">
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
                
                <!-- Warehouse Selection -->
                <!-- <div class="field mb-4">
                  <Label for="warehouse_id" :required="true">
                    Depósito
                  </Label>
                  <Dropdown 
                    id="warehouse_id" 
                    v-model="formData.warehouse_id" 
                    :options="warehouses"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Selecione um depósito"
                    class="w-full"
                    :class="{ 'p-invalid': errors.warehouse_id }"
                    :loading="isLoadingWarehouses"
                  />
                  <small v-if="errors.warehouse_id" class="p-error text-red-600">{{ Array.isArray(errors.warehouse_id) ? errors.warehouse_id[0] : errors.warehouse_id }}</small>
                </div> -->

                <!-- Product Selection -->
                <div class="field mb-4">
                  <Label for="product_id" :required="true">
                    Produto
                  </Label>
                  <Dropdown 
                    id="product_id" 
                    v-model="formData.product_id" 
                    :options="products"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Selecione um produto"
                    class="w-full"
                    :class="{ 'p-invalid': errors.product_id }"
                    :loading="isLoadingProducts"
                    filter
                    showClear
                  >
                    <template #option="slotProps">
                      <div class="flex align-items-center">
                        <div>
                          <div class="font-medium">{{ slotProps.option.name }}</div>
                          <div class="text-sm text-gray-600">SKU: {{ slotProps.option.sku }}</div>
                        </div>
                      </div>
                    </template>
                  </Dropdown>
                  <small v-if="errors.product_id" class="p-error text-red-600">{{ Array.isArray(errors.product_id) ? errors.product_id[0] : errors.product_id }}</small>
                </div>

                <!-- Stock Quantity -->
                <div class="field mb-4">
                  <Label for="stock" :required="true">
                    Quantidade em Estoque
                  </Label>
                  <InputNumber 
                    id="stock" 
                    v-model="formData.stock" 
                    :min="0"
                    :disabled="formData.is_infinite"
                    placeholder="Digite a quantidade"
                    class="w-full"
                    :class="{ 'p-invalid': errors.stock }"
                  />
                  <small v-if="errors.stock" class="p-error text-red-600">{{ Array.isArray(errors.stock) ? errors.stock[0] : errors.stock }}</small>
                </div>

                <!-- Infinite Stock Toggle -->
                <div class="field mb-4">
                  <div class="flex align-items-center">
                    <Checkbox 
                      id="is_infinite" 
                      v-model="formData.is_infinite" 
                      :binary="true"
                    />
                    <Label for="is_infinite" class="ml-2">
                      Estoque Infinito
                    </Label>
                  </div>
                  <small class="text-gray-600">Quando ativado, o produto terá estoque ilimitado</small>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-2 mt-6">
                  <Button 
                    label="Cancelar" 
                    severity="secondary" 
                    @click="handleCancel"
                    :disabled="isSubmitting"
                  />
                  <Button 
                    :label="submitButtonLabel" 
                    type="submit"
                    :loading="isSubmitting"
                    :disabled="isSubmitting"
                  />
                </div>
              </template>
            </Card>
          </div>

          <!-- Right Side - Info Card -->
          <div class="col-12 md:col-4">
            <ToggleCard title="Informações do Inventário" class="mb-4">
              <template #content>
                <div class="inventory-info-fields">
                  <div class="info-field">
                    <div class="info-row">
                      <span class="info-label">Status:</span>
                      <span class="info-value">{{ isEditMode ? 'Editando' : 'Novo' }}</span>
                    </div>
                  </div>
                  
                  <div v-if="selectedProduct" class="info-field">
                    <div class="info-row">
                      <span class="info-label">Produto Selecionado:</span>
                      <span class="info-value">{{ selectedProduct.name }}</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">SKU:</span>
                      <span class="info-value">{{ selectedProduct.sku }}</span>
                    </div>
                  </div>

                  <div v-if="selectedWarehouse" class="info-field">
                    <div class="info-row">
                      <span class="info-label">Depósito Selecionado:</span>
                      <span class="info-value">{{ selectedWarehouse.name }}</span>
                    </div>
                  </div>

                  <div v-if="formData.is_infinite" class="info-field">
                    <div class="info-row">
                      <span class="info-label">Tipo de Estoque:</span>
                      <span class="info-value text-green-600">Infinito</span>
                    </div>
                  </div>
                </div>
              </template>
            </ToggleCard>
          </div>
        </div>
      </template>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

// Components
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Checkbox from 'primevue/checkbox';
import Label from '../../components/Label.vue';
import ToggleCard from '../../components/InfoCard.vue';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

// Reactive data
const formData = reactive({
  warehouse_id: null,
  product_id: null,
  stock: 0,
  is_infinite: false
});

const errors = ref({});
const isSubmitting = ref(false);
const isLoading = ref(false);
const isLoadingWarehouses = ref(true);
const isLoadingProducts = ref(true);
const warehouses = ref([]);
const products = ref([]);

// Computed properties
const isEditMode = computed(() => !!route.params.id);
const pageTitle = computed(() => {
  return isEditMode.value ? 'Editar Inventário' : 'Novo Inventário';
});

const submitButtonLabel = computed(() => {
  return isEditMode.value ? 'Atualizar Inventário' : 'Criar Inventário';
});

const selectedProduct = computed(() => {
  return products.value.find(product => product.id === formData.product_id);
});

const selectedWarehouse = computed(() => {
  return warehouses.value.find(warehouse => warehouse.id === formData.warehouse_id);
});

// Watch for infinite stock changes
watch(() => formData.is_infinite, (newValue) => {
  if (newValue) {
    formData.stock = 0;
  }
});

// Methods
const loadWarehouses = async () => {
  try {
    isLoadingWarehouses.value = true;
    const response = await axios.get('/api/warehouses');
    warehouses.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading warehouses:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro ao carregar depósitos',
      life: 3000
    });
  } finally {
    isLoadingWarehouses.value = false;
  }
};

const loadProducts = async () => {
  try {
    isLoadingProducts.value = true;
    const response = await axios.get('/api/products');
    products.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading products:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro ao carregar produtos',
      life: 3000
    });
  } finally {
    isLoadingProducts.value = false;
  }
};

const loadInventory = async () => {
  if (!isEditMode.value) return;

  try {
    isLoading.value = true;
    const response = await axios.get(`/api/inventories/${route.params.id}`);
    const inventory = response.data.data || response.data;
    
    // Populate form data
    Object.keys(formData).forEach(key => {
      if (inventory[key] !== undefined) {
        formData[key] = inventory[key];
      }
    });
  } catch (error) {
    console.error('Error loading inventory:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro ao carregar inventário',
      life: 3000
    });
    router.push({ name: 'inventories.index' });
  } finally {
    isLoading.value = false;
  }
};

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    errors.value = {};

    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/inventories/${route.params.id}`, formData);
    } else {
      response = await axios.post('/api/inventories', formData);
    }

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Inventário atualizado com sucesso!' : 'Inventário criado com sucesso!',
      life: 3000
    });

    router.push({ name: 'inventories.index' });

  } catch (error) {
    console.error('Error submitting form:', error);
    
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao salvar inventário',
      life: 3000
    });
  } finally {
    isSubmitting.value = false;
  }
};

const handleCancel = () => {
  router.push({ name: 'inventories.index' });
};

// Load data on component mount
onMounted(async () => {
  await Promise.all([
    loadWarehouses(),
    loadProducts()
  ]);
  
  if (isEditMode.value) {
    await loadInventory();
  }
});
</script>

<style scoped>
.inventory-form-container {
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

/* Info Card Styles */
.inventory-info-fields {
  padding: 1rem;
}

.info-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-label {
  font-weight: 500;
  color: #374151;
}

.info-value {
  color: #6b7280;
  font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
  .inventory-form-container {
    padding: 0.5rem;
  }
}
</style>