<template>
  <div class="order-list-container">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Pedidos</h1>
        <p class="text-sm text-gray-600 mt-1">Gerencie seus pedidos e acompanhe seus status</p>
      </div>
      <Button 
        label="Criar pedido" 
        icon="pi pi-plus"
        @click="$router.push('/orders/create')"
      />
    </div>

    <Card>
      <template #content>
        <DataTable 
          :value="orders" 
          :loading="loading"
          paginator 
          :rows="10"
          :totalRecords="totalRecords"
          lazy
          @page="onPage"
          dataKey="id"
          class="p-datatable-sm"
        >
          <Column field="status" header="Status" sortable>
            <template #body="{ data }">
              <Tag 
                :value="getStatusLabel(data.status)" 
                :severity="getStatusSeverity(data.status)"
                class="capitalize"
              />
            </template>
          </Column>
          
          <Column field="id" header="Pedido #" sortable>
            <template #body="{ data }">
              <span class="font-medium">#{{ data.id }}</span>
            </template>
          </Column>
          
          <Column field="customer.name" header="Cliente" sortable>
            <template #body="{ data }">
              <div v-if="data.customer">
                <p class="font-medium">{{ data.customer.name }}</p>
                <p class="text-sm text-gray-600">{{ data.customer.email }}</p>
              </div>
              <span v-else class="text-gray-400">Sem cliente</span>
            </template>
          </Column>
          
          <Column field="total_price" header="Total" sortable>
            <template #body="{ data }">
              <span class="font-medium">R$ {{ formatPrice(data.total_price) }}</span>
            </template>
          </Column>
          
          <Column field="created_at" header="Data" sortable>
            <template #body="{ data }">
              {{ formatDate(data.created_at) }}
            </template>
          </Column>
          
          <Column header="Ações">
            <template #body="{ data }">
              <div class="flex gap-2">
                <Button 
                  icon="pi pi-eye" 
                  severity="secondary" 
                  text 
                  size="small"
                  @click="viewOrder(data.id)"
                />
                <Button 
                  icon="pi pi-pencil" 
                  severity="secondary" 
                  text 
                  size="small"
                  @click="editOrder(data.id)"
                />
                <Button 
                  icon="pi pi-trash" 
                  severity="danger" 
                  text 
                  size="small"
                  @click="deleteOrder(data.id)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

// Components
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const router = useRouter();
const toast = useToast();

// Reactive data
const orders = ref([]);
const loading = ref(false);
const totalRecords = ref(0);

// Methods
const loadOrders = async (page = 1) => {
  loading.value = true;
  
  try {
    const { data } = await axios.get('/api/orders', {
      params: { page, per_page: 10 }
    });
    
    orders.value = data.data || [];
    totalRecords.value = data.total || 0;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao carregar pedidos'
    });
  } finally {
    loading.value = false;
  }
};

const onPage = (event) => {
  loadOrders(event.page + 1);
};

const viewOrder = (id) => {
  router.push(`/orders/${id}`);
};

const editOrder = (id) => {
  router.push(`/orders/${id}/edit`);
};

const deleteOrder = async (id) => {
  if (!confirm('Tem certeza que deseja excluir este pedido?')) return;
  
  try {
    await axios.delete(`/api/orders/${id}`);
    
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Pedido excluído com sucesso'
    });
    
    loadOrders();
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao excluir pedido'
    });
  }
};

const getStatusSeverity = (status) => {
  const severityMap = {
    pending: 'warning',
    processing: 'info',
    shipped: 'success',
    delivered: 'success',
    cancelled: 'danger'
  };
  return severityMap[status] || 'secondary';
};

const getStatusLabel = (status) => {
  const statusLabels = {
    pending: 'Pendente',
    processing: 'Processando',
    shipped: 'Enviado',
    delivered: 'Entregue',
    cancelled: 'Cancelado'
  };
  return statusLabels[status] || status;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price || 0);
};

const formatDate = (date) => {
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date));
};

onMounted(() => {
  loadOrders();
});
</script>

<style scoped>
.order-list-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

:deep(.p-card) {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
  background-color: #f9fafb;
  border-color: #e5e7eb;
  font-weight: 600;
  color: #374151;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
  border-color: #e5e7eb;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
  background-color: #f9fafb;
}
</style>