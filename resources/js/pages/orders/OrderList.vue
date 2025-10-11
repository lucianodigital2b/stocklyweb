<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedOrders" :value="orders" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['id', 'customer.name', 'customer.email']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Pedidos</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push('/orders/create')">Novo pedido</Button>

                    </div>

                </div>
                
            </template>
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
                  size="small" 
                  severity="secondary"
                  @click="viewOrder(data.id)"
                />
                <Button 
                  icon="pi pi-pencil" 
                  size="small" 
                  severity="secondary"
                  @click="editOrder(data.id)"
                />
                <Button 
                  icon="pi pi-trash" 
                  size="small" 
                  severity="danger"
                  @click="deleteOrder(data.id)"
                />
              </div>
            </template>
          </Column>
          <template #footer> {{ totalRecords }} pedidos encontrados. </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

// Components
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const router = useRouter();
const toast = useToast();

// Reactive data
const selectedOrders = ref();
const filters = ref();
const orders = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

// Methods
const loadOrders = async (page = 1, search = '') => {
  loading.value = true;
  
  try {
    const params = {
      page: page,
      per_page: perPage.value
    };
    
    if (search) {
      params.q = search;
    }
    
    const { data } = await axios.get('/api/orders', { params });
    
    orders.value = data.data || [];
    totalRecords.value = data.total || 0;
    currentPage.value = data.current_page || page;
  } catch (error) {
    console.error('Error loading orders:', error);
    orders.value = [];
    totalRecords.value = 0;
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao carregar pedidos',
      life: 3000
    });
  } finally {
    loading.value = false;
  }
};

const onPage = (event) => {
  const page = event.page + 1; // PrimeVue uses 0-based indexing
  currentPage.value = page;
  loadOrders(page, searchQuery.value);
};

const onSearch = () => {
  // Debounce search to avoid too many API calls
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    loadOrders(1, searchQuery.value);
  }, 500);
};

const onFilter = () => {
  // Handle filtering if needed
  loadOrders(1, searchQuery.value);
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
      detail: 'Pedido excluído com sucesso',
      life: 3000
    });
    
    // Reload orders after deletion
    loadOrders(currentPage.value, searchQuery.value);
  } catch (error) {
    console.error('Error deleting order:', error);
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Falha ao excluir pedido. Tente novamente.',
      life: 5000
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
</style>