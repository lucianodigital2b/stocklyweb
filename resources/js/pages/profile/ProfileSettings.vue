<template>
  <div class="profile-settings">
    <!-- Page Header -->
    <div class="mb-4">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Configurações do Perfil</h1>
      <p class="text-gray-600 dark:text-gray-400">Gerencie suas informações pessoais e configurações de segurança</p>
    </div>

    <div class="space-y-6">
      <!-- Profile Information Card -->
      <Card class="w-full">
        <template #title>
          <div class="flex items-center gap-3">
            <i class="pi pi-user text-primary"></i>
            <span>Informações Pessoais</span>
          </div>
        </template>
        <template #content>
          <form @submit.prevent="updateProfile" class="space-y-4">
            <!-- Name Field -->
            <div class="field">
              <Label for="name" :required="true">Nome Completo</Label>
              <InputText
                id="name"
                v-model="profileForm.name"
                :class="{ 'p-invalid': profileErrors.name }"
                class="w-full"
                placeholder="Digite seu nome completo"
                :disabled="isUpdatingProfile"
              />
              <small v-if="profileErrors.name" class="p-error">{{ profileErrors.name }}</small>
            </div>

            <!-- Email Field -->
            <div class="field">
              <Label for="email" :required="true">E-mail</Label>
              <InputText
                id="email"
                v-model="profileForm.email"
                type="email"
                :class="{ 'p-invalid': profileErrors.email }"
                class="w-full"
                placeholder="Digite seu e-mail"
                :disabled="isUpdatingProfile"
              />
              <small v-if="profileErrors.email" class="p-error">{{ profileErrors.email }}</small>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
              <Button
                type="submit"
                label="Atualizar Perfil"
                icon="pi pi-check"
                :loading="isUpdatingProfile"
                :disabled="!isProfileFormValid"
                class="w-full sm:w-auto"
              />
            </div>
          </form>
        </template>
      </Card>

      <!-- Password Change Card -->
      <Card class="w-full">
        <template #title>
          <div class="flex items-center gap-3">
            <i class="pi pi-lock text-primary"></i>
            <span>Alterar Senha</span>
          </div>
        </template>
        <template #content>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <!-- Current Password -->
            <div class="field">
              <Label for="current_password" :required="true">Senha Atual</Label>
              <Password
                id="current_password"
                v-model="passwordForm.current_password"
                :class="{ 'p-invalid': passwordErrors.current_password }"
                class="w-full"
                placeholder="Digite sua senha atual"
                :feedback="false"
                toggleMask
                :disabled="isUpdatingPassword"
              />
              <small v-if="passwordErrors.current_password" class="p-error">{{ passwordErrors.current_password }}</small>
            </div>

            <!-- New Password -->
            <div class="field">
              <Label for="password" :required="true">Nova Senha</Label>
              <Password
                id="password"
                v-model="passwordForm.password"
                :class="{ 'p-invalid': passwordErrors.password }"
                class="w-full"
                placeholder="Digite sua nova senha"
                :feedback="true"
                toggleMask
                :disabled="isUpdatingPassword"
              />
              <small v-if="passwordErrors.password" class="p-error">{{ passwordErrors.password }}</small>
            </div>

            <!-- Confirm Password -->
            <div class="field">
              <Label for="password_confirmation" :required="true">Confirmar Nova Senha</Label>
              <Password
                id="password_confirmation"
                v-model="passwordForm.password_confirmation"
                :class="{ 'p-invalid': passwordErrors.password_confirmation }"
                class="w-full"
                placeholder="Confirme sua nova senha"
                :feedback="false"
                toggleMask
                :disabled="isUpdatingPassword"
              />
              <small v-if="passwordErrors.password_confirmation" class="p-error">{{ passwordErrors.password_confirmation }}</small>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
              <Button
                type="submit"
                label="Alterar Senha"
                icon="pi pi-key"
                :loading="isUpdatingPassword"
                :disabled="!isPasswordFormValid"
                class="w-full sm:w-auto"
              />
            </div>
          </form>
        </template>
      </Card>
    </div>

    <!-- Success Messages -->
    <Toast />
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useAuthStore } from '../../store/modules/auth';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Toast from 'primevue/toast';
import Label from '../../components/Label.vue';
import axios from '../../plugins/axios';

const toast = useToast();
const authStore = useAuthStore();

// Profile form data
const profileForm = reactive({
  name: '',
  email: ''
});

// Password form data
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

// Error states
const profileErrors = reactive({
  name: '',
  email: ''
});

const passwordErrors = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

// Loading states
const isUpdatingProfile = ref(false);
const isUpdatingPassword = ref(false);

// Form validation
const isProfileFormValid = computed(() => {
  return profileForm.name.trim() !== '' && 
         profileForm.email.trim() !== '' && 
         /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profileForm.email);
});

const isPasswordFormValid = computed(() => {
  return passwordForm.current_password !== '' && 
         passwordForm.password !== '' && 
         passwordForm.password_confirmation !== '' &&
         passwordForm.password === passwordForm.password_confirmation &&
         passwordForm.password.length >= 8;
});

// Clear errors function
const clearProfileErrors = () => {
  Object.keys(profileErrors).forEach(key => {
    profileErrors[key] = '';
  });
};

const clearPasswordErrors = () => {
  Object.keys(passwordErrors).forEach(key => {
    passwordErrors[key] = '';
  });
};

// Update profile function
const updateProfile = async () => {
  if (!isProfileFormValid.value) return;

  clearProfileErrors();
  isUpdatingProfile.value = true;

  try {
    const response = await axios.patch('/api/profile', {
      name: profileForm.name,
      email: profileForm.email
    });

    // Update auth store with new user data
    authStore.updateUser(response.data.user);

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Perfil atualizado com sucesso!',
      life: 3000
    });

  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        if (profileErrors.hasOwnProperty(key)) {
          profileErrors[key] = errors[key][0];
        }
      });
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Erro ao atualizar perfil. Tente novamente.',
        life: 3000
      });
    }
  } finally {
    isUpdatingProfile.value = false;
  }
};

// Update password function
const updatePassword = async () => {
  if (!isPasswordFormValid.value) return;

  clearPasswordErrors();
  isUpdatingPassword.value = true;

  try {
    await axios.patch('/api/profile/password', {
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation
    });

    // Clear password form
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Senha alterada com sucesso!',
      life: 3000
    });

  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        if (passwordErrors.hasOwnProperty(key)) {
          passwordErrors[key] = errors[key][0];
        }
      });
    } else if (error.response?.status === 400) {
      passwordErrors.current_password = 'Senha atual incorreta';
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Erro ao alterar senha. Tente novamente.',
        life: 3000
      });
    }
  } finally {
    isUpdatingPassword.value = false;
  }
};

// Load user data on component mount
onMounted(() => {
  if (authStore.user) {
    profileForm.name = authStore.user.name || '';
    profileForm.email = authStore.user.email || '';
  }
});
</script>

<style scoped>
.profile-settings {
  padding: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.field {
  margin-bottom: 1rem;
}

.field:last-child {
  margin-bottom: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .profile-settings {
    padding: 1rem;
  }
}

/* Custom card styling */
:deep(.p-card) {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

:deep(.p-card-title) {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

:deep(.p-card-content) {
  padding-top: 0;
}

/* Button styling */
:deep(.p-button) {
  border-radius: 8px;
  font-weight: 500;
}

/* Input styling */
:deep(.p-inputtext),
:deep(.p-password input) {
  border-radius: 8px;
  border: 1px solid #d1d5db;
  transition: border-color 0.2s, box-shadow 0.2s;
}

:deep(.p-inputtext:focus),
:deep(.p-password input:focus) {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.2);
}

:deep(.p-invalid) {
  border-color: #ef4444 !important;
}

:deep(.p-invalid:focus) {
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
}
</style>