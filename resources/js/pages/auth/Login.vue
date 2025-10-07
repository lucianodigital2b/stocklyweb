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
                    <!-- <img :src="'../img/logo.png'" alt="logo" class="logo"> -->
                    <h4 class="mb-0 font-weight-bold logo">stockly</h4>
                  </div>

                  <form @submit.prevent="login" @keydown="form.onKeydown($event)">
                    <p>Please login to your account</p>

                    <AlertError :form="form" />
                    <div data-mdb-input-init class="form-outline mb-4">
                      <label class="form-label" for="form2Example11">E-mail</label>

                      <input solid v-model="form.email" :class="{ 'is-invalid': form.errors.has('email') }" class="form-control" type="email" name="email">
                      <has-error :form="form" field="email" />

                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                      <label class="form-label" for="form2Example22">Password</label>
                      <input autocomplete  v-model="form.password" :class="{ 'is-invalid': form.errors.has('password') }" class="form-control" type="password" name="password">
                      <has-error :form="form" field="password" />

                    </div>

                    <div class="text-center pt-1 mb-5 pb-1">
                      <v-button large block :loading="form.busy">Login</v-button>
                    </div>

                    <div class="d-flex align-items-center justify-content-center pb-4">
                      <p class="mb-0 me-2">Don't have an account?</p>
                      <router-link
                        :to="{ name: 'register' }"
                        class="font-medium "
                      >
                        Register for free
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
    background: url('/img/login-img.jpeg') no-repeat center center;
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
  email: '',
  password: ''
}));

const router = useRouter()

const login = async () => {
  // Submit the form.
  
  const { data } = await form.post('/login')
  
  console.log(form.errors);
  
  const store = useAuthStore()
  
  // Save the data.
  store.login({
    token: data.token,
    user: data.user,
  })

  redirect();
}

const redirect = () => {
  const intendedUrl = Cookies.get('intended_url')

  if (intendedUrl) {
    Cookies.remove('intended_url')
    router.push({ path: intendedUrl })
  } else {
    router.push({ name: 'dashboard' })
  }
}
</script>
