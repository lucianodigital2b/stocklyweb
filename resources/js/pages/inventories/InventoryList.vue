<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedInventories" :value="inventories" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['warehouse.name', 'product.name', 'product.sku']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Inventários</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'inventories.create'})">Novo inventário</Button>
                    </div>
                </div>
            </template>
            
            <Column field="product.name" header="Produto">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                      
                        <div>
                            <div class="font-semibold">{{ slotProps.data.product?.name || 'Produto não encontrado' }}</div>
                            <div class="text-sm text-gray-500">SKU: {{ slotProps.data.product?.sku || 'N/A' }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="warehouse.name" header="Depósito">
                <template #body="slotProps">
                    <div class="flex gap-2 items-center">
                        <span>{{ slotProps.data.warehouse?.name || 'Depósito não encontrado' }}</span>
                    </div>
                </template>
            </Column>

            <Column field="stock" header="Estoque">
                <template #body="slotProps">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold" :class="getStockClass(slotProps.data.stock)">
                            {{ slotProps.data.is_infinite ? '∞' : slotProps.data.stock }}
                        </span>
                        <Tag v-if="slotProps.data.is_infinite" value="Infinito" severity="info" />
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
                            @click="$router.push({ name: 'inventories.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteInventory(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} inventários encontrados. </template>
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
const selectedInventories = ref();
const filters = ref();
const inventories = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
let searchTimeout = null;

onMounted(() => {
    loadInventories();
});

const loadInventories = async (page = 1, search = '') => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: perPage.value
        };
        
        if (search) {
            params.q = search;
        }
        
        const response = await axios.get('/api/inventories', { params });
        
        // Handle raw paginated response from Laravel
        inventories.value = response.data.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error loading inventories:', error);
        inventories.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based indexing
    currentPage.value = page;
    loadInventories(page, searchQuery.value);
};

const onSearch = () => {
    // Debounce search to avoid too many API calls
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        loadInventories(1, searchQuery.value);
    }, 500);
};

const onFilter = () => {
    // Handle filtering if needed
    loadInventories(1, searchQuery.value);
};

const getStockClass = (stock) => {
    if (stock === 0) return 'text-red-600';
    if (stock < 10) return 'text-orange-600';
    return 'text-green-600';
};

const deleteInventory = async (inventoryId) => {
    if (confirm('Tem certeza que deseja excluir este inventário?')) {
        try {
            await axios.delete(`/api/inventories/${inventoryId}`);
            
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Inventário excluído com sucesso',
                life: 3000
            });
            
            // Reload the current page
            loadInventories(currentPage.value, searchQuery.value);
        } catch (error) {
            console.error('Error deleting inventory:', error);
            
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: 'Erro ao excluir inventário',
                life: 3000
            });
        }
    }
};
</script>