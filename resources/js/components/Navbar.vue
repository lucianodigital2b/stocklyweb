<template>
  <nav class="navbar navbar-expand-lg shadow-none border-bottom">
  <div class="container">

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <!-- <span class="navbar-toggler-icon"></span> -->
       <Bars3CenterLeftIcon class="hero-icon" />
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <router-link :to="{ name: 'dashboard' }" class="navbar-brand me-auto logo d-flex align-items-center justify-content-baseline gap-2 d-none d-md-block">
        <img :src="'/img/logo.png'" alt="logo" class="logo">
        recipefy
      </router-link>
      

      <!-- <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <router-link :to="{ name: 'dashboard' }" class="nav-link" active-class="active">Home</router-link>
        </li>
      </ul> -->

      <search-input />

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-flex align-items-center" >
        <v-button v-if="user" @click="router.push({ name: 'recipes.create' })"><LightBulbIcon class="hero-icon"/> new recipe</v-button>
        <li class="nav-item dropdown" v-if="user">
            <a class="nav-link ml-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img :src="user?.photo_url ?? '/img/avatar.png'"  class="rounded-circle profile-photo me-1">
            </a>
            <ul class="dropdown-menu shadow-sm">

              <li><button @click.prevent="logout" class="nav-link">Favorites</button></li>
              <li><router-link :to="{ name: 'recipes.create' }" class="nav-link">Add a recipe</router-link></li>
              <li><hr class="dropdown-divider"></li>
              <li><button @click.prevent="logout" class="nav-link">Logout</button></li>
            </ul>
        </li>
        <!-- Guest -->
        <template v-else>
          <li class="nav-item">
            <router-link :to="{ name: 'login' }" class="nav-link">Login</router-link>
          </li>
          <li class="nav-item">
            <router-link :to="{ name: 'register' }" class="nav-link"> Register</router-link>
          </li>
        </template>
      </ul>

    </div>
  </div>
</nav>
</template>

<script setup>
import { useAuthStore } from '../store/modules/auth'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router';
import { LightBulbIcon, UserCircleIcon, Bars3CenterLeftIcon } from '@heroicons/vue/24/outline'

const store = useAuthStore()
const router = useRouter()
const { user } = storeToRefs(store)

const logout = () => {
  store.logout()
  router.push({ path: '/login' })
}


  
</script>

<style scoped>
  .profile-photo {
    width: 2rem;
    height: 2rem;
    margin: -.375rem 0;
  }



  .navbar-expand-lg .navbar-nav .dropdown-menu {
    border-radius: .5rem;
  }

  .dropdown:hover .dropdown-menu {
    display: block;
    margin-top: 0; /* remove the gap so it doesn't close */
  }

  .navbar-toggler .hero-icon {
    width: 22px;
    height: 22px;
  }
</style>
