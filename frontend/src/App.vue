<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from './layouts/MainLayout.vue'
import AuthLayout from './layouts/AuthLayout.vue'

const route = useRoute()

const layout = computed(() => {
  const layoutMeta = route.meta.layout
  if (layoutMeta === 'AuthLayout') return AuthLayout
  return MainLayout
})
</script>

<template>
  <component :is="layout">
    <router-view />
  </component>
</template>
