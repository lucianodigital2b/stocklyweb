<template>
    <div class="d-inline-flex align-items-center rounded-3 mt-4" style="background-color: #F1F1F4;">
        <button @click.prevent="changeVote(1)" class="border-0 btn btn-sm btn-upvote" :class="{ 'has-upvoted': hasUpvoted }" :disabled="loading.value || hasUpvoted">
            <ArrowUpIcon class="hero-icon"/>
        </button>
        <span class="text-dark">{{ votes }}</span>
        <button @click.prevent="changeVote(-1)" class="border-0 btn btn-sm btn-downvote" :class="{ 'has-downvoted': hasDownvoted }" :disabled="loading.value || hasDownvoted">
            <ArrowDownIcon class="hero-icon"/>
        </button>
    </div>

</template>

<script setup>
import { useVote } from '../composables/recipes/useVote';
import { ArrowDownIcon, ArrowUpIcon } from '@heroicons/vue/24/outline'


const props = defineProps({
    recipe: Object
})


const { votes, loading, changeVote, hasUpvoted, hasDownvoted } = useVote(() => props.recipe.id, () => props.recipe.votes, () => props.recipe.userVote?.type == 1, () => props.recipe.userVote?.type == -1);

</script>