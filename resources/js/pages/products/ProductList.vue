
<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedCustomers" :value="products" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['name', 'category', 'price']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Products</span>

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
            <Column field="name" header="Name">
            
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                        <img :src="`https://primefaces.org/cdn/primevue/images/product/${slotProps.data.image}`" :alt="slotProps.data.image" class="w-24 rounded" />
                        <div>{{slotProps.data.name}}</div>
                    </div>
                </template>
            </Column>

            <Column field="price" header="Price">
                <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.price) }}
                </template>
            </Column>
            <Column field="category" header="Category"></Column>
            <Column field="rating" header="Reviews">
                <template #body="slotProps">
                    <Rating :modelValue="slotProps.data.rating" readonly />
                </template>
            </Column>
            <Column header="Status">
                <template #body="slotProps">
                    <Tag :value="slotProps.data.inventoryStatus" :severity="getSeverity(slotProps.data)" />
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
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};

const getSeverity = (product) => {
    switch (product.inventoryStatus) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warn';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
};
</script>
