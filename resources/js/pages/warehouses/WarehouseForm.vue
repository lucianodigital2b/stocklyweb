<template>
  <div class="warehouse-form-container">
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
                
                <!-- Warehouse Name -->
                <div class="field mb-4">
                  <Label for="name" :required="true">
                    Nome do Depósito
                  </Label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    placeholder="Digite o nome do depósito"
                    class="w-full"
                    :class="{ 'p-invalid': errors.name }"
                  />
                  <small v-if="errors.name" class="p-error text-red-600">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</small>
                </div>

                <!-- Status -->
                <div class="field mb-4">
                  <Label for="status">
                    Status
                  </Label>
                  <div class="flex align-items-center">
                    <InputSwitch 
                      id="status" 
                      v-model="formData.status" 
                    />
                    <label for="status" class="ml-2">{{ formData.status ? 'Ativo' : 'Inativo' }}</label>
                  </div>
                </div>

                <!-- Dock Management Section -->
                <div class="field mb-4">
                  <div class="flex justify-between items-center mb-3">
                    <Label>Docas Vinculadas</Label>
                    <Button 
                      type="button" 
                      label="Adicionar Doca" 
                      icon="pi pi-plus" 
                      size="small"
                      @click="showAddDockDialog = true"
                    />
                  </div>
                  
                  <!-- Docks Table -->
                  <div v-if="formData.docks && formData.docks.length > 0" class="border border-gray-200 rounded">
                    <DataTable :value="formData.docks" class="p-datatable-sm">
                      <Column field="dock_name" header="Nome da Doca">
                        <template #body="slotProps">
                          {{ slotProps.data.dock_name || `Doca ${slotProps.data.dock_id}` }}
                        </template>
                      </Column>
                      <Column field="processing_days" header="Dias de Processamento">
                        <template #body="slotProps">
                          <InputNumber 
                            v-model="slotProps.data.processing_days" 
                            :min="0" 
                            class="w-full"
                            size="small"
                          />
                        </template>
                      </Column>
                      <Column field="processing_seconds" header="Segundos de Processamento">
                        <template #body="slotProps">
                          <InputNumber 
                            v-model="slotProps.data.processing_seconds" 
                            :min="0" 
                            class="w-full"
                            size="small"
                          />
                        </template>
                      </Column>
                      <Column field="extra_fee" header="Taxa Extra">
                        <template #body="slotProps">
                          <InputNumber 
                            v-model="slotProps.data.extra_fee" 
                            mode="currency" 
                            currency="BRL" 
                            locale="pt-BR"
                            :min="0" 
                            class="w-full"
                            size="small"
                          />
                        </template>
                      </Column>
                      <Column header="Ações">
                        <template #body="slotProps">
                          <Button 
                            icon="pi pi-trash" 
                            severity="danger" 
                            size="small"
                            @click="removeDock(slotProps.index)"
                          />
                        </template>
                      </Column>
                    </DataTable>
                  </div>
                  
                  <!-- Empty State -->
                  <div v-else class="text-center py-8 border border-gray-200 rounded bg-gray-50">
                    <i class="pi pi-truck text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 mb-3">Nenhuma doca adicionada</p>
                    <Button 
                      type="button" 
                      label="Adicionar Primeira Doca" 
                      icon="pi pi-plus" 
                      size="small"
                      @click="showAddDockDialog = true"
                    />
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <!-- Right Side - Actions -->
          <div class="col-12 md:col-4">
            <Card class="form-card">
              <template #content>
                <h4 class="mb-4">Ações</h4>
                
                <div class="flex flex-column gap-3">
                  <Button 
                    type="submit" 
                    :label="isEditMode ? 'Atualizar Depósito' : 'Criar Depósito'"
                    :loading="isSubmitting"
                    class="w-full"
                  />
                  
                  <Button 
                    type="button" 
                    label="Cancelar" 
                    severity="secondary"
                    class="w-full"
                    @click="$router.push({ name: 'warehouses.index' })"
                  />
                  
                  <Button 
                    v-if="isEditMode" 
                    type="button" 
                    label="Excluir Depósito" 
                    severity="danger"
                    class="w-full"
                    @click="deleteWarehouse"
                  />
                </div>
              </template>
            </Card>
          </div>
        </div>
      </template>
    </form>

    <!-- Add Dock Dialog -->
    <Dialog 
      v-model:visible="showAddDockDialog" 
      modal 
      header="Adicionar Doca" 
      :style="{ width: '500px' }"
    >
      <div class="field mb-4">
        <Label for="dock_id" :required="true">Doca</Label>
        <Dropdown 
          id="dock_id"
          v-model="newDock.dock_id" 
          :options="availableDocks" 
          optionLabel="name" 
          optionValue="id"
          placeholder="Selecione uma doca"
          class="w-full"
          :class="{ 'p-invalid': dockErrors.dock_id }"
        />
        <small v-if="dockErrors.dock_id" class="p-error">{{ dockErrors.dock_id }}</small>
      </div>

      <div class="field mb-4">
        <Label for="processing_days">Dias de Processamento</Label>
        <InputNumber 
          id="processing_days"
          v-model="newDock.processing_days" 
          :min="0" 
          class="w-full"
        />
      </div>

      <div class="field mb-4">
        <Label for="processing_seconds">Segundos de Processamento</Label>
        <InputNumber 
          id="processing_seconds"
          v-model="newDock.processing_seconds" 
          :min="0" 
          class="w-full"
        />
      </div>

      <div class="field mb-4">
        <Label for="extra_fee">Taxa Extra</Label>
        <InputNumber 
          id="extra_fee"
          v-model="newDock.extra_fee" 
          mode="currency" 
          currency="BRL" 
          locale="pt-BR"
          :min="0" 
          class="w-full"
        />
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Cancelar" severity="secondary" @click="cancelAddDock" />
          <Button label="Adicionar" @click="addDock" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

// Components
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import InputSwitch from 'primevue/inputswitch';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Label from '../../components/Label.vue';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

// Reactive data
const isEditMode = computed(() => !!route.params.id);
const warehouseId = computed(() => route.params.id);
const pageTitle = computed(() => isEditMode.value ? 'Editar Depósito' : 'Novo Depósito');

const isLoading = ref(false);
const isSubmitting = ref(false);
const errors = ref({});
const dockErrors = ref({});

const formData = reactive({
  name: '',
  status: true,
  docks: []
});

const availableDocks = ref([]);
const showAddDockDialog = ref(false);

const newDock = reactive({
  dock_id: null,
  dock_name: '',
  processing_days: 0,
  processing_seconds: 0,
  extra_fee: 0
});

// Load form data
onMounted(async () => {
  await loadAvailableDocks();
  if (isEditMode.value) {
    await loadWarehouseData();
  }
});

const loadAvailableDocks = async () => {
  try {
    // Since we don't have a Dock model, we'll create a simple mock for now
    // In a real scenario, you would have an API endpoint for docks
    availableDocks.value = [
      { id: 1, name: 'Doca A' },
      { id: 2, name: 'Doca B' },
      { id: 3, name: 'Doca C' },
      { id: 4, name: 'Doca D' }
    ];
  } catch (error) {
    console.error('Error loading docks:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao carregar docas disponíveis.',
      life: 5000
    });
  }
};

const loadWarehouseData = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/warehouses/${warehouseId.value}`);
    const warehouse = response.data.data;
    
    formData.name = warehouse.name;
    formData.status = warehouse.status;
    
    // Load docks with their relationship data
    if (warehouse.docks_relation && warehouse.docks_relation.length > 0) {
      formData.docks = warehouse.docks_relation.map(dock => ({
        dock_id: dock.dock_id,
        dock_name: dock.dock?.name || `Doca ${dock.dock_id}`,
        processing_days: dock.processing_days || 0,
        processing_seconds: dock.processing_seconds || 0,
        extra_fee: dock.extra_fee || 0
      }));
    }
  } catch (error) {
    console.error('Error loading warehouse:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao carregar dados do depósito.',
      life: 5000
    });
  } finally {
    isLoading.value = false;
  }
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  errors.value = {};
  
  try {
    const payload = {
      name: formData.name,
      status: formData.status,
      docks: formData.docks.map(dock => ({
        dock_id: dock.dock_id,
        processing_days: dock.processing_days || 0,
        processing_seconds: dock.processing_seconds || 0,
        extra_fee: dock.extra_fee || 0
      }))
    };

    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/warehouses/${warehouseId.value}`, payload);
    } else {
      response = await axios.post('/api/warehouses', payload);
    }

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Depósito atualizado com sucesso!' : 'Depósito criado com sucesso!',
      life: 3000
    });

    router.push({ name: 'warehouses.index' });
  } catch (error) {
    console.error('Error saving warehouse:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Falha ao salvar depósito. Tente novamente.',
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

const addDock = () => {
  dockErrors.value = {};
  
  // Validation
  if (!newDock.dock_id) {
    dockErrors.value.dock_id = 'Selecione uma doca';
    return;
  }
  
  // Check if dock is already added
  if (formData.docks.some(dock => dock.dock_id === newDock.dock_id)) {
    dockErrors.value.dock_id = 'Esta doca já foi adicionada';
    return;
  }
  
  // Find dock name
  const selectedDock = availableDocks.value.find(dock => dock.id === newDock.dock_id);
  
  // Add dock to form data
  formData.docks.push({
    dock_id: newDock.dock_id,
    dock_name: selectedDock?.name || `Doca ${newDock.dock_id}`,
    processing_days: newDock.processing_days || 0,
    processing_seconds: newDock.processing_seconds || 0,
    extra_fee: newDock.extra_fee || 0
  });
  
  // Reset and close dialog
  cancelAddDock();
};

const cancelAddDock = () => {
  newDock.dock_id = null;
  newDock.dock_name = '';
  newDock.processing_days = 0;
  newDock.processing_seconds = 0;
  newDock.extra_fee = 0;
  dockErrors.value = {};
  showAddDockDialog.value = false;
};

const removeDock = (index) => {
  if (confirm('Tem certeza que deseja remover esta doca?')) {
    formData.docks.splice(index, 1);
  }
};

const deleteWarehouse = async () => {
  if (confirm('Tem certeza que deseja excluir este depósito? Esta ação não pode ser desfeita.')) {
    try {
      await axios.delete(`/api/warehouses/${warehouseId.value}`);
      
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Depósito excluído com sucesso!',
        life: 3000
      });
      
      router.push({ name: 'warehouses.index' });
    } catch (error) {
      console.error('Error deleting warehouse:', error);
      
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Falha ao excluir depósito. Tente novamente.',
        life: 5000
      });
    }
  }
};
</script>

<style scoped>
.warehouse-form-container {
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