
<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedCustomers" :value="products" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['name', 'sku', 'price']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Produtos</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'products.create'})">Novo produto</Button>

                    </div>

                </div>
                
            </template>
            <Column field="name" header="Nome">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                        <div class="w-24 h-16 bg-gray-200 rounded flex items-center justify-center">
                            <i class="pi pi-image text-gray-400"></i>
                        </div>
                        <div>
                            <div class="font-semibold">{{ slotProps.data.name }}</div>
                            <div class="text-sm text-gray-500">SKU: {{ slotProps.data.sku }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="price" header="Preço">
                <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.price || 0) }}
                </template>
            </Column>
            <Column field="description" header="Descrição">
                <template #body="slotProps">
                    <div class="max-w-xs truncate">
                        {{ slotProps.data.description || 'Sem descrição' }}
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
                            @click="$router.push({ name: 'products.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteProduct(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} produtos encontrados. </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Button from "primevue/button";
import axios from 'axios';

const selectedCustomers = ref();
const filters = ref();
const products = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadProducts();
});

const loadProducts = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/products', { params });
        
        // Handle raw paginated response from Laravel
        products.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading products:', error);
        products.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadProducts(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadProducts(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadProducts(1, searchQuery.value);
};

const formatCurrency = (value) => {
    return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const deleteProduct = async (productId) => {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        try {
            await axios.delete(`/api/products/${productId}`);
            // Reload products after deletion
            loadProducts(currentPage.value, searchQuery.value);
        } catch (error) {
            console.error('Error deleting product:', error);
            alert('Falha ao excluir produto. Tente novamente.');
        }
    }
};
</script>
