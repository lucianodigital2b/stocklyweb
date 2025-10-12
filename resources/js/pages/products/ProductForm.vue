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
                  <small v-if="errors.name" class="p-error text-red-600">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</small>
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
                  <small class="text-color-secondary">Caracteres: {{ formData.description?.length }}/500</small>
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
                  <small v-if="errors.price" class="p-error">{{ Array.isArray(errors.price) ? errors.price[0] : errors.price }}</small>
                </div>

                <!-- Gallery Section -->
                <div class="gallery-section mb-4">
                  <h4 class="mb-3">Galeria</h4>
                  <p class="text-gray-600 mb-3">A primeira imagem será usada como miniatura do produto.</p>
                  
                  <div class="gallery-grid">
                    <!-- Existing Images -->
                    <div 
                      v-for="(image, index) in galleryImages" 
                      :key="index"
                      class="gallery-item"
                      :class="{ 'is-primary': index === 0 }"
                      @dragstart="handleDragStart(index)"
                      @dragover.prevent
                      @drop.prevent="handleGalleryDrop(index, $event)"
                      draggable="true"
                    >
                      <img :src="image.preview" :alt="`Imagem ${index + 1}`" class="gallery-image" />
                      <div class="gallery-overlay">
                        <span v-if="index === 0" class="primary-badge">Principal</span>
                        <div class="gallery-actions">
                          <Button 
                            icon="pi pi-trash" 
                            class="p-button-rounded p-button-danger p-button-sm"
                            @click="removeGalleryImage(index)"
                          />
                        </div>
                      </div>
                    </div>
                    
                    <!-- Add New Image Button -->
                    <div 
                      class="gallery-item gallery-add-button"
                      @click="triggerGalleryFileInput"
                      @dragover.prevent
                      @drop.prevent="handleGalleryFileDrop"
                    >
                      <i class="pi pi-plus text-3xl text-gray-400"></i>
                      <p class="text-gray-500 text-sm mt-2">Adicionar imagem</p>
                    </div>
                  </div>
                  
                  <input 
                    ref="galleryFileInput"
                    type="file" 
                    accept="image/*" 
                    multiple
                    @change="handleGalleryFileSelect"
                    style="display: none"
                  />
                  
                  <small class="text-gray-500">PNG, JPG até 10MB cada. Arraste para reordenar.</small>
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
                    <small v-if="errors.sku" class="p-error">{{ Array.isArray(errors.sku) ? errors.sku[0] : errors.sku }}</small>
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

          <!-- Right Side - Product Info -->
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

             <!-- <ToggleCard 
              title="Categorias"
              :toggleable="true"
              class="mb-4"
            >
              <div class="product-status-fields">
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
                  <small v-if="errors.category" class="p-error">{{ Array.isArray(errors.category) ? errors.category[0] : errors.category }}</small>
                </div>
              </div>
            </ToggleCard> -->
          </div>
        </div>
      </template>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '../../plugins/axios';

// Components
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';
import TreeSelect from 'primevue/treeselect';
import Checkbox from 'primevue/checkbox';
import Label from '../../components/Label.vue';
import ToggleCard from '../../components/InfoCard.vue';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

// Reactive data
const formData = reactive({
  name: '',
  description: '',
  price: null,
  sku: '',
  ean: '',
  stock: 0,
  allow_backorders: false,
  status: 'draft',
  category: null,
  gallery: []
});

const errors = ref({});
const isSubmitting = ref(false);
const isLoadingCategories = ref(false);
const isLoadingProduct = ref(false);
const categories = ref([]);
const galleryImages = ref([]);
const draggedIndex = ref(null);

// Refs for file inputs
const galleryFileInput = ref(null);

// Status options
const statusOptions = [
  { label: 'Rascunho', value: 'draft' },
  { label: 'Ativo', value: 'active' },
  { label: 'Arquivado', value: 'archived' }
];

// Computed properties
const pageTitle = computed(() => {
  return route.params.id ? 'Editar Produto' : 'Novo Produto';
});

const submitButtonLabel = computed(() => {
  return route.params.id ? 'Atualizar Produto' : 'Criar Produto';
});

const categoriesTree = computed(() => {
  return buildCategoryTree(categories.value);
});

const formattedPublishedDate = computed(() => {
  return 'Não publicado';
});

const formattedUpdatedDate = computed(() => {
  return 'Nunca';
});

// Gallery methods
const triggerGalleryFileInput = () => {
  galleryFileInput.value?.click();
};

const handleGalleryFileSelect = (event) => {
  const files = Array.from(event.target.files);
  addGalleryImages(files);
  event.target.value = '';
};

const handleGalleryFileDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  addGalleryImages(files);
};

const addGalleryImages = (files) => {
  files.forEach(file => {
    if (file.type.startsWith('image/') && file.size <= 10 * 1024 * 1024) {
      const reader = new FileReader();
      reader.onload = (e) => {
        galleryImages.value.push({
          file: file,
          preview: e.target.result,
          id: Date.now() + Math.random()
        });
        updateFormDataGallery();
      };
      reader.readAsDataURL(file);
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Arquivo deve ser uma imagem de até 10MB',
        life: 3000
      });
    }
  });
};

const removeGalleryImage = (index) => {
  galleryImages.value.splice(index, 1);
  updateFormDataGallery();
};

const handleDragStart = (index) => {
  draggedIndex.value = index;
};

const handleGalleryDrop = (targetIndex, event) => {
  if (draggedIndex.value !== null && draggedIndex.value !== targetIndex) {
    const draggedItem = galleryImages.value[draggedIndex.value];
    galleryImages.value.splice(draggedIndex.value, 1);
    galleryImages.value.splice(targetIndex, 0, draggedItem);
    updateFormDataGallery();
  }
  draggedIndex.value = null;
};

const updateFormDataGallery = () => {
  formData.gallery = galleryImages.value.map(img => img.file || img.url);
};

// Category methods
const buildCategoryTree = (categories) => {
  const categoryMap = {};
  const tree = [];

  categories.forEach(category => {
    categoryMap[category.id] = {
      key: category.id,
      label: category.name,
      children: []
    };
  });

  categories.forEach(category => {
    if (category.parent_id) {
      if (categoryMap[category.parent_id]) {
        categoryMap[category.parent_id].children.push(categoryMap[category.id]);
      }
    } else {
      tree.push(categoryMap[category.id]);
    }
  });

  return tree;
};

const loadCategories = async () => {
  try {
    isLoadingCategories.value = true;
    const response = await axios.get('/api/categories/tree');
    categories.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading categories:', error);
    // toast.add({
    //   severity: 'error',
    //   summary: 'Erro',
    //   detail: 'Falha ao carregar categorias',
    //   life: 3000
    // });
  } finally {
    isLoadingCategories.value = false;
  }
};

const loadProduct = async () => {
  if (!route.params.id) return;

  try {
    isLoadingProduct.value = true;
    const response = await axios.get(`/api/products/${route.params.id}`);
    const product = response.data.data || response.data;

    // Populate form data
    Object.keys(formData).forEach(key => {
      if (product[key] !== undefined) {
        formData[key] = product[key];
      }
    });

    // Load existing gallery images
    if (product.gallery && product.gallery.length > 0) {
      galleryImages.value = product.gallery.map((url, index) => ({
        url: url,
        preview: url,
        id: `existing-${index}`
      }));
    }

  } catch (error) {
    console.error('Error loading product:', error);
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao carregar produto',
      life: 3000
    });
    router.push({ name: 'products.index' });
  } finally {
    isLoadingProduct.value = false;
  }
};

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    errors.value = {};

    const formDataToSend = new FormData();
    
    // Add basic form data
    Object.keys(formData).forEach(key => {
      if (key !== 'gallery' && formData[key] !== null && formData[key] !== undefined) {
        // Convert boolean values to strings for FormData
        const value = typeof formData[key] === 'boolean' ? (formData[key] ? '1' : '0') : formData[key];
        formDataToSend.append(key, value);
      }
    });

    // Add gallery images
    galleryImages.value.forEach((image, index) => {
      if (image.file) {
        formDataToSend.append(`gallery[${index}]`, image.file);
      } else if (image.url) {
        formDataToSend.append(`existing_gallery[${index}]`, image.url);
      }
    });

    let response;
    if (route.params.id) {
      formDataToSend.append('_method', 'PUT');
      response = await axios.post(`/api/products/${route.params.id}`, formDataToSend, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      response = await axios.post('/api/products', formDataToSend, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: route.params.id ? 'Produto atualizado com sucesso!' : 'Produto criado com sucesso!',
      life: 3000
    });

    router.push({ name: 'products.index' });

  } catch (error) {
    console.error('Error submitting form:', error);
    
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Falha ao salvar produto',
      life: 5000
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Lifecycle
onMounted(async () => {
  // await loadCategories();
  await loadProduct();
});
</script>

<style scoped>
.product-form-container {
  padding: 1rem;
}

.form-card {
  height: fit-content;
}

.form-divider {
  width: 2px;
  height: 200px;
  background: linear-gradient(to bottom, transparent, #e5e7eb, transparent);
}

/* Gallery Styles */
.gallery-section {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1rem;
  background: #fafafa;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.gallery-item {
  position: relative;
  aspect-ratio: 1;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
}

.gallery-item:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.gallery-item.is-primary {
  border-color: #10b981;
  border-width: 3px;
}

.gallery-item.is-primary::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border: 2px solid #10b981;
  border-radius: 6px;
  pointer-events: none;
}

.gallery-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.gallery-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.gallery-item:hover .gallery-overlay {
  opacity: 1;
}

.primary-badge {
  background: #10b981;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.gallery-actions {
  display: flex;
  gap: 0.5rem;
}

.gallery-add-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  border-style: dashed;
  transition: all 0.2s ease;
}

.gallery-add-button:hover {
  background: #f3f4f6;
  border-color: #6b7280;
}

/* Status Fields */
.product-status-fields {
  padding: 1rem;
}

.status-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-label {
  font-weight: 500;
  color: #374151;
}

.status-value {
  color: #6b7280;
  font-size: 0.875rem;
}

.status-select-button {
  flex: 1;
  max-width: 200px;
}

/* Inventory Section */
.inventory-section {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1rem;
  background: #fafafa;
}

/* Responsive */
@media (max-width: 768px) {
  .form-divider {
    width: 100%;
    height: 2px;
    margin: 1rem 0;
  }
  
  .gallery-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
  }
}
</style>
