<template>
  <div class="mb-3 border-bottom" >
    <div class="row align-items-center py-7">
      <div class="col-md-auto col-sm-12">
        <router-link :to="{ name: 'recipes.show', params: { slug: slug, id: id } }" class="text-decoration-none">
          <img :src="thumbnail ? `../storage/${thumbnail}` : '../img/blank-image-recipe.png'" alt="Post Thumbnail" class="img-fluid rounded-3 recipe-thumbnail" >
        </router-link>
      </div>
      <div class="col">
        <div class="card-body">
          <router-link :to="{ name: 'recipes.show', params: { slug: slug, id: id } }" class="text-decoration-none">
            <h5 class="card-title">{{ title }}</h5>
          </router-link> 
          <div class="text-muted"><small>By {{ author }}</small></div>

          <div class="d-flex gap-2">
            <upvote-downvote-button :recipe="props" />
            <favorite-button :recipe="props" />
          </div>

        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>

  .recipe-thumbnail {
    width: 300px; 
    height: 300px;
    object-fit: cover;
  }

  @media (max-width: 992px) {
    .recipe-thumbnail {
      width: 100%; 
      margin-bottom: 1rem;
    }
  }



</style>

<script setup>

const props = defineProps({
  id: Number,
  title: String,
  slug: String,
  thumbnail: String,
  author: String,
  votes: Number,
  favorites: Number,
  hasUpvoted: Boolean,
  hasDownvoted: Boolean,
  hasFavorited: Boolean,
})




</script>
