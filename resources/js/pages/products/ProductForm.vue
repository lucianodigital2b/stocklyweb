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
                <h3 class="mb-4">Informações do Produto</h3>
                
                <!-- Product Name -->
                <div class="field mb-4">
                  <label for="name" class="block font-medium mb-2">
                    Nome do Produto *
                  </label>
                  <InputText 
                    id="name" 
                    v-model="formData.name" 
                    placeholder="Digite o nome do produto"
                    class="w-full"
                    :class="{ 'p-invalid': errors.name }"
                  />
                  <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
                </div>

                <!-- Description -->
                <div class="field mb-4">
                  <label for="description" class="block font-medium mb-2">
                    Descrição
                  </label>
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

                <!-- SKU -->
                <div class="field mb-4">
                  <label for="sku" class="block font-medium mb-2">
                    SKU *
                  </label>
                  <InputText 
                    id="sku"
                    v-model="formData.sku" 
                    placeholder="Digite o SKU"
                    class="w-full"
                    :class="{ 'p-invalid': errors.sku }"
                  />
                  <small v-if="errors.sku" class="p-error">{{ errors.sku }}</small>
                </div>

                <!-- Price -->
                <div class="field mb-4">
                  <label for="price" class="block font-medium mb-2">
                    Preço *
                  </label>
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

                <!-- Categories - Cascading -->
                <div class="field mb-4">
                  <label for="category" class="block font-medium mb-2">
                    Categoria *
                  </label>
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

                <!-- Tags - MultiSelect -->
                <div class="field mb-4">
                  <label for="tags" class="block font-medium mb-2">
                    Tags
                  </label>
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
                </div>

                <!-- Submit Button -->
                <Button 
                  type="submit" 
                  :label="isSubmitting ? 'Enviando...' : 'Cadastrar Produto'" 
                  class="w-full" 
                  icon="pi pi-check"
                  :disabled="isSubmitting || isLoadingCategories"
                />
              </template>
            </Card>
          </div>

          <!-- Divider -->
          <div class="col-12 md:col-1 flex align-items-center justify-content-center">
            <div class="form-divider"></div>
          </div>

          <!-- Right Side - Featured Image -->
          <div class="col-12 md:col-4">
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
import { reactive, ref, watch, computed } from 'vue';
import { useAxios } from '@vueuse/integrations/useAxios';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import TreeSelect from 'primevue/treeselect';
import MultiSelect from 'primevue/multiselect';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FormSkeleton from '../../components/skeletons/FormSkeleton.vue';
import axios from '../../plugins/axios';

const toast = useToast();

const formData = reactive({
  name: '',
  sku: '',
  price: null,
  category: null,
  tags: [],
  description: '',
  inStock: false
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
  '/categorias',
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

// Tags data
const availableTags = ref([
  { id: 1, name: 'Novo' },
  { id: 2, name: 'Promoção' },
  { id: 3, name: 'Destaque' },
  { id: 4, name: 'Limitado' },
  { id: 5, name: 'Sazonal' },
  { id: 6, name: 'Premium' },
  { id: 7, name: 'Eco-friendly' },
  { id: 8, name: 'Artesanal' }
]);

const { execute: submitForm, isLoading: isSubmitting } = useAxios(
  { 
    method: 'POST',
    url: '/produtos'
  },
  axios
);

const validateForm = () => {
  let valid = true;
  
  errors.name = formData.name.trim() ? '' : 'Nome do produto é obrigatório';
  errors.sku = formData.sku.trim() ? '' : 'SKU é obrigatório';
  errors.price = formData.price !== null ? '' : 'Preço é obrigatório';
  errors.category = formData.category ? '' : 'Categoria é obrigatória';

  if (!formData.name.trim() || !formData.sku.trim() || formData.price === null || !formData.category) {
    valid = false;
  }

  return valid;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  try {
    const submitData = new FormData();
    submitData.append('name', formData.name);
    submitData.append('sku', formData.sku);
    submitData.append('price', formData.price);
    submitData.append('category_id', formData.category);
    submitData.append('description', formData.description);
    
    if (formData.tags.length > 0) {
      submitData.append('tags', JSON.stringify(formData.tags));
    }
    
    if (featuredImage.value) {
      submitData.append('featured_image', featuredImage.value);
    }

    await submitForm({
      data: submitData,
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Produto cadastrado com sucesso!',
      life: 3000
    });

    // Reset form
    Object.assign(formData, {
      name: '',
      sku: '',
      price: null,
      category: null,
      tags: [],
      description: '',
      inStock: false
    });
    removeFeaturedImage();

  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Falha ao cadastrar produto',
      life: 5000
    });
  }
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
  padding: 1rem;
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
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.form-card .p-card-content {
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
