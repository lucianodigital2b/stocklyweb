<template>
    <Dialog 
        :visible="visible" 
        modal 
        :header="modalTitle"
        :style="{ width: '80vw', maxWidth: '1000px' }"
        :closable="true"
        @update:visible="handleVisibilityChange"
        @hide="$emit('close')"
    >
        <div v-if="loading" class="flex justify-center items-center py-8">
            <ProgressSpinner />
        </div>
        
        <div v-else-if="inventory">
            <!-- Inventory Info -->
            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <strong>Produto:</strong> {{ inventory.product_name }}
                    </div>
                    <div v-if="inventory.product_sku">
                        <strong>SKU:</strong> {{ inventory.product_sku }}
                    </div>
                    <div>
                        <strong>Depósito:</strong> {{ inventory.warehouse_name }}
                    </div>
                </div>
                <div class="mt-2">
                    <strong>Estoque Atual:</strong> 
                    <span class="font-semibold" :class="getStockClass(inventory.current_stock)">
                        {{ inventory.is_infinite ? '∞' : inventory.current_stock }}
                    </span>
                    <Tag v-if="inventory.is_infinite" value="Infinito" severity="info" class="ml-2" />
                </div>
            </div>

            <!-- Movements Table -->
            <DataTable 
                :value="movements" 
                :paginator="movements.length > 10"
                :rows="10"
                :loading="loading"
                responsiveLayout="scroll"
                class="p-datatable-sm"
            >
                <Column field="user_name" header="Autor" style="min-width: 120px">
                    <template #body="slotProps">
                        <div>
                            <div class="font-medium">{{ slotProps.data.user_name }}</div>
                            <div class="text-sm text-gray-500">{{ formatDate(slotProps.data.created_at) }}</div>
                        </div>
                    </template>
                </Column>

                <Column field="movement_type" header="Tipo" style="min-width: 120px">
                    <template #body="slotProps">
                        <Tag 
                            :value="formatMovementType(slotProps.data.movement_type)" 
                            :severity="getMovementTypeSeverity(slotProps.data.movement_type)"
                        />
                    </template>
                </Column>

                <Column field="quantity_change" header="Alteração" style="min-width: 100px">
                    <template #body="slotProps">
                        <span 
                            :class="getQuantityChangeClass(slotProps.data.quantity_change)"
                        >
                            {{ formatQuantityChange(slotProps.data.quantity_change) }}
                        </span>
                    </template>
                </Column>

                <Column field="stock_before" header="Estoque Antes" style="min-width: 100px">
                    <template #body="slotProps">
                        <span class="font-medium">
                            {{ slotProps.data.is_infinite_before ? '∞' : slotProps.data.stock_before }}
                        </span>
                    </template>
                </Column>

                <Column field="stock_after" header="Estoque Depois" style="min-width: 100px">
                    <template #body="slotProps">
                        <span class="font-medium">
                            {{ slotProps.data.is_infinite_after ? '∞' : slotProps.data.stock_after }}
                        </span>
                    </template>
                </Column>

                <Column field="is_infinite_before" header="Infinito Antes" style="min-width: 100px">
                    <template #body="slotProps">
                        <Tag 
                            :value="slotProps.data.is_infinite_before ? 'SIM' : 'NÃO'" 
                            :severity="slotProps.data.is_infinite_before ? 'success' : 'secondary'"
                        />
                    </template>
                </Column>

                <Column field="is_infinite_after" header="Infinito Depois" style="min-width: 100px">
                    <template #body="slotProps">
                        <Tag 
                            :value="slotProps.data.is_infinite_after ? 'SIM' : 'NÃO'" 
                            :severity="slotProps.data.is_infinite_after ? 'success' : 'secondary'"
                        />
                    </template>
                </Column>

                <template #empty>
                    <div class="text-center py-4">
                        <p class="text-gray-500">Nenhuma movimentação encontrada</p>
                    </div>
                </template>
            </DataTable>
        </div>

        <template #footer>
            <Button label="Fechar" @click="$emit('close')" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';
import axios from '../plugins/axios';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    inventoryId: {
        type: [String, Number],
        default: null
    }
});

const emit = defineEmits(['close']);

const loading = ref(false);
const inventory = ref(null);
const movements = ref([]);

const modalTitle = computed(() => {
    return inventory.value 
        ? `Histórico de Movimentações - ${inventory.value.product_name}`
        : 'Histórico de Movimentações';
});

const handleVisibilityChange = (newVisible) => {
    if (!newVisible) {
        emit('close');
    }
};

// Watch for changes in visibility and inventoryId
watch(() => props.visible, (newVisible) => {
    if (newVisible && props.inventoryId) {
        loadStockMovements();
    }
});

watch(() => props.inventoryId, (newId) => {
    if (props.visible && newId) {
        loadStockMovements();
    }
});

const loadStockMovements = async () => {
    if (!props.inventoryId) return;
    
    loading.value = true;
    try {
        const response = await axios.get(`/api/inventories/${props.inventoryId}/movements`);
        inventory.value = response.data.inventory;
        movements.value = response.data.movements;
    } catch (error) {
        console.error('Error loading stock movements:', error);
        inventory.value = null;
        movements.value = [];
    } finally {
        loading.value = false;
    }
};

const getStockClass = (stock) => {
    if (stock === 0) return 'text-red-600';
    if (stock < 10) return 'text-orange-600';
    return 'text-green-600';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('pt-BR');
};

const formatMovementType = (type) => {
    const types = {
        'initial': 'Estoque Inicial',
        'adjustment': 'Ajuste',
        'sale': 'Venda',
        'purchase': 'Compra',
        'return': 'Devolução',
        'transfer': 'Transferência',
        'loss': 'Perda',
        'found': 'Encontrado'
    };
    return types[type] || type;
};

const getQuantityChangeClass = (change) => {
    if (change > 0) return 'text-green-600 font-semibold';
    if (change < 0) return 'text-red-600 font-semibold';
    return 'text-gray-600';
};

const formatQuantityChange = (change) => {
    if (change > 0) return `+${change}`;
    return change.toString();
};

const getMovementTypeSeverity = (type) => {
    const severities = {
        'initial_stock': 'info',
        'order_creation': 'danger',
        'order_cancellation': 'success',
        'manual_adjustment': 'warning',
        'inventory_update': 'info',
        'stock_in': 'success',
        'stock_out': 'danger'
    };
    return severities[type] || 'secondary';
};
</script>