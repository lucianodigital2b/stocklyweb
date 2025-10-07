<template>
    <template v-for="(item) in items" :key="item">
        <recipe-card 
          :id="item.id"
          :title="item.title"
          :slug="item.slug ?? 'blabla'"
          :thumbnail="item.thumbnail"
          :author="item.author.name"
          :votes="item.votes ?? 0"
          :favorites="item.favorites ?? 0"
          :hasUpvoted="item.userVote?.type == 1"
          :hasDownvoted="item.userVote?.type == -1"
          :hasFavorited="item.userFavorite != null" 
        />

    </template>
    <div class="d-flex align-items-center justify-content-center">
      <loading :loading="loading" />
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from '../plugins/axios';
import Loading from './Loading.vue';

let items = ref([]);
let current_page = ref(1);
let last_page = ref(null);
let bottom = ref(false);
let loading = ref(false);

watch(bottom, () => {
  fetchRecipes();
});

const fetchRecipes = async () => {
  if((current_page.value > last_page.value && last_page.value) || loading.value) {
    return;
  }

  loading.value = true;

  try {
    const { data} = await axios.get('/recipes', {
      params: {
        page: current_page.value
      }
    });

    // items.value.push(...data.data)

    // current_page.value += 1;
    // last_page.value = data.meta.last_page;

  } finally {
    loading.value = false;
  }




  
}


onMounted(() => {
  fetchRecipes();

  window.onscroll = () => {
    bottom.value = bottomVisible();
  }
})


const bottomVisible = () => {
  const visible = document.documentElement.clientHeight;
  const pageHeight = document.documentElement.scrollHeight;
  const bottom = visible + window.scrollY >= pageHeight;

  return bottom;
}



</script>