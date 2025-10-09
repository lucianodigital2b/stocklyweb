<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedCostCenters" :value="costCenters" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['name', 'description', 'code']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Centros de Custo</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'cost-centers.create'})">Novo centro de custo</Button>

                    </div>

                </div>
                
            </template>
            <Column field="name" header="Nome">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                 
                        <div>
                            <div class="font-semibold">{{ slotProps.data.name }}</div>
                            <div class="text-sm text-gray-500">{{ slotProps.data.code || '' }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="description" header="Descrição">
                <template #body="slotProps">
                    <div class="max-w-xs truncate">
                        {{ slotProps.data.description }}
                    </div>
                </template>
            </Column>

            <Column field="code" header="Código">
                <template #body="slotProps">
                    {{ slotProps.data.code }}
                </template>
            </Column>
            
            <Column field="status" header="Status">
                <template #body="slotProps">
                    <Tag 
                        :value="getStatusLabel(slotProps.data.status)" 
                        :severity="getStatusSeverity(slotProps.data.status)"
                    />
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
                            @click="$router.push({ name: 'cost-centers.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteCostCenter(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} centros de custo encontrados. </template>
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
const selectedCostCenters = ref();
const filters = ref();
const costCenters = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadCostCenters();
});

const loadCostCenters = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/cost-centers', { params });
        
        // Handle raw paginated response from Laravel
        costCenters.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading cost centers:', error);
        costCenters.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadCostCenters(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadCostCenters(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadCostCenters(1, searchQuery.value);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const getStatusLabel = (status) => {
    const statusMap = {
        'active': 'Ativo',
        'inactive': 'Inativo'
    };
    return statusMap[status] || status;
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'active': 'success',
        'inactive': 'danger'
    };
    return severityMap[status] || 'info';
};

const deleteCostCenter = async (costCenterId) => {
    if (confirm('Tem certeza que deseja excluir este centro de custo?')) {
        try {
            await axios.delete(`/api/cost-centers/${costCenterId}`);
            
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Centro de custo excluído com sucesso!',
                life: 3000
            });
            
            // Reload cost centers after deletion
            loadCostCenters(currentPage.value, searchQuery.value);
        } catch (error) {
            console.error('Error deleting cost center:', error);
            
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Falha ao excluir centro de custo. Tente novamente.',
                life: 5000
            });
        }
    }
};
</script>