<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedWarehouses" :value="warehouses" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['name']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Depósitos</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'warehouses.create'})">Novo depósito</Button>
                    </div>
                </div>
            </template>
            
            <Column field="name" header="Nome">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="pi pi-building text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold">{{ slotProps.data.name }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="status" header="Status">
                <template #body="slotProps">
                    <Tag :value="getStatusLabel(slotProps.data.status)" :severity="getStatusSeverity(slotProps.data.status)" />
                </template>
            </Column>
            
            <Column field="docks_count" header="Docas">
                <template #body="slotProps">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-truck text-gray-500"></i>
                        <span>{{ slotProps.data.docks_count || 0 }}</span>
                    </div>
                </template>
            </Column>

            <Column field="docks_str" header="Docas Vinculadas">
                <template #body="slotProps">
                    <div class="max-w-xs truncate">
                        {{ slotProps.data.docks_str || 'Nenhuma doca vinculada' }}
                    </div>
                </template>
            </Column>
            
            <Column header="Ações">
                <template #body="slotProps">
                    <div class="flex gap-2">
                        <Button 
                            icon="pi pi-pencil" 
                            size="small" 
                            severity="secondary"
                            @click="$router.push({ name: 'warehouses.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteWarehouse(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} depósitos encontrados. </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Button from "primevue/button";
import Tag from "primevue/tag";
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

const toast = useToast();
const selectedWarehouses = ref();
const filters = ref();
const warehouses = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadWarehouses();
});

const loadWarehouses = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/warehouses', { params });
        
        // Handle raw paginated response from Laravel
        warehouses.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading warehouses:', error);
        warehouses.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadWarehouses(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadWarehouses(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadWarehouses(1, searchQuery.value);
};

const getStatusLabel = (status) => {
    return status ? 'Ativo' : 'Inativo';
};

const getStatusSeverity = (status) => {
    return status ? 'success' : 'secondary';
};

const deleteWarehouse = async (warehouseId) => {
    if (confirm('Tem certeza que deseja excluir este depósito?')) {
        try {
            await axios.delete(`/api/warehouses/${warehouseId}`);
            
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Depósito excluído com sucesso!',
                life: 3000
            });
            
            // Reload warehouses after deletion
            loadWarehouses(currentPage.value, searchQuery.value);
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