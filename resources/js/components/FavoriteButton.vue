<template>
    <div class="d-inline-flex align-items-center rounded-3 mt-4" style="background-color: #F1F1F4;">
        <button @click.prevent="favorite" class="btn btn-sm" :class="{ 'has-favorited': hasFavorited }" :disabled="loadingFavorite.value">
            {{ favorites }}
            <HeartIcon v-if="!hasFavorited" class="hero-icon btn-favorited"/>
            <HeartIconSolid v-if="hasFavorited" class="hero-icon"/>
        </button>
    </div>
</template>


<script setup>

import { PlusIcon, XMarkIcon, PencilSquareIcon, ClockIcon, CheckCircleIcon, ArrowDownIcon, ArrowUpIcon, HeartIcon } from '@heroicons/vue/24/outline'
import { useFavorite } from '../composables/recipes/useFavorite';
import { HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
    recipe: Object
});

const { favorites, hasFavorited, favorite, loading: loadingFavorite } = useFavorite(() => props.recipe.id, () => props.recipe.favorites, () => props.recipe.userFavorite != null);

</script>