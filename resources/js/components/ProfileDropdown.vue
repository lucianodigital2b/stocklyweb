<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../plugins/axios';
import { useAuthStore } from '../store/modules/auth';

const router = useRouter();
const authStore = useAuthStore();

// Dropdown state
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

// Toggle dropdown
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdown
const closeDropdown = () => {
    isDropdownOpen.value = false;
};

// Handle logout
const handleLogout = async () => {
    try {
        // Close dropdown first
        closeDropdown();
        
        // Make logout API call using axios (includes auth token automatically)
        const response = await axios.post('/api/logout');
        
        // Only clear auth store after successful logout
        if (response.status === 200) {
            // Clear auth store
            authStore.logout();
            
            // Clear any local storage/session storage if needed
            localStorage.removeItem('auth_token');
            sessionStorage.clear();
            
            // Redirect to login page
            router.push({ name: 'login' });
        }
    } catch (error) {
        console.error('Logout error:', error);
        
        // If logout fails, still clear local auth and redirect
        // This handles cases where the server is unreachable
        authStore.logout();
        localStorage.removeItem('auth_token');
        sessionStorage.clear();
        
        // Redirect to login page
        router.push({ name: 'login' });
    }
};

// Handle profile navigation
const goToProfile = () => {
    closeDropdown();
    router.push({ name: 'profile.settings' });
};

// Handle settings navigation
const goToSettings = () => {
    closeDropdown();
    router.push('/settings');
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Props for customization
const props = defineProps({
    buttonClass: {
        type: String,
        default: 'layout-topbar-action'
    },
    showChevron: {
        type: Boolean,
        default: true
    },
    buttonText: {
        type: String,
        default: 'Profile'
    }
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <button 
            type="button" 
            :class="buttonClass"
            @click="toggleDropdown"
        >
            <i class="pi pi-user"></i>
            <span>{{ buttonText }}</span>
            <i 
                v-if="showChevron"
                class="pi pi-chevron-down ml-2 text-xs transition-transform duration-200" 
                :class="{ 'rotate-180': isDropdownOpen }"
            ></i>
        </button>
        
        <!-- Dropdown Menu -->
        <div 
            v-show="isDropdownOpen"
            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 animate-fadeIn"
        >
            <div class="py-1">
                <button 
                    @click="goToProfile"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                >
                    <i class="pi pi-user mr-3"></i>
                    Meu Perfil
                </button>
                <button 
                    @click="goToSettings"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                >
                    <i class="pi pi-cog mr-3"></i>
                    Configurações
                </button>
                <hr class="my-1 border-gray-200 dark:border-gray-600">
                <button 
                    @click="handleLogout"
                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-left"
                >
                    <i class="pi pi-sign-out mr-3"></i>
                    Sair
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>