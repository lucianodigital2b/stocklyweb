<!-- components/RouteDrawer.vue -->
<template>
    <Drawer
      v-model:visible="isDrawerOpen"
      position="right"
      :dismissable="true"
      @hide="closeDrawer"
    >
      <!-- Dynamic Content Based on Route -->
      <component :is="drawerComponent" />
      
    </Drawer>
  </template>
  
  <script setup>
    import { computed, defineAsyncComponent } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import Drawer from 'primevue/drawer'
    
    const router = useRouter()
    const route = useRoute()
    

    console.log(route.meta.drawerType);
    
    const drawerComponent = defineAsyncComponent(() =>
        import(`@/components/drawers/${route.meta.drawerType}Drawer.vue`)
    )
    
    // Control drawer visibility
    const isDrawerOpen = computed({
        get: () => route.meta.showDrawer,
        set: (value) => {
        if (!value) closeDrawer()
        }
    })
    
    // Close logic
    const closeDrawer = () => {
        router.push(route.meta.parentRoute || '/')
  }
  </script>


<style >

    .p-drawer-right .p-drawer{
        width: 45rem !important;
    }


</style>