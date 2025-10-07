<template>
  <section class="wrapper-form" >
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-xl-10">
          <div class="card rounded-5 text-black">
            <div class="row g-0">
              <div class="col-lg-6">
                <div class="card-body p-md-5 mx-md-4">

                  <div class="d-flex align-items-center justify-content-baseline mb-7 gap-2">
                    <img :src="'../img/logo.png'" alt="logo" class="logo">
                    <h4 class="mb-0 font-weight-bold logo">recipefy</h4>
                  </div>

                  <form @submit.prevent="register" @keydown="form.onKeydown($event)">
                    <p>Please fill all the required fields</p>

                    <AlertError :form="form" />
                    <div class="form-outline mb-4">
                      <label class="form-label" for="form2Example11">Name</label>
                      <input solid v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control" name="name">
                      <has-error :form="form" field="name" />
                    </div>

                    <div class="form-outline mb-4">
                      <label class="form-label" for="form2Example11">E-mail</label>
                      <input solid v-model="form.email" :class="{ 'is-invalid': form.errors.has('email') }" class="form-control" type="email" name="email">
                      <has-error :form="form" field="email" />
                    </div>

                    <div class="form-outline mb-4">
                      <label class="form-label" for="form2Example22">Password</label>
                      <input autocomplete  v-model="form.password" :class="{ 'is-invalid': form.errors.has('password') }" class="form-control" type="password" name="password">
                      <has-error :form="form" field="password" />

                    </div>
                    <div class="form-outline mb-4">
                      <label class="form-label" for="form2Example22">Confirm Password</label>
                      <input autocomplete  v-model="form.password_confirmation" :class="{ 'is-invalid': form.errors.has('password_confirmation') }" class="form-control" type="password" name="password_confirmation">
                      <has-error :form="form" field="password_confirmation" />
                    </div>

                    <div class="text-center pt-1 mb-5 pb-1">
                      <v-button large block :loading="form.busy">Register</v-button>
                    </div>

                    <div class="d-flex align-items-center justify-content-center pb-4">
                      <p class="mb-0 me-2">Already have an account?</p>
                      <router-link
                        :to="{ path: '/login' }"
                        class="font-medium "
                      >
                        Login now
                      </router-link>
                    </div>

                  </form>

                </div>
              </div>
              <div class="col-lg-6 d-flex align-items-center bg-img">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>


<style scoped>
  .bg-img {
    background: url('../img/register-img.jpg') no-repeat center center;
    background-size: cover;
  }

  .wrapper-form {
    height: 100vh;
  }

  @media (min-width: 769px) {
    .bg-img {
      border-top-right-radius:2rem;
      border-bottom-right-radius:2rem;
    }
  }
</style>

<script setup>


import Form from 'vform'
import Cookies from 'js-cookie'
import LoginWithGithub from '../../components/LoginWithGithub.vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/modules/auth'
import { ref, reactive } from 'vue'
import axios from '../../plugins/axios';

const form = reactive( new Form({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
}));

const router = useRouter()

const register = async () => {
    const { data } = await form.post('/register')
    const store = useAuthStore()
    
    const login_data = await form.post('/login')


    // Log in the user.
    store.login({
      token: login_data.data.token,
      user: login_data.data.user,
    })

    // Redirect home.
    router.push({ name: 'dashboard' })
}

</script>
