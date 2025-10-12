<script setup>
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import { useAuthStore } from '../store/modules/auth';

const auth = useAuthStore();

console.log(auth.user);

const model = ref([
    {
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/' },
            {
                label: 'Clientes',
                icon: 'pi pi-fw pi-users',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/customers/create'
                    },
                    {
                        label: 'Listar',
                        to: '/customers'
                    },
                ]
            },
            {
                label: 'Fornecedores',
                icon: 'pi pi-fw pi-user',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/suppliers/create'
                    },
                    {
                        label: 'Listar',
                        to: '/suppliers'
                    },
                ]
            },
            {
                label: 'Lançamentos',
                icon: 'pi pi-fw pi-money-bill',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/entries/create'
                    },
                    {
                        label: 'Listar',
                        to: '/entries'
                    },
                ]
            },
            {
                label: 'Centros de Custo',
                icon: 'pi pi-fw pi-circle',
                to: '/cost-centers'
            },
            {
                label: 'Produtos',
                icon: 'pi pi-fw pi-box',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/products/create'
                    },
                    {
                        label: 'Listar',
                        to: '/products'
                    },
                    {
                        label: 'Categorias',
                        to: '/categories'
                    },
                    // {
                    //     label: 'Atributos',
                    //     to: '/products/create'
                    // },
                    // {
                    //     label: 'Marcas',
                    //     to: '/products/create'
                    // },
                ]
            },
            {
                label: 'Pedidos',
                icon: 'pi pi-fw pi-list-check',
                items: [
                    {
                        label: 'Listar',
                        to: '/orders'
                    },
                    {
                        label: 'Cadastrar',
                        to: '/orders/create'
                    },
                ]
            },
            
            {
                label: 'Inventário',
                icon: 'pi pi-fw pi-building',
                to: '/inventories'
            },
            {
                label: 'Cupons',
                icon: 'pi pi-fw pi-users',
                soon: true,
            },
            {
                label: 'Depósito e Entrega',
                icon: 'pi pi-fw pi-users',
                soon: true,

                items: [
                    {
                        label: 'Políticas de Envio',
                        to: '/deliveries'
                    },
                    // {
                    //     label: 'Métodos de Envio',
                    //     to: '/shipping-methods'
                    // },
                    {
                        label: 'Inventário',
                        to: '/inventories'
                    },
                ]
            },
        ]
    },
    
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>

    <div class="mt-auto px-3">
        <!-- <strong>{{ auth.getUser().name }}</strong> -->
    </div>
</template>

<style lang="scss" scoped></style>



