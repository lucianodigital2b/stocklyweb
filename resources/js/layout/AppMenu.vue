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
                        to: '/customers'
                    },
                    {
                        label: 'Listar',
                        to: '/customers/create'
                    },
                ]
            },
            {
                label: 'Produtos',
                icon: 'pi pi-fw pi-users',
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
                        to: '/products/create'
                    },
                    {
                        label: 'Atributos',
                        to: '/products/create'
                    },
                    {
                        label: 'Marcas',
                        to: '/products/create'
                    },
                ]
            },
            {
                label: 'Pedidos',
                icon: 'pi pi-fw pi-users',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/products'
                    },
                    {
                        label: 'Listar',
                        to: '/products/create'
                    },
                ]
            },
            {
                label: 'Cupons',
                icon: 'pi pi-fw pi-users',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/coupons'
                    },
                    {
                        label: 'Listar',
                        to: '/coupons/create'
                    },
                ]
            },
            {
                label: 'Fornecedores',
                icon: 'pi pi-fw pi-users',
                items: [
                    {
                        label: 'Cadastrar',
                        to: '/suppliers'
                    },
                    {
                        label: 'Listar',
                        to: '/suppliers/create'
                    },
                ]
            },
            {
                label: 'Depósito e Entrega',
                icon: 'pi pi-fw pi-users',
                items: [
                    {
                        label: 'Políticas de Envio',
                        to: '/deliveries'
                    },
                    {
                        label: 'Métodos de Envio',
                        to: '/shipping-methods'
                    },
                    {
                        label: 'Inventário',
                        to: '/inventories'
                    },
                ]
            },
            { label: 'Configurações', icon: 'pi pi-fw pi-id-card', to: '/uikit/formlayout' },
            // { label: 'Input', icon: 'pi pi-fw pi-check-square', to: '/uikit/input' },
            // { label: 'Button', icon: 'pi pi-fw pi-mobile', to: '/uikit/button', class: 'rotated-icon' },
            // { label: 'Table', icon: 'pi pi-fw pi-table', to: '/uikit/table' },
            // { label: 'List', icon: 'pi pi-fw pi-list', to: '/uikit/list' },
            // { label: 'Tree', icon: 'pi pi-fw pi-share-alt', to: '/uikit/tree' },
            // { label: 'Panel', icon: 'pi pi-fw pi-tablet', to: '/uikit/panel' },
            // { label: 'Overlay', icon: 'pi pi-fw pi-clone', to: '/uikit/overlay' },
            // { label: 'Media', icon: 'pi pi-fw pi-image', to: '/uikit/media' },
            // { label: 'Menu', icon: 'pi pi-fw pi-bars', to: '/uikit/menu' },
            // { label: 'Message', icon: 'pi pi-fw pi-comment', to: '/uikit/message' },
            // { label: 'File', icon: 'pi pi-fw pi-file', to: '/uikit/file' },
            // { label: 'Chart', icon: 'pi pi-fw pi-chart-bar', to: '/uikit/charts' },
            // { label: 'Timeline', icon: 'pi pi-fw pi-calendar', to: '/uikit/timeline' },
            // { label: 'Misc', icon: 'pi pi-fw pi-circle', to: '/uikit/misc' }
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



