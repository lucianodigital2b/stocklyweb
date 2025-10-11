<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedCustomers" :value="customers" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['name', 'email', 'phone', 'document_number']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Clientes</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'customers.create'})">Novo cliente</Button>

                    </div>

                </div>
                
            </template>
            <Column field="name" header="Nome">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                        <div>
                            <div class="font-semibold">{{ slotProps.data.name }}</div>
                            <div class="text-sm text-gray-500">{{ slotProps.data.email }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="phone" header="Telefone">
                <template #body="slotProps">
                    {{ slotProps.data.phone || 'Não informado' }}
                </template>
            </Column>

            <Column field="document_number" header="CPF/CNPJ">
                <template #body="slotProps">
                    {{ slotProps.data.document_number || 'Não informado' }}
                </template>
            </Column>
            
            <Column field="orders_count" header="Pedidos">
                <template #body="slotProps">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">
                            {{ slotProps.data.orders_count || 0 }}
                        </span>
                        <i v-if="(slotProps.data.orders_count || 0) > 0" class="pi pi-shopping-cart text-blue-500" title="Cliente com pedidos"></i>
                    </div>
                </template>
            </Column>
            
            <Column field="created_at" header="Cadastrado em">
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.created_at) }}
                </template>
            </Column>
            
            <Column header="Ações">
                <template #body="slotProps">
                    <div class="flex gap-2">
                        <Button 
                            icon="pi pi-pencil" 
                            size="small" 
                            severity="secondary"
                            @click="$router.push({ name: 'customers.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteCustomer(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} clientes encontrados. </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Button from "primevue/button";
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

const toast = useToast();
const selectedCustomers = ref();
const filters = ref();
const customers = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadCustomers();
});

const loadCustomers = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/customers', { params });
        
        // Handle raw paginated response from Laravel
        customers.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading customers:', error);
        customers.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadCustomers(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadCustomers(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadCustomers(1, searchQuery.value);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const deleteCustomer = async (customerId) => {
    if (confirm('Tem certeza que deseja excluir este cliente?')) {
        try {
            await axios.delete(`/api/customers/${customerId}`);
            
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Cliente excluído com sucesso!',
                life: 3000
            });
            
            // Reload customers after deletion
            loadCustomers(currentPage.value, searchQuery.value);
        } catch (error) {
            console.error('Error deleting customer:', error);
            
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Falha ao excluir cliente. Tente novamente.',
                life: 5000
            });
        }
    }
};
</script>