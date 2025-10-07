<template>
  <div class="info-card">
    <div 
      class="info-card-header" 
      @click="toggleable ? toggleCollapsed() : null"
      :class="{ 'clickable': toggleable }"
    >
      <div class="info-card-content">
        <i 
          v-if="toggleable"
          class="pi pi-chevron-down info-card-icon" 
          :class="{ 'rotated': isCollapsed }"
        ></i>
        <div class="info-card-title-section">
          <h4 class="info-card-title">{{ title }}</h4>
          <p v-if="subtitle" class="info-card-subtitle">{{ subtitle }}</p>
        </div>
      </div>
    </div>
    
    <Transition name="slide-down">
      <div v-show="!toggleable || !isCollapsed" class="info-card-body">
        <slot></slot>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  toggleable: {
    type: Boolean,
    default: false
  },
  defaultCollapsed: {
    type: Boolean,
    default: false
  }
});

const isCollapsed = ref(false);

const toggleCollapsed = () => {
  isCollapsed.value = !isCollapsed.value;
};
</script>

<style scoped>
.info-card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  background: white;
  box-shadow: none;
}

.info-card-header {
  padding: 1rem 0;
  transition: background-color 0.2s ease;
}

.info-card-header.clickable {
  cursor: pointer;
}



.info-card-content {
  display: flex;
  align-items: start;
  gap: 0.75rem;
}

.info-card-icon {
  font-size: 1rem;
  color: #6b7280;
  transition: transform 0.2s ease;
  flex-shrink: 0;
  border: 1px solid #e5e7eb;
  border-radius: 50px;
  padding: .575rem;
}

.info-card-icon.rotated {
  transform: rotate(-180deg);
}

.info-card-title-section {
  flex: 1;
}

.info-card-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #222;
  line-height: 1.5;
}

.info-card-subtitle {
  margin: 0.25rem 0 0 0;
  font-size: 1rem;
  color: #6b7280;
  line-height: 1.4;
}

.info-card-body {
  padding: 1rem;
  background-color: #FAFBF6;
  border-radius: var(--content-border-radius);
  border: 1px solid #e5e7eb;
}

/* Transition animations */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.slide-down-enter-to,
.slide-down-leave-from {
  max-height: 500px;
  opacity: 1;
}
</style>