<template>
  <component :is="route.meta.layoutComponent">
      <router-view />
  </component>
</template>

<script setup>

import { useRouter, useRoute } from "vue-router";
import { globalRouter } from "./router/globalRouter";

const router = useRouter();
const route = useRoute();

globalRouter.router = router;

</script>