<template>
    <div class="card">
        <DataTable v-model:filters="filters" v-model:selection="selectedEntries" :value="entries" paginator :rows="perPage" dataKey="id" filterDisplay="menu"
            :globalFilterFields="['value', 'supplier.name', 'cost_center.name', 'external_code']" :loading="loading" :totalRecords="totalRecords" :lazy="true" @page="onPage" @filter="onFilter">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Lançamentos Financeiros</span>

                    <div class="flex gap-3">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="searchQuery" @input="onSearch" placeholder="Procurar" />
                        </IconField>
                        <Button @click="$router.push({ name: 'entries.create'})">Novo lançamento</Button>

                    </div>

                </div>
                
            </template>
            
            <Column field="value" header="Valor">
                <template #body="slotProps">
                    <div class="flex items-center gap-2">
                        <i :class="getOperationIcon(slotProps.data.operation)" 
                           :style="{ color: getOperationColor(slotProps.data.operation) }"></i>
                        <span :class="getOperationClass(slotProps.data.operation)">
                            {{ formatCurrency(slotProps.data.value || 0) }}
                        </span>
                    </div>
                </template>
            </Column>

            <Column field="operation" header="Operação">
                <template #body="slotProps">
                    <Tag :value="getOperationLabel(slotProps.data.operation)" 
                         :severity="getOperationSeverity(slotProps.data.operation)" />
                </template>
            </Column>
            
            <Column field="supplier" header="Fornecedor">
                <template #body="slotProps">
                    <div class="flex gap-3 items-center">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="pi pi-building text-gray-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="font-medium">{{ slotProps.data.supplier?.name || 'N/A' }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="cost_center" header="Centro de Custo">
                <template #body="slotProps">
                    {{ slotProps.data.cost_center?.name || 'N/A' }}
                </template>
            </Column>
            
            <Column field="payment_method" header="Método de Pagamento">
                <template #body="slotProps">
                    <Tag :value="getPaymentMethodLabel(slotProps.data.payment_method)" 
                         severity="info" />
                </template>
            </Column>
            
            <Column field="due_at" header="Vencimento">
                <template #body="slotProps">
                    <div v-if="slotProps.data.due_at">
                        {{ formatDate(slotProps.data.due_at) }}
                    </div>
                    <span v-else class="text-gray-400">-</span>
                </template>
            </Column>
            
            <Column field="status" header="Status">
                <template #body="slotProps">
                    <Tag :value="getStatusLabel(slotProps.data)" 
                         :severity="getStatusSeverity(slotProps.data)" />
                </template>
            </Column>
            
            <Column field="external_code" header="Código Externo">
                <template #body="slotProps">
                    <div class="max-w-xs truncate">
                        {{ slotProps.data.external_code || '-' }}
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
                            @click="$router.push({ name: 'entries.edit', params: { id: slotProps.data.id } })"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small" 
                            severity="danger"
                            @click="deleteEntry(slotProps.data.id)"
                        />
                    </div>
                </template>
            </Column>
            <template #footer> {{ totalRecords }} lançamentos encontrados. </template>
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
const selectedEntries = ref();
const filters = ref();
const entries = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const searchQuery = ref('');

// Pagination and filtering
const onPage = (event) => {
    loadEntries(event.page + 1, event.rows);
};

const onFilter = (event) => {
    loadEntries(1, perPage.value, event.filters);
};

const onSearch = () => {
    loadEntries(1, perPage.value, null, searchQuery.value);
};

// Load entries from API
const loadEntries = async (page = 1, rows = 10, filters = null, search = '') => {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: rows,
            search
        };

        if (filters) {
            Object.keys(filters).forEach(key => {
                if (filters[key].value) {
                    params[key] = filters[key].value;
                }
            });
        }

        const response = await axios.get('/api/entries', { params });
        entries.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error('Error loading entries:', error);
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Erro ao carregar lançamentos',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

// Delete entry
const deleteEntry = async (id) => {
    if (confirm('Tem certeza que deseja excluir este lançamento?')) {
        try {
            await axios.delete(`/api/entries/${id}`);
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Lançamento excluído com sucesso',
                life: 3000
            });
            loadEntries();
        } catch (error) {
            console.error('Error deleting entry:', error);
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: 'Erro ao excluir lançamento',
                life: 3000
            });
        }
    }
};

// Helper functions for formatting and display
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const getOperationLabel = (operation) => {
    const operations = {
        1: 'Entrada',
        2: 'Saída'
    };
    return operations[operation] || 'N/A';
};

const getOperationSeverity = (operation) => {
    return operation === 1 ? 'success' : 'danger';
};

const getOperationIcon = (operation) => {
    return operation === 1 ? 'pi pi-arrow-up' : 'pi pi-arrow-down';
};

const getOperationColor = (operation) => {
    return operation === 1 ? '#10b981' : '#ef4444';
};

const getOperationClass = (operation) => {
    return operation === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
};

const getPaymentMethodLabel = (method) => {
    const methods = {
        'pix': 'PIX',
        'bank_slip': 'BOLETO',
        'money': 'DINHEIRO',
        'credit': 'CRÉDITO',
        'debit': 'DÉBITO'
    };
    return methods[method] || method || 'N/A';
};

const getStatusLabel = (entry) => {
    if (entry.paid_at) {
        return 'Pago';
    }
    
    if (entry.due_at && new Date(entry.due_at) < new Date()) {
        return 'Em atraso';
    }
    
    return 'Pendente';
};

const getStatusSeverity = (entry) => {
    if (entry.paid_at) {
        return 'success';
    }
    
    if (entry.due_at && new Date(entry.due_at) < new Date()) {
        return 'danger';
    }
    
    return 'info';
};

// Load entries on component mount
onMounted(() => {
    loadEntries();
});
</script>

<style scoped>
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>