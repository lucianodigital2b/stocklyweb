import { ref, watch, watchEffect, onMounted } from 'vue';
import axios from '../../plugins/axios';
import { toValue } from 'vue';
import { useAuthStore } from '../../store/modules/auth';


export function useVote(recipeId, initialVotes, initialHasUpvoted, initialHasDownvoted) {
  const votes = ref(toValue(initialVotes) || 0);
  const hasUpvoted = ref(initialHasUpvoted);
  const hasDownvoted = ref(initialHasDownvoted);
  const loading = ref(false);
  const authStore = useAuthStore();

  

  const changeVote = async (vote) => {
    if( loading.value || !authStore.isAuthenticated) return;
    
  
    votes.value += vote;
    hasUpvoted.value = vote === 1
    hasDownvoted.value = vote !== 1;
  
    loading.value = true;
  
    try {
      await axios.post(`/recipes/${toValue(recipeId)}/vote`, {
        direction: vote
      });
    }finally {
      loading.value = false;
    }
  }


  watchEffect(() => {
    votes.value = toValue(initialVotes) || 0;
    hasUpvoted.value = toValue(initialHasUpvoted);
    hasDownvoted.value = toValue(initialHasDownvoted);
  })

  onMounted(() => {
    window.Echo.channel(`recipe-votes.${recipeId}`)
        .listen("RecipeVoteChanged", (response) => {
          
          const { data } = response;
          
          votes.value += data.type;
        })
  });


  return {
    votes,
    loading,
    hasUpvoted,
    hasDownvoted,
    changeVote,
  };
}
