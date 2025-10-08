<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedSuppliers" :value="suppliers" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['name', 'email', 'phone', 'number']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Fornecedores</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'suppliers.create'})">Novo fornecedor</Button>

                    </div>

                </div>
                
            </template>
            <Column field="name" header="Nome">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="pi pi-building text-gray-400"></i>
                        </div>
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

            <Column field="number" header="Número">
                <template #body="slotProps">
                    {{ slotProps.data.number || 'Não informado' }}
                </template>
            </Column>
            
            <Column field="city" header="Cidade">
                <template #body="slotProps">
                    {{ slotProps.data.city || 'Não informado' }}
                </template>
            </Column>
            
            <Column field="created_at" header="Cadastrado em">
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.created_at) }}
                </template>
            </Column>
            
            <Column field="cost_center" header="Centro de Custo">
                <template #body="slotProps">
                    <div class="max-w-xs truncate">
                        {{ slotProps.data.cost_center?.name || 'Não informado' }}
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
                            @click="$router.push({ name: 'suppliers.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteSupplier(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} fornecedores encontrados. </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Button from "primevue/button";
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

const toast = useToast();
const selectedSuppliers = ref();
const filters = ref();
const suppliers = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadSuppliers();
});

const loadSuppliers = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/suppliers', { params });
        
        // Handle raw paginated response from Laravel
        suppliers.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading suppliers:', error);
        suppliers.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadSuppliers(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadSuppliers(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadSuppliers(1, searchQuery.value);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const deleteSupplier = async (supplierId) => {
    if (confirm('Tem certeza que deseja excluir este fornecedor?')) {
        try {
            await axios.delete(`/api/suppliers/${supplierId}`);
            
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Fornecedor excluído com sucesso!',
                life: 3000
            });
            
            // Reload suppliers after deletion
            loadSuppliers(currentPage.value, searchQuery.value);
        } catch (error) {
            console.error('Error deleting supplier:', error);
            
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Falha ao excluir fornecedor. Tente novamente.',
                life: 5000
            });
        }
    }
};
</script>