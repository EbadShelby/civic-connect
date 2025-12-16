<script setup>
import { useAuthStore } from './stores/authStore'

const authStore = useAuthStore()
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- Navigation bar will be added here -->
    <nav class="bg-indigo-600 text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <router-link to="/" class="text-2xl font-bold">CivicConnect</router-link>
        <div class="flex gap-4">
          <template v-if="authStore.isAuthenticated">
            <router-link to="/dashboard" class="hover:text-indigo-100">Dashboard</router-link>
            <button @click="authStore.logout" class="hover:text-indigo-100">Logout</button>
          </template>
          <template v-else>
            <router-link to="/login" class="hover:text-indigo-100">Sign In</router-link>
            <router-link to="/register" class="hover:text-indigo-100">Register</router-link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <router-view />
  </div>
</template>
