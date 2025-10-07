<template>
  <label :for="for" :class="labelClass">
    <slot />
    <span v-if="required" class="text-red-500 ml-1">*</span>
  </label>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  for: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: 'block font-medium mb-2'
  }
})

const labelClass = computed(() => props.class)
</script>