<template>
  <div class="product-form-container">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoadingCategories">
        <FormSkeleton />
      </template>

      <!-- Form Content -->
      <template v-else>
        <div class="grid">
          <!-- Left Side - Form Fields -->
          <div class="col-12 md:col-7">
            <Card class="form-card">
              <template #content>
                <h3 class="mb-4">{{ pageTitle }}</h3>
                
                <!-- Product Name -->
                <div class="field mb-4">
                  <Label for="name" :required="true">
                    Nome do Produto
                  </Label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    placeholder="Digite o nome do produto"
                    class="w-full"
                    :class="{ 'p-invalid': errors.name }"
                  />
                  <small v-if="errors.name" class="p-error text-red-600">{{ errors.name }}</small>
                </div>

                <!-- Description -->
                <div class="field mb-4">
                  <Label for="description">
                    Descrição
                  </Label>
                  <Textarea 
                    id="description" 
                    v-model="formData.description" 
                    rows="4"
                    class="w-full"
                    placeholder="Descrição do produto..."
                    :maxlength="500"
                  />
                  <small class="text-color-secondary">Caracteres: {{ formData.description.length }}/500</small>
                </div>

                 <!-- Price -->
                <div class="field mb-4">
                  <Label for="price" :required="true">
                    Preço
                  </Label>
                  <InputNumber 
                    id="price" 
                    v-model="formData.price" 
                    mode="currency" 
                    currency="BRL" 
                    locale="pt-BR"
                    placeholder="R$0,00"
                    class="w-full"
                    :class="{ 'p-invalid': errors.price }"
                  />
                  <small v-if="errors.price" class="p-error">{{ errors.price }}</small>
                </div>


                <!-- Inventory Section -->
                <div class="inventory-section mb-4">
                  <h4 class="mb-3">Inventário</h4>
                  
                  <!-- SKU -->
                  <div class="field mb-4">
                    <Label for="sku" required>SKU</Label>
                    <InputText 
                      id="sku"
                      v-model="formData.sku" 
                      placeholder="Digite o SKU do produto"
                      :class="{ 'p-invalid': errors.sku }"
                      class="w-full"
                    />
                    <small class="text-gray-500">Digite o SKU do produto.</small>
                    <small v-if="errors.sku" class="p-error">{{ errors.sku }}</small>
                  </div>

                  <!-- Barcode -->
                  <div class="field mb-4">
                    <Label for="ean">Código de Barras</Label>
                    <InputText 
                      id="ean"
                      v-model="formData.ean" 
                      placeholder="Digite o código de barras"
                      class="w-full"
                    />
                    <small class="text-gray-500">Digite o número do código de barras do produto.</small>
                  </div>

                  <!-- Quantity -->
                  <div class="field mb-4">
                    <Label for="quantity">Quantidade</Label>
                    <div class="">
                      <div>
                        <InputNumber 
                          id="quantity_shelf"
                          v-model="formData.stock" 
                          placeholder="Na prateleira"
                          :min="0"
                          class="w-full"
                        />
                      </div>
                     
                    </div>
                    <small class="text-gray-500">Digite a quantidade do produto.</small>
                  </div>

                  <!-- Allow Backorders -->
                  <div class="field mb-4">
                    <div class="flex align-items-center gap-3">
                      <Checkbox 
                        id="allow_backorders"
                        v-model="formData.allow_backorders" 
                        :binary="true"
                      />
                      <div>
                        <Label for="allow_backorders">Permitir Pedidos sem estoque</Label>
                        <small class="text-gray-500">Permitir que os clientes comprem produtos que estão fora de estoque.</small>

                      </div>
                    </div>
                  </div>
                </div>

               
               
       
              </template>
            </Card>
          </div>

          <!-- Divider -->
          <div class="col-12 md:col-1 flex align-items-center justify-content-center">
            <div class="form-divider"></div>
          </div>

          <!-- Right Side - Product Info & Featured Image -->
          <div class="col-12 md:col-4">
            <!-- Product Status Card -->
            <ToggleCard 
              title="Publicar"
              subtitle="Escolha quando este produto deve ir ao ar"
              :toggleable="true"
              class="mb-4"
            >
              <div class="product-status-fields">
                <!-- Estado -->
                <div class="status-field mb-3">
                  <div class="status-row">
                    <span class="status-label">Estado:</span>
                    <SelectButton 
                      v-model="formData.status" 
                      :options="statusOptions" 
                      optionLabel="label" 
                      optionValue="value"
                      class="status-select-button"
                    />
                  </div>
                </div>

              

                <!-- Publicado em -->
                <div class="status-field mb-3">
                  <div class="status-row">
                    <span class="status-label">Publicado em:</span>
                    <span class="status-value">{{ formattedPublishedDate }}</span>
                  </div>
                </div>

                <!-- Atualizado em -->
                <div class="status-field">
                  <div class="status-row">
                    <span class="status-label">Atualizado em:</span>
                    <span class="status-value">{{ formattedUpdatedDate }}</span>
                  </div>
                </div>
                         <!-- Submit Button -->
                <Button 
                  type="submit" 
                  :label="submitButtonLabel" 
                  class="mt-5" 
                  icon="pi pi-check"
                  :disabled="isSubmitting || isLoadingCategories || isLoadingProduct"
                  :loading="isSubmitting"
                />
              </div>
            </ToggleCard>

             <ToggleCard 
              title="Categorias"
              :toggleable="true"
              class="mb-4"
            >
              <div class="product-status-fields">
                 <!-- Category -->
                <div class="field mb-4">
                  <Label for="category">
                    Categoria
                  </Label>
                  <TreeSelect 
                    id="category" 
                    v-model="formData.category" 
                    :options="categoriesTree" 
                    placeholder="Selecione uma categoria"
                    class="w-full"
                    :class="{ 'p-invalid': errors.category }"
                  />
                  <small v-if="errors.category" class="p-error">{{ errors.category }}</small>
                </div>

                <!-- Tags -->
                <!-- <div class="field mb-4">
                  <Label for="tags">
                    Tags
                  </Label>
                  <MultiSelect 
                    id="tags" 
                    v-model="formData.tags" 
                    :options="availableTags" 
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Selecione tags"
                    class="w-full"
                    :maxSelectedLabels="3"
                    selectedItemsLabel="{0} tags selecionadas"
                  />
                  <small class="text-color-secondary">Adicione tags para facilitar a busca</small>
                </div> -->


              </div>

            </ToggleCard>


            <div class="featured-image-section">
              <h4 class="mb-3">Imagem em Destaque</h4>
              
              <!-- Image Upload Area -->
              <div class="image-upload-container">
                <div 
                  v-if="!featuredImage" 
                  class="upload-placeholder"
                  @click="triggerFileInput"
                  @dragover.prevent
                  @drop.prevent="handleDrop"
                >
                  <i class="pi pi-cloud-upload text-4xl text-gray-400 mb-3"></i>
                  <p class="text-gray-600 mb-2">Clique para fazer upload ou arraste uma imagem</p>
                  <small class="text-gray-500">PNG, JPG até 10MB</small>
                </div>
                
                <div v-else class="image-preview-container">
                  <img :src="featuredImagePreview" alt="Preview" class="featured-image-preview" />
                  <div class="image-overlay">
                    <Button 
                      icon="pi pi-pencil" 
                      class="p-button-rounded p-button-secondary p-button-sm mr-2"
                      @click="triggerFileInput"
                    />
                    <Button 
                      icon="pi pi-trash" 
                      class="p-button-rounded p-button-danger p-button-sm"
                      @click="removeFeaturedImage"
                    />
                  </div>
                </div>
                
                <input 
                  ref="fileInput"
                  type="file" 
                  accept="image/*" 
                  @change="handleFileSelect"
                  style="display: none"
                />
              </div>
            </div>
          </div>
        </div>
      </template>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed, onMounted } from 'vue';
import { useAxios } from '@vueuse/integrations/useAxios';
import { useToast } from 'primevue/usetoast';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../store/modules/auth';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import TreeSelect from 'primevue/treeselect';
import MultiSelect from 'primevue/multiselect';
import SelectButton from 'primevue/selectbutton';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';
import Label from '../../components/Label.vue';
import ToggleCard from '../../components/InfoCard.vue';
import axios from '../../plugins/axios';

const toast = useToast();
const route = useRoute();
const router = useRouter();

// Check if we're in edit mode
const isEditMode = computed(() => !!route.params.id);
const productId = computed(() => route.params.id);

const formData = reactive({
  name: '',
  sku: '',
  price: null,
  category: null,
  stock: 0,
  tags: [],
  description: '',
  inStock: false,
  status: 'draft',
  ean: '',
  stock: 0,
  allow_backorders: false
});

const errors = reactive({
  name: '',
  sku: '',
  price: '',
  category: ''
});

// Featured Image Upload
const featuredImage = ref(null);
const featuredImagePreview = ref('');
const fileInput = ref(null);

// Status options based on Shopify product states
const statusOptions = ref([
  { label: 'Ativo', value: 'active' },
  { label: 'Rascunho', value: 'draft' },
  { label: 'Arquivado', value: 'archived' }
]);

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    handleImageFile(file);
  }
};

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    handleImageFile(file);
  }
};

const handleImageFile = (file) => {
  if (file.size > 10 * 1024 * 1024) { // 10MB limit
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Arquivo muito grande. Máximo 10MB.',
      life: 3000
    });
    return;
  }

  featuredImage.value = file;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    featuredImagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeFeaturedImage = () => {
  featuredImage.value = null;
  featuredImagePreview.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Categories API
const { data: categoriesData, isLoading: isLoadingCategories } = useAxios(
  '/api/categories',
  { method: 'GET' },
  axios
);

const categories = ref([]);

// Transform categories to tree structure for TreeSelect
const categoriesTree = computed(() => {
  if (!categories.value.length) return [];
  
  return categories.value.map(category => ({
    key: category.id,
    label: category.nome,
    data: category,
    children: category.subcategorias?.map(sub => ({
      key: sub.id,
      label: sub.nome,
      data: sub
    })) || []
  }));
});

watch(categoriesData, (newVal) => {
  if (newVal?.data) {
    categories.value = newVal.data;
  }
});

// Load product data if in edit mode
const isLoadingProduct = ref(false);

const loadProductData = async () => {
  if (isEditMode.value) {
    isLoadingProduct.value = true;
    try {
      const response = await axios.get(`/api/products/${productId.value}`);
      console.log('Product response:', response); // Debug log
      
      if (response.data) {
        const product = response.data;
        console.log('Product data:', product); // Debug log
        
        // Dynamically map product data to formData fields
        const fieldMappings = {
          name: product.name || '',
          sku: product.sku || '',
          price: product.price || null,
          description: product.description || '',
          status: product.status || 'draft',
          ean: product.ean || '',
          stock: product.stock,
          allow_backorders: product.allow_backorders === 1,
          // Special cases that need custom logic
          category: null, // We'll need to handle categories separately
          tags: [], // We'll need to handle tags separately
        };

        // Only assign fields that exist in formData to avoid adding unwanted properties
        Object.keys(formData).forEach(key => {
          if (fieldMappings.hasOwnProperty(key)) {
            formData[key] = fieldMappings[key];
          }
        });
        
        // Set featured image preview if exists
        if (product.featured_image) {
          featuredImagePreview.value = product.featured_image;
        }

        console.log(formData);
        
      }
    } catch (error) {
      console.error('Error loading product:', error);
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Falha ao carregar dados do produto',
        life: 5000
      });
    } finally {
      isLoadingProduct.value = false;
    }
  }
};

onMounted(() => {
  loadProductData();
});

// Form submission
const isSubmitting = ref(false);

const validateForm = () => {
  let valid = true;
  
  // Clear previous errors
  errors.name = '';
  errors.sku = '';
  errors.price = '';
  errors.category = '';
  
  // Validate required fields
  if (!formData.name.trim()) {
    errors.name = 'Nome do produto é obrigatório';
    valid = false;
  }

  
  if (formData.price === null || formData.price <= 0) {
    errors.price = 'Preço é obrigatório e deve ser maior que zero';
    valid = false;
  }
  

  return valid;
};

const handleSubmit = async () => {
  console.log('Form submitted', formData); // Debug log
  
  if (!validateForm()) {
    console.log('Validation failed', errors); // Debug log
    return;
  }

  isSubmitting.value = true;

  try {
    const submitData = new FormData();
    
    // Define field mappings for backend compatibility (only for fields that need different names)
    const fieldMappings = {
      category: 'category_id',
    };

    // Define fields that need special handling or should be excluded
    const excludeFields = ['tags', 'inStock']; // inStock is computed, tags handled separately
    
    // Automatically convert formData to FormData using Object.entries
    Object.entries(formData).forEach(([key, value]) => {
      if (excludeFields.includes(key)) return;
      
      const backendFieldName = fieldMappings[key] || key;
      
      // Handle different data types
      if (value === null || value === undefined) {
        submitData.append(backendFieldName, '');
      } else if (typeof value === 'boolean') {
        submitData.append(backendFieldName, value ? '1' : '0');
      } else if (typeof value === 'number') {
        submitData.append(backendFieldName, value.toString());
      } else if (Array.isArray(value)) {
        // Skip arrays here, handle them separately if needed
        return;
      } else {
        submitData.append(backendFieldName, value);
      }
    });
    
    // Handle special fields
    if (formData.tags && formData.tags.length > 0) {
      submitData.append('tags', JSON.stringify(formData.tags));
    }
    
    if (featuredImage.value) {
      submitData.append('featured_image', featuredImage.value);
    }

    console.log('Submitting data:', Object.fromEntries(submitData)); // Debug log

    let response;
    if (isEditMode.value) {
      // For edit mode, use PUT method
      submitData.append('_method', 'PUT');
      response = await axios.post(`/api/products/${productId.value}`, submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    } else {
      // For create mode, use POST method
      response = await axios.post('/api/products', submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    }

    console.log('Response:', response); // Debug log

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: isEditMode.value ? 'Produto atualizado com sucesso!' : 'Produto cadastrado com sucesso!',
      life: 3000
    });

    // Redirect to edit page if creating new product
    if (!isEditMode.value && response.data?.id) {
      router.push({ name: 'products.edit', params: { id: response.data.id } });
    }

  } catch (error) {
    console.error('Submit error:', error); // Debug log
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = validationErrors[field][0];
        }
      });
    }
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || `Falha ao ${isEditMode.value ? 'atualizar' : 'cadastrar'} produto`,
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Computed properties for dynamic content
const pageTitle = computed(() => isEditMode.value ? 'Editar Produto' : 'Cadastrar Produto');
const submitButtonLabel = computed(() => {
  if (isSubmitting.value) {
    return isEditMode.value ? 'Atualizando...' : 'Cadastrando...';
  }
  return isEditMode.value ? 'Atualizar Produto' : 'Cadastrar Produto';
});

// Product status computed properties
const productStatus = computed(() => {
  // You can customize this based on your product status logic
  return formData.inStock ? 'Publicado' : 'Rascunho';
});

const productVisibility = computed(() => {
  // You can customize this based on your product visibility logic
  return 'Público';
});

const formattedPublishedDate = computed(() => {
  // For now, using current date as example
  // You should replace this with actual published_at field from your product data
  const date = new Date();
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const formattedUpdatedDate = computed(() => {
  // For now, using current date as example
  // You should replace this with actual updated_at field from your product data
  const date = new Date();
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

// Methods for edit buttons
const editStatus = () => {
  // Implement status editing logic
  console.log('Edit status clicked');
};

const editVisibility = () => {
  // Implement visibility editing logic
  console.log('Edit visibility clicked');
};
</script>

<style scoped>
.product-form-container {
  margin: 0 auto;
  padding: 2rem;
  max-width: 1380px;
}

.form-divider {
  width: 2px;
  height: 100%;
  background-color: #f3f3f3;
  margin: 0 auto;
  position: absolute;
  top: 0;
}

.featured-image-section {
  padding: 0rem;
}

.image-upload-container {
  position: relative;
}

.upload-placeholder {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f9fafb;
}

.upload-placeholder:hover {
  border-color: #6366f1;
  background: #f0f9ff;
}

.image-preview-container {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.featured-image-preview {
  width: 100%;
  height: 300px;
  object-fit: cover;
  display: block;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-preview-container:hover .image-overlay {
  opacity: 1;
}

.form-card {
  height: fit-content;
  box-shadow: none;
}

.form-card .p-card-content {
}

/* Product Status Card Styles */
.product-status-fields {
  padding: 0;
}

.status-field:last-child {
  padding-bottom: 0;
}

.status-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.status-label {
  font-weight: 500;
  color: #6b7280;
  font-size: 1rem;
  min-width: fit-content;
}

.status-value {
  color: #374151;
  font-size: 1rem;
  flex: 1;
  font-weight: 600;
}

.status-select-button {
  flex: 1;
}

.status-select-button .p-selectbutton .p-button {
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 500;
}

.status-select-button .p-selectbutton .p-button.p-highlight {
  background: var(--p-primary-color);
  border-color: var(--p-primary-color);
  color: var(--p-primary-contrast-color);
}

.status-edit-btn {
  padding: 0.25rem 0.5rem !important;
  font-size: 0.75rem !important;
  height: auto !important;
  min-height: auto !important;
}

@media (max-width: 768px) {
  .form-divider {
    width: 100%;
    height: 1px;
    background-color: #e5e7eb;
    margin: 2rem 0;
  }
  
  .product-form-container {
    padding: 1rem;
  }
}
</style>
