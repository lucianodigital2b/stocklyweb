<template>
  <form class="d-flex position-relative" role="search">
      <input  @focus="() => showResultbox = input != null" @input="debouncedFn" v-model="input" class="rounded-pill search rounded-pill border-none" type="search" placeholder="Search" aria-label="Search">
      <div v-if="showResultbox" class="result-box " ref="target">
        <div class="item" v-for="item in results" :key="item" >
            <router-link class="text-decoration-none w-100 d-block" :to="{ name: 'recipes.show', params: { slug: item.slug, id: item.id } }" >
              {{ item.title }}
            </router-link>
        </div>
        <div class="p-3" v-if="input && !results?.length && !loading">
            <p>No results found!</p>
        </div>
      </div>
  </form>

</template>

<style scoped>
  .search {
    position: relative;
    min-width: 200px;
    width: 516px;
    padding: 10px 30px;
    background: #F7F8FC;
  }

  .result-box {
    position: absolute;
    top: 100%;
    left: 0;
    width: 516px;
    background: white;
    border-radius: 0 0 10px 10px;
    box-shadow: 0px 10px 20px 0px rgba(38, 50, 56, 0.1019607843);
  } 

  .item {
    padding: .85rem 1rem;
    border-bottom: 1px solid #F7F8FC;
  }

  .item:hover{
    background: #F7F8FC;
  }

</style>
<script setup>
import axios from '../plugins/axios';
import { ref, watch } from "vue";
import { useDebounceFn, onClickOutside, } from '@vueuse/core'
import { useRouter } from 'vue-router';

const input = ref(null);
let loading = ref(false);
let showResultbox = ref(false);
let results = ref(null); 
const target = ref(null)
const router = useRouter();

onClickOutside(target, event => showResultbox.value = false)

watch(input, () => {
  showResultbox.value = true
})


const search = async () => {
  loading.value = true
  try {
    const {data} = await axios.get("/recipes/search?", { params: { q: input.value } });
    results.value = data.data;
    console.log(results.value);
    
  }finally{
    loading.value = false 
  }
}

const debouncedFn = useDebounceFn(() => {
  search();
}, 1000)


</script>