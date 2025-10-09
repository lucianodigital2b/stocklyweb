<template>
    <div class="card">
        <DataTable 
            v-model:filters="filters" 
            v-model:selection="selectedCategories" 
            :value="categories" 
            paginator 
            :rows="perPage" 
            dataKey="id" 
            filterDisplay="menu"
            :globalFilterFields="['name']" 
            :loading="loading" 
            :totalRecords="totalRecords" 
            :lazy="true" 
            @page="onPage" 
            @filter="onFilter"
        >
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Categorias</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'categories.create'})">Nova categoria</Button>
                    </div>
                </div>
            </template>


            <Column field="products_count" header="Produtos">
                <template #body="slotProps">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ slotProps.data.products_count || 0 }}</span>
                        <span class="text-sm text-gray-500">produtos</span>
                    </div>
                </template>
            </Column>
            
            <Column field="status" header="Status">
                <template #body="slotProps">
                    <Tag :value="getStatusLabel(slotProps.data.status)" :severity="getStatusSeverity(slotProps.data.status)" />
                </template>
            </Column>
            
            <Column field="created_at" header="Criado em">
                <template #body="slotProps">
                    <div class="text-sm">
                        {{ formatDate(slotProps.data.created_at) }}
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
                            @click="$router.push({ name: 'categories.edit', params: { id: slotProps.data.id } })"
                            title="Editar categoria"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteCategory(slotProps.data.id)"
                            title="Excluir categoria"
                        />
                    </div>
                </template>
            </Column>

            <template #footer> 
                {{ totalRecords }} {{ totalRecords === 1 ? 'categoria encontrada' : 'categorias encontradas' }}. 
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Button from "primevue/button";
import Tag from "primevue/tag";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

const toast = useToast();
const selectedCategories = ref();
const filters = ref();
const categories = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadCategories();
});

const loadCategories = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/categories', { params });
        
        // Handle raw paginated response from Laravel
        categories.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading categories:', error);
        categories.value = [];
        totalRecords.value = 0;
        
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Falha ao carregar categorias. Tente novamente.',
            life: 5000
        });
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadCategories(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadCategories(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadCategories(1, searchQuery.value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
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
        'inactive': 'secondary'
    };
    return severityMap[status] || 'info';
};

const truncateText = (text, maxLength) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};

const deleteCategory = async (categoryId) => {
    if (confirm('Tem certeza que deseja excluir esta categoria?')) {
        try {
            await axios.delete(`/api/categories/${categoryId}`);
            
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Categoria excluída com sucesso!',
                life: 3000
            });
            
            // Reload categories after deletion
            loadCategories(currentPage.value, searchQuery.value);
        } catch (error) {
            console.error('Error deleting category:', error);
            
            let errorMessage = 'Falha ao excluir categoria. Tente novamente.';
            
            if (error.response?.status === 409) {
                errorMessage = 'Não é possível excluir esta categoria pois ela possui produtos associados.';
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: errorMessage,
                life: 5000
            });
        }
    }
};
</script>

<style scoped>
.card {
    padding: 1rem;
}
</style>