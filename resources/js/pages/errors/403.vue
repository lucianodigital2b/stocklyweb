<template>
  <card class="text-center">
    <h3 class="mb-4">
       You can't do that right now!
    </h3>

    <div class="links">
      <router-link :to="{ name: '/' }">
        Get back
      </router-link>
    </div>
  </card>
</template>

<script>
export default {
  name: 'NotFound'
}
</script>
