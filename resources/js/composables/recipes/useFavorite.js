import { ref, toValue, watch, watchEffect, onMounted } from 'vue';
import axios from '../../plugins/axios';
import { useAuthStore } from '../../store/modules/auth';



export function useFavorite(recipeId, initialFavorites, initialHasFavorited ) {
  
  const favorites = ref( toValue(initialFavorites) || 0 );
  const hasFavorited = ref( toValue(initialHasFavorited) );
  const loading = ref(false);
  const authStore = useAuthStore();

  const favorite = async () => {
    
    if( loading.value || !authStore.isAuthenticated) return;

    hasFavorited.value = !hasFavorited.value;
    favorites.value = hasFavorited.value ? favorites.value + 1 : favorites.value - 1;

    loading.value = true;

    try {
      await axios.post('/recipes/' + toValue(recipeId) + '/favorite', {
        direction: hasFavorited.value
      });
    }finally {
      loading.value = false;
    }
  }


  watchEffect(() => {
    favorites.value = toValue(initialFavorites) || 0;
    hasFavorited.value = toValue(initialHasFavorited);
  })

  onMounted(() => {
    window.Echo.channel(`recipe-favorites.${recipeId}`)
        .listen("RecipeFavoriteChanged", (response) => {
          
          const { data } = response;
          
          favorites.value += data.type;
        })
  });

  return {
    favorites,
    loading,
    hasFavorited,
    favorite,
  };
}
