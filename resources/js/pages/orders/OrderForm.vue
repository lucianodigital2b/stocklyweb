<template>
  <div class="order-form-container">
    <div class="order-header">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Criar pedido</h1>
          <p class="text-sm text-gray-600 mt-1">Adicione produtos e informações do cliente para criar um novo pedido</p>
        </div>
        <div class="flex gap-3">
          <Button 
            label="Cancelar" 
            severity="secondary" 
            outlined 
            @click="$router.push('/orders')"
          />
          <Button 
            label="Salvar pedido" 
            @click="saveOrder"
            :loading="isSubmitting"
            :disabled="!canSaveOrder"
          />
        </div>
      </div>
    </div>

    <div class="grid">
      <!-- Left Column - Main Content -->
      <div class="col-12 md:col-7">
        <div class="space-y-6">



        <!-- Products Section -->
        <Card class="products-section" ref="productsCard">
          <template #title>
            <div class="flex items-center justify-between">
              <span>Produtos</span>
              <!-- <Button 
                label="Navegar" 
                severity="secondary" 
                outlined 
                size="small"
                @click="showProductBrowser = true"
              /> -->
            </div>
          </template>
          <template #content>
            <div class="space-y-4">
              <!-- Search Products -->

              <IconField>
                <InputIcon class="pi pi-search" />
                <InputText 
                 v-model="productSearch"
                  placeholder="Buscar produtos"
                  class="w-full pl-10"
                  @input="searchProducts"
                />
              </IconField>

              <!-- Product Search Results -->
              <div v-if="searchResults.length > 0" class="border rounded-lg max-h-48 overflow-y-auto">
                <div 
                  v-for="product in searchResults" 
                  :key="product.id"
                  class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
                  @click="addProduct(product)"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="font-medium">{{ product.name }}</p>
                      <p class="text-sm text-gray-600">{{ product.sku }}</p>
                    </div>
                    <div class="text-right">
                      <p class="font-medium">R$ {{ formatPrice(product.price) }}</p>
                      <p class="text-sm text-gray-600">{{ product.stock }} em estoque</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Selected Products -->
              <div v-if="orderItems.length > 0" class="space-y-3">
                <div 
                  v-for="(item, index) in orderItems" 
                  :key="index"
                  class="border rounded-lg p-4 order-item"
                  :ref="el => { if (el) orderItemRefs[index] = el }"
                >
                  <!-- Product Title - Full Width -->
                  <div class="w-full mb-3">
                    <h4 class="font-medium">{{ item.product.name }}</h4>
                    <p class="text-sm text-gray-600">{{ item.product.sku }}</p>
                  </div>
                  
                  <!-- Inputs Row - Below Title -->
                  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 justify-between">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                      <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600 whitespace-nowrap">Qtd:</label>
                        <InputNumber
                          v-model="item.quantity"
                          :min="1"
                          :max="item.product.stock"
                          showButtons
                          buttonLayout="horizontal"
                          class="w-20 sm:w-24"
                          @input="updateItemTotal(index)"
                        />
                      </div>
                      <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600 whitespace-nowrap">Preço:</label>
                        <span class="font-medium text-gray-900">R$ {{ formatPrice(item.price) }}</span>
                      </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                      <div class="text-right">
                        <p class="font-medium">R$ {{ formatPrice(item.total) }}</p>
                      </div>
                      <Button
                        icon="pi pi-trash"
                        severity="danger"
                        text
                        size="small"
                        @click="removeProduct(index)"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="orderItems.length === 0" class="text-center py-8 text-gray-500">
                <i class="pi pi-shopping-cart text-4xl mb-4"></i>
                <p>Adicione um produto para calcular o total e visualizar as opções de pagamento</p>
              </div>
            </div>
          </template>
        </Card>

        <!-- Payment Section -->
        <Card class="payment-section">
          <template #title>Pagamento</template>
          <template #content>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span>Subtotal</span>
                <span>R$ {{ formatPrice(calculations.subtotal) }}</span>
              </div>
              
              <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                  <span>Adicionar desconto</span>
                  <Button 
                    icon="pi pi-minus" 
                    text 
                    size="small"
                    @click="showDiscountDialog = true"
                  />
                </div>
                <span>-R$ {{ formatPrice(calculations.discount) }}</span>
              </div>
              
              <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                  <span>Adicionar frete ou entrega</span>
                  <Button 
                    icon="pi pi-minus" 
                    text 
                    size="small"
                    @click="showShippingDialog = true"
                  />
                </div>
                <span>R$ {{ formatPrice(calculations.shipping) }}</span>
              </div>
              
              <div class="flex justify-between items-center text-sm text-gray-600">
                <span>Taxa estimada</span>
                <span>{{ calculations.tax > 0 ? 'R$ ' + formatPrice(calculations.tax) : 'Não calculado' }}</span>
              </div>
              
              <hr class="my-4">
              
              <div class="flex justify-between items-center text-lg font-semibold">
                <span>Total</span>
                <span>R$ {{ formatPrice(calculations.total) }}</span>
              </div>
            </div>
          </template>
        </Card>
        </div>
      </div>

      <!-- Divider -->
      <div class="col-12 md:col-1 flex align-items-center justify-content-center">
        <div class="form-divider"></div>
      </div>

      <!-- Right Column - Sidebar -->
      <div class="col-12 md:col-4">
        <div class="space-y-6">
        <!-- Notes -->

                <!-- Order Status -->
        <Card>
          <template #title>Status do Pedido</template>
          <template #content>
            <Dropdown
              v-model="orderStatus"
              :options="statusOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Selecionar status"
              class="w-full"
            />
          </template>
        </Card>

        <Card>
          <template #title>
            <div class="flex items-center justify-between">
              <span>Observações</span>
              <Button icon="pi pi-pencil" text size="small" />
            </div>
          </template>
          <template #content>
            <Textarea
              v-model="orderNotes"
              placeholder="Sem observações"
              rows="3"
              class="w-full"
            />
          </template>
        </Card>

        <!-- Customer -->
        <Card>
          <template #title>Cliente</template>
          <template #content>
            <div class="space-y-4">

              <IconField>
                <InputIcon class="pi pi-search" />
                <InputText 
                  v-model="customerSearch"
                  placeholder="Buscar cliente"
                  class="w-full pl-10"
                  @input="searchCustomers"
                />
              </IconField>

              <!-- Customer Search Results -->
              <div v-if="customerSearchResults.length > 0" class="border rounded-lg max-h-48 overflow-y-auto">
                <div 
                  v-for="customer in customerSearchResults" 
                  :key="customer.id"
                  class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
                  @click="selectCustomer(customer)"
                >
                  <div>
                    <p class="font-medium">{{ customer.name }}</p>
                    <p class="text-sm text-gray-600">{{ customer.email }}</p>
                  </div>
                </div>
              </div>

              <!-- Selected Customer -->
              <div v-if="selectedCustomer" class="border rounded-lg p-3">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-medium">{{ selectedCustomer.name }}</p>
                    <p class="text-sm text-gray-600">{{ selectedCustomer.email }}</p>
                  </div>
                  <Button
                    icon="pi pi-times"
                    text
                    size="small"
                    @click="selectedCustomer = null"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>


        <!-- Order History -->
        <Card v-if="isEditMode">
          <template #title>Histórico do Pedido</template>
          <template #content>
            <div class="space-y-3">
              <div 
                v-for="entry in orderHistory" 
                :key="entry.id"
                class="border-l-2 border-blue-200 pl-3"
              >
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium">{{ entry.description }}</p>
                  <span class="text-xs text-gray-500">{{ formatDate(entry.created_at) }}</span>
                </div>
                <p class="text-xs text-gray-600">{{ entry.user?.name || 'Sistema' }}</p>
              </div>
              
              <!-- Add History Entry -->
              <div class="pt-3 border-t">
                <div class="flex gap-2">
                  <InputText
                    v-model="newHistoryEntry"
                    placeholder="Adicionar nota..."
                    class="flex-1"
                    size="small"
                  />
                  <Button
                    icon="pi pi-plus"
                    size="small"
                    @click="addHistoryEntry"
                    :disabled="!newHistoryEntry.trim()"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>
        </div>
      </div>
    </div>

    <!-- Discount Dialog -->
    <Dialog 
      v-model:visible="showDiscountDialog" 
      modal 
      header="Adicionar desconto"
      class="w-96"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Tipo de desconto</label>
          <SelectButton
            v-model="discountType"
            :options="[{label: 'Valor fixo', value: 'fixed'}, {label: 'Porcentagem', value: 'percentage'}]"
            optionLabel="label"
            optionValue="value"
            class="w-full"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-2">
            {{ discountType === 'percentage' ? 'Porcentagem' : 'Valor' }}
          </label>
          <InputNumber
            v-model="discountValue"
            :mode="discountType === 'percentage' ? 'decimal' : 'currency'"
            :currency="discountType === 'fixed' ? 'BRL' : undefined"
            :locale="discountType === 'fixed' ? 'pt-BR' : undefined"
            :suffix="discountType === 'percentage' ? '%' : ''"
            class="w-full"
          />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Cancelar" severity="secondary" @click="showDiscountDialog = false" />
          <Button label="Aplicar" @click="applyDiscount" />
        </div>
      </template>
    </Dialog>

    <!-- Shipping Dialog -->
    <Dialog 
      v-model:visible="showShippingDialog" 
      modal 
      header="Adicionar frete"
      class="w-96"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Custo do frete</label>
          <InputNumber
            v-model="shippingCost"
            mode="currency"
            currency="BRL"
            locale="pt-BR"
            class="w-full"
          />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Cancelar" severity="secondary" @click="showShippingDialog = false" />
          <Button label="Aplicar" @click="applyShipping" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';
import { gsap } from 'gsap';

// Components
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import SelectButton from 'primevue/selectbutton';
import Dialog from 'primevue/dialog';

const route = useRoute();
const router = useRouter();
const toast = useToast();

// Reactive data
const isEditMode = computed(() => !!route.params.id);
const orderId = computed(() => route.params.id);

const orderItems = ref([]);
const selectedCustomer = ref(null);
const orderNotes = ref('');
const orderTags = ref('');
const orderStatus = ref('pending');
const orderHistory = ref([]);
const newHistoryEntry = ref('');

// Search states
const productSearch = ref('');
const customerSearch = ref('');
const searchResults = ref([]);
const customerSearchResults = ref([]);

// Dialog states
const showCustomItemDialog = ref(false);
const showDiscountDialog = ref(false);
const showShippingDialog = ref(false);
const showProductBrowser = ref(false);

// Form states
const isSubmitting = ref(false);

// Custom item form
const customItem = reactive({
  name: '',
  price: 0,
  quantity: 1
});

// Discount form
const discountType = ref('fixed');
const discountValue = ref(0);

// Shipping
const shippingCost = ref(0);

// Currency options
const selectedCurrency = ref('BRL');
const currencies = [
  { label: 'Real Brasileiro (BRL R$)', value: 'BRL' }
];

// Status options
const statusOptions = [
  { label: 'Pendente', value: 'pending' },
  { label: 'Processando', value: 'processing' },
  { label: 'Enviado', value: 'shipped' },
  { label: 'Entregue', value: 'delivered' },
  { label: 'Cancelado', value: 'cancelled' }
];

// Calculations
const calculations = computed(() => {
  const subtotal = orderItems.value.reduce((sum, item) => sum + (item.total || 0), 0);
  const discount = calculateDiscount(subtotal);
  const shipping = shippingCost.value || 0;
  const tax = 0; // Not calculated for now
  const total = subtotal - discount + shipping + tax;

  return {
    subtotal,
    discount,
    shipping,
    tax,
    total
  };
});

const canSaveOrder = computed(() => {
  return orderItems.value.length > 0 && selectedCustomer.value;
});

// Methods
const searchProducts = async () => {
  if (productSearch.value.length < 2) {
    searchResults.value = [];
    return;
  }

  try {
    const { data } = await axios.get('/api/products', {
      params: { search: productSearch.value, limit: 10 }
    });
    searchResults.value = data.data || [];
  } catch (error) {
    console.error('Error searching products:', error);
  }
};

const searchCustomers = async () => {
  if (customerSearch.value.length < 2) {
    customerSearchResults.value = [];
    return;
  }

  try {
    const { data } = await axios.get('/api/customers', {
      params: { search: customerSearch.value, limit: 10 }
    });
    customerSearchResults.value = data.data || [];
  } catch (error) {
    console.error('Error searching customers:', error);
  }
};

const addProduct = async (product) => {
  const existingIndex = orderItems.value.findIndex(item => item.product.id === product.id);
  
  if (existingIndex >= 0) {
    orderItems.value[existingIndex].quantity += 1;
    updateItemTotal(existingIndex);
    
    // Animate quantity update
    await nextTick();
    if (orderItemRefs.value[existingIndex]) {
      gsap.fromTo(orderItemRefs.value[existingIndex], 
        { scale: 1 },
        { scale: 1.05, duration: 0.2, yoyo: true, repeat: 1 }
      );
    }
  } else {
    // Animate card height before adding item
    if (productsCard.value && orderItems.value.length === 0) {
      gsap.to(productsCard.value.$el, {
        height: 'auto',
        duration: 0.3,
        ease: "power2.out"
      });
    }
    
    orderItems.value.push({
      product: product,
      quantity: 1,
      price: product.price,
      total: product.price
    });
    
    // Animate new item addition
    await nextTick();
    const newIndex = orderItems.value.length - 1;
    if (orderItemRefs.value[newIndex]) {
      gsap.fromTo(orderItemRefs.value[newIndex], 
        { opacity: 0, y: -20, scale: 0.9 },
        { opacity: 1, y: 0, scale: 1, duration: 0.4, ease: "back.out(1.7)" }
      );
    }
    
    // Animate card height expansion for subsequent items
    if (productsCard.value && orderItems.value.length > 1) {
      gsap.to(productsCard.value.$el, {
        height: 'auto',
        duration: 0.3,
        ease: "power2.out"
      });
    }
  }
  
  productSearch.value = '';
  searchResults.value = [];
};

const removeProduct = async (index) => {
  // Animate removal
  if (orderItemRefs.value[index]) {
    await gsap.to(orderItemRefs.value[index], {
      opacity: 0,
      x: 100,
      scale: 0.8,
      duration: 0.3,
      ease: "power2.in"
    });
  }
  
  orderItems.value.splice(index, 1);
  
  // Re-animate remaining items
  await nextTick();
  orderItemRefs.value.forEach((ref, i) => {
    if (ref) {
      gsap.fromTo(ref, 
        { y: -10 },
        { y: 0, duration: 0.2, delay: i * 0.05 }
      );
    }
  });
  
  // Animate card height contraction after item removal
  await nextTick();
  if (productsCard.value) {
    gsap.to(productsCard.value.$el, {
      height: 'auto',
      duration: 0.3,
      ease: "power2.out"
    });
  }
};

const updateItemTotal = (index) => {
  const item = orderItems.value[index];
  item.total = item.quantity * item.price;
};

const selectCustomer = (customer) => {
  selectedCustomer.value = customer;
  customerSearch.value = '';
  customerSearchResults.value = [];
};

const addCustomItem = () => {
  if (!customItem.name || !customItem.price) return;

  orderItems.value.push({
    product: {
      id: null,
      name: customItem.name,
      sku: 'CUSTOM',
      price: customItem.price
    },
    quantity: customItem.quantity,
    price: customItem.price,
    total: customItem.quantity * customItem.price
  });

  // Reset form
  customItem.name = '';
  customItem.price = 0;
  customItem.quantity = 1;
  showCustomItemDialog.value = false;
};

const calculateDiscount = (subtotal) => {
  if (!discountValue.value) return 0;
  
  if (discountType.value === 'percentage') {
    return (subtotal * discountValue.value) / 100;
  }
  return discountValue.value;
};

const applyDiscount = () => {
  showDiscountDialog.value = false;
};

const applyShipping = () => {
  showShippingDialog.value = false;
};

const addHistoryEntry = async () => {
  if (!newHistoryEntry.value.trim() || !isEditMode.value) return;

  try {
    const { data } = await axios.post(`/api/orders/${orderId.value}/history`, {
      description: newHistoryEntry.value
    });
    
    orderHistory.value.unshift(data.data);
    newHistoryEntry.value = '';
    
    toast.add({
       severity: 'success',
       summary: 'Sucesso',
       detail: 'Entrada de histórico adicionada com sucesso',
       life: 3000
     });
   } catch (error) {
     toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Falha ao adicionar entrada de histórico',
        life: 3000
      });
  }
};

const saveOrder = async () => {
  if (!canSaveOrder.value) return;

  isSubmitting.value = true;

  try {
    const orderData = {
      customer_id: selectedCustomer.value.id,
      status: orderStatus.value,
      total_price: calculations.value.total,
      items: orderItems.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        price: item.price
      })),
      meta: {
        notes: orderNotes.value,
        tags: orderTags.value,
        discount_type: discountType.value,
        discount_value: discountValue.value,
        shipping_cost: shippingCost.value
      }
    };

    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/orders/${orderId.value}`, orderData);
    } else {
      response = await axios.post('/api/orders', orderData);
    }

    toast.add({
       severity: 'success',
       summary: 'Sucesso',
       detail: `Pedido ${isEditMode.value ? 'atualizado' : 'criado'} com sucesso`,
       life: 3000
     });

     router.push('/orders');
   } catch (error) {
     toast.add({
       severity: 'error',
       summary: 'Erro',
       detail: `Falha ao ${isEditMode.value ? 'atualizar' : 'criar'} pedido`
     });
  } finally {
    isSubmitting.value = false;
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price || 0);
};

const formatDate = (date) => {
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date));
};

// Load order data if editing
const loadOrder = async () => {
  if (!isEditMode.value) return;

  try {
    const { data } = await axios.get(`/api/orders/${orderId.value}`);
    const order = data.data;

    selectedCustomer.value = order.customer;
    orderStatus.value = order.status;
    orderItems.value = order.items.map(item => ({
      product: item.product,
      quantity: item.quantity,
      price: item.price,
      total: item.quantity * item.price
    }));

    // Load order history
    const historyResponse = await axios.get(`/api/orders/${orderId.value}/history`);
    orderHistory.value = historyResponse.data.data;
  } catch (error) {
     toast.add({
         severity: 'error',
         summary: 'Erro',
         detail: 'Falha ao carregar pedido',
         life: 3000
       });
  }
};

onMounted(() => {
  loadOrder();
});

// GSAP refs for animations
const orderItemRefs = ref([]);
const productsCard = ref(null);
</script>

<style scoped>
.order-form-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.products-section :deep(.p-card-content) {
  padding-top: 0;
}

.payment-section :deep(.p-card-content) {
  padding-top: 0;
}

/* Custom styling for better Shopify-like appearance */
:deep(.p-card) {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

:deep(.p-card-title) {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

:deep(.p-inputtext) {
  border-color: #d1d5db;
}

.order-form-container {
  padding: 1rem;
}

.form-divider {
  width: 2px;
  height: 200px;
  background: linear-gradient(to bottom, transparent, #e5e7eb, transparent);
}

/* Responsive */
@media (max-width: 768px) {
  .form-divider {
    width: 100%;
    height: 2px;
    margin: 1rem 0;
  }
}

:deep(.p-inputtext:focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}

:deep(.p-button) {
  font-weight: 500;
}

:deep(.p-button.p-button-outlined) {
  border-color: #d1d5db;
  color: #374151;
}

:deep(.p-button.p-button-outlined:hover) {
  background-color: #f9fafb;
  border-color: #9ca3af;
}
</style>