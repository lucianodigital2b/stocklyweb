<template>
  <div class="flex justify-content-center ">
    <form @submit.prevent="handleSubmit" class="w-full">
      <!-- Loading Skeletons -->
      <template v-if="isLoadingCategories">
        <FormSkeleton />
      </template>



      <!-- Form Content -->
      <template v-else>
        <div class="field grid mb-4">
          <h3>Adicionar produto</h3>
          <div class="col-12">
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
          
        </div>

        <div class="grid form-grid mb-4">
          <div class="col-12">
            <label for="name" class="block font-medium mb-2">
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
        </div>
        <div class="grid form-grid mb-4">
          <div class="field col-12 md:col-6">
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

          <div class="field col-12 md:col-6">
            <label for="category" class="block font-medium mb-2">
              Categoria *
            </label>
            <Dropdown 
              id="category" 
              v-model="formData.category" 
              :options="categories" 
              optionLabel="nome" 
              placeholder="Selecione uma categoria"
              class="w-full"
              :class="{ 'p-invalid': errors.category }"
            />
            <small v-if="errors.category" class="p-error">{{ errors.category }}</small>
          </div>
        </div>

        <div class="field grid mb-4">
          <div class="col-12">
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
        </div>

        <!-- <div class="field grid mb-4">
          <div class="col-12 flex align-items-center">
            <Checkbox 
              id="inStock" 
              v-model="formData.inStock" 
              :binary="true" 
              class="mr-2"
            />
            <label for="inStock" class="font-medium">
              Disponível em estoque
            </label>
          </div>
        </div> -->
      </template>

      <Button 
        type="submit" 
        :label="isSubmitting ? 'Enviando...' : 'Cadastrar Produto'" 
        class="w-full" 
        icon="pi pi-check"
        :disabled="isSubmitting || isLoadingCategories"
      />
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useAxios } from '@vueuse/integrations/useAxios';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import axios from '../../plugins/axios';


const toast = useToast();

const formData = reactive({
  name: '',
  price: null,
  category: null,
  description: '',
  inStock: false
});

const errors = reactive({
  name: '',
  price: '',
  category: ''
});

const { data: categoriesData, isLoading: isLoadingCategories } = useAxios(
  '/categorias',
  { method: 'GET' },
  axios
);

const categories = ref([]);

watch(categoriesData, (newVal) => {
  if (newVal?.data) {
    categories.value = newVal.data;
  }
});

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
  errors.price = formData.price !== null ? '' : 'Preço é obrigatório';
  errors.category = formData.category ? '' : 'Categoria é obrigatória';

  if (!formData.name.trim() || formData.price === null || !formData.category) {
    valid = false;
  }

  return valid;
};

const handleSubmit = async () => {
  // if (!validateForm()) return;

  try {
    await submitForm({
      data: {
        name: formData.name,
        price: formData.price,
        category_id: formData.category.code,
        description: formData.description,
      }
    });
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
</style>
